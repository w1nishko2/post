<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TelegramBot;
use App\Jobs\SendTelegramNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Создать заказ из корзины с проверкой запасов
     */
    public function createOrderFromCart(array $cartItems, array $customerData, TelegramBot $bot): array
    {
        try {
            return DB::transaction(function () use ($cartItems, $customerData, $bot) {
                // Получаем товары с блокировкой для обновления
                $productIds = collect($cartItems)->pluck('id')->toArray();
                $products = Product::where('telegram_bot_id', $bot->id)
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $orderItems = [];
                $totalAmount = 0;
                $issues = [];

                // Проверяем наличие и резервируем товары
                foreach ($cartItems as $item) {
                    $product = $products->get($item['id']);
                    $requestedQuantity = max(1, intval($item['quantity'] ?? 1));

                    if (!$product) {
                        $issues[] = ['type' => 'product_not_found', 'product_id' => $item['id']];
                        continue;
                    }

                    if (!$product->is_active) {
                        $issues[] = ['type' => 'product_inactive', 'product_id' => $item['id']];
                        continue;
                    }

                    if ($product->quantity < $requestedQuantity) {
                        $issues[] = [
                            'type' => 'insufficient_quantity',
                            'product_id' => $item['id'],
                            'available' => $product->quantity,
                            'requested' => $requestedQuantity
                        ];
                        continue;
                    }

                    // Атомарно уменьшаем количество
                    $affected = Product::where('id', $product->id)
                        ->where('quantity', '>=', $requestedQuantity)
                        ->decrement('quantity', $requestedQuantity);

                    if ($affected === 0) {
                        // Кто-то другой купил товар между проверкой и уменьшением
                        $issues[] = [
                            'type' => 'concurrent_purchase',
                            'product_id' => $item['id'],
                            'message' => 'Товар был куплен другим покупателем'
                        ];
                        continue;
                    }

                    $itemTotal = $product->price * $requestedQuantity;
                    $totalAmount += $itemTotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_article' => $product->article,
                        'product_photo_url' => $product->photo_url,
                        'quantity' => $requestedQuantity,
                        'price' => $product->price,
                        'total_price' => $itemTotal,
                    ];
                }

                if (!empty($issues)) {
                    // Откатываем транзакцию если есть проблемы
                    throw new \Exception('Cart validation failed: ' . json_encode($issues));
                }

                if (empty($orderItems)) {
                    throw new \Exception('No valid items in cart');
                }

                // Создаем заказ
                $order = Order::create([
                    'telegram_bot_id' => $bot->id,
                    'order_number' => $this->generateOrderNumber(),
                    'customer_name' => $customerData['name'] ?? null,
                    'customer_phone' => $customerData['phone'] ?? null,
                    'customer_email' => $customerData['email'] ?? null,
                    'customer_address' => $customerData['address'] ?? null,
                    'telegram_chat_id' => $customerData['telegram_chat_id'] ?? null,
                    'notes' => $customerData['notes'] ?? null,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                ]);

                // Создаем элементы заказа
                foreach ($orderItems as $itemData) {
                    $order->items()->create($itemData);
                }

                // Очищаем корзину (если есть session_id или user_id)
                if (isset($customerData['session_id'])) {
                    Cart::where('session_id', $customerData['session_id'])->delete();
                }

                if (isset($customerData['user_id'])) {
                    Cart::where('user_id', $customerData['user_id'])->delete();
                }

                // Отправляем уведомления асинхронно
                try {
                    SendTelegramNotifications::dispatch($order, $bot);
                } catch (\Exception $e) {
                    Log::warning('Failed to dispatch notification job', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }

                return [
                    'success' => true,
                    'order' => $order->load('items'),
                    'order_number' => $order->order_number,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'bot_id' => $bot->id,
                'cart_items' => $cartItems,
                'customer_data' => $customerData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Если это известная ошибка валидации корзины
            if (str_contains($e->getMessage(), 'Cart validation failed')) {
                $issues = json_decode(str_replace('Cart validation failed: ', '', $e->getMessage()), true);
                return [
                    'success' => false,
                    'issues' => $issues ?? [],
                    'message' => 'Не удалось создать заказ из-за проблем с товарами'
                ];
            }

            return [
                'success' => false,
                'message' => 'Произошла ошибка при создании заказа'
            ];
        }
    }

    /**
     * Генерировать уникальный номер заказа
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}