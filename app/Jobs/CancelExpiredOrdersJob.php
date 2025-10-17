<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting automatic cancellation of expired orders');

        // Находим все просроченные заказы
        $expiredOrders = Order::expired()->get();

        if ($expiredOrders->isEmpty()) {
            Log::info('No expired orders found');
            return;
        }

        $cancelledCount = 0;
        $errorCount = 0;

        Log::info("Found {$expiredOrders->count()} expired orders to process");

        foreach ($expiredOrders as $order) {
            try {
                if ($order->cancelAndUnreserve()) {
                    $cancelledCount++;
                    
                    Log::info('Expired order cancelled automatically', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'expired_at' => $order->expires_at,
                        'total_amount' => $order->total_amount,
                    ]);
                } else {
                    $errorCount++;
                    Log::warning('Failed to cancel expired order', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'reason' => 'cancelAndUnreserve returned false'
                    ]);
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Exception occurred while cancelling expired order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Automatic cancellation of expired orders completed', [
            'total_processed' => $expiredOrders->count(),
            'cancelled_count' => $cancelledCount,
            'error_count' => $errorCount,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error('CancelExpiredOrdersJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
