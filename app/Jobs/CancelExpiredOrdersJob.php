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
        // Находим все просроченные заказы
        $expiredOrders = Order::expired()->get();

        if ($expiredOrders->isEmpty()) {
            return;
        }

        $errorCount = 0;

        foreach ($expiredOrders as $order) {
            try {
                if (!$order->cancelAndUnreserve()) {
                    $errorCount++;
                    Log::warning('Failed to cancel expired order', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number
                    ]);
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Exception while cancelling expired order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
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
