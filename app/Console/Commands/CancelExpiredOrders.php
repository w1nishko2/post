<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отменить просроченные заказы и снять резерв товаров';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Поиск просроченных заказов...');

        // Находим все просроченные заказы
        $expiredOrders = Order::expired()->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Просроченных заказов не найдено.');
            return 0;
        }

        $cancelledCount = 0;
        $errorCount = 0;

        $this->info("Найдено просроченных заказов: {$expiredOrders->count()}");

        foreach ($expiredOrders as $order) {
            try {
                $this->line("Отменяем заказ #{$order->order_number}...");
                
                if ($order->cancelAndUnreserve()) {
                    $cancelledCount++;
                    $this->info("✅ Заказ #{$order->order_number} отменен");
                    
                    Log::info('Expired order cancelled automatically', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'expired_at' => $order->expires_at,
                    ]);
                } else {
                    $errorCount++;
                    $this->error("❌ Не удалось отменить заказ #{$order->order_number}");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("❌ Ошибка при отмене заказа #{$order->order_number}: {$e->getMessage()}");
                
                Log::error('Failed to cancel expired order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Обработка завершена:");
        $this->info("• Отменено заказов: {$cancelledCount}");
        if ($errorCount > 0) {
            $this->warn("• Ошибок: {$errorCount}");
        }

        return 0;
    }
}
