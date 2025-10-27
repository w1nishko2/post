<?php

namespace App\Console\Commands;

use App\Models\CheckoutQueue;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Команда для обработки очереди оформления заказов
 * Запускается через CRON каждую минуту
 * 
 * Преимущества:
 * - Мгновенный ответ пользователю (добавили в очередь и сразу вернули session_id)
 * - Обработка в фоне без блокировки UI
 * - Повторные попытки при ошибках
 * - Контроль за нагрузкой (limit)
 */
class ProcessCheckoutQueue extends Command
{
    protected $signature = 'checkout:process-queue {--limit=100 : Количество заказов за раз}';
    protected $description = 'Обработка очереди оформления заказов';

    public function handle()
    {
        // Убираем ограничения
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '-1');

        $limit = $this->option('limit');

        // Получаем pending записи
        $items = CheckoutQueue::pending()
            ->limit($limit)
            ->get();

        if ($items->isEmpty()) {
            $this->info('✅ Очередь пуста');
            return 0;
        }

        $this->info("🔄 Обработка {$items->count()} заказов...");

        $processed = 0;
        $failed = 0;

        foreach ($items as $item) {
            try {
                $item->markAsProcessing();
                
                $order = $this->processCheckout($item);
                
                if ($order) {
                    $item->markAsCompleted($order->id);
                    $processed++;
                    $this->info("✅ Заказ #{$order->order_number} создан");
                } else {
                    throw new \Exception('Не удалось создать заказ');
                }

            } catch (\Exception $e) {
                $failed++;
                $item->markAsFailed($e->getMessage());
                
                $this->error("❌ Ошибка: {$e->getMessage()}");
                Log::error('Checkout queue processing failed', [
                    'checkout_id' => $item->id,
                    'session_id' => $item->session_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("✅ Обработано: {$processed}, Ошибок: {$failed}");
        
        Log::info('Checkout queue processed', [
            'processed' => $processed,
            'failed' => $failed,
            'total' => $items->count()
        ]);

        return 0;
    }

    /**
     * Обработка одного заказа из очереди
     */
    protected function processCheckout(CheckoutQueue $item): ?Order
    {
        DB::beginTransaction();

        try {
            $cartData = $item->cart_data;
            $userData = $item->user_data;
            $bot = TelegramBot::findOrFail($item->telegram_bot_id);

            // Проверяем наличие товаров и резервируем
            $reservationErrors = [];
            $totalAmount = 0;

            foreach ($cartData as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                
                if (!$product) {
                    $reservationErrors[] = "Товар ID {$cartItem['product_id']} не найден";
                    continue;
                }

                if (!$product->isAvailableForReservation($cartItem['quantity'])) {
                    $reservationErrors[] = "Товар \"{$product->name}\" недоступен в количестве {$cartItem['quantity']}";
                    continue;
                }

                if (!$product->reserve($cartItem['quantity'])) {
                    $reservationErrors[] = "Не удалось зарезервировать товар \"{$product->name}\"";
                }

                $totalAmount += $cartItem['price'] * $cartItem['quantity'];
            }

            if (!empty($reservationErrors)) {
                DB::rollBack();
                throw new \Exception(implode(', ', $reservationErrors));
            }

            // Создаём заказ
            $customerName = trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));

            $order = Order::create([
                'user_id' => $item->user_id,
                'session_id' => $item->session_cart_id,
                'telegram_chat_id' => $item->telegram_user_id,
                'telegram_bot_id' => $bot->id,
                'customer_name' => $customerName,
                'notes' => $item->notes,
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_PENDING,
                'expires_at' => Carbon::now('Europe/Moscow')->addHours(5),
            ]);

            // Создаём позиции заказа
            foreach ($cartData as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                
                if (!$product) {
                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_article' => $product->article,
                    'product_photo_url' => $product->photo_url,
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'total_price' => $cartItem['price'] * $cartItem['quantity'],
                ]);
            }

            // Отправляем уведомления асинхронно
            \App\Jobs\SendTelegramNotifications::dispatch($order, $bot);

            DB::commit();

            Log::info('Checkout processed successfully', [
                'checkout_id' => $item->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
