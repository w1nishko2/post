<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Добавить товар в корзину
     */
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->quantity,
        ]);

        if (!$product->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Товар недоступен для покупки'
            ], 400);
        }

        $sessionId = Session::getId();
        $userId = Auth::id();
        $quantity = $request->quantity;

        // Найти существующую позицию в корзине
        $cartItem = Cart::where('product_id', $product->id)
            ->where(function ($query) use ($sessionId, $userId) {
                $query->where('session_id', $sessionId)
                      ->orWhere('user_id', $userId);
            })
            ->first();

        if ($cartItem) {
            // Обновить количество
            $newQuantity = $cartItem->quantity + $quantity;
            
            if ($newQuantity > $product->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity . ' шт.'
                ], 400);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
                'price' => $product->price, // Обновляем цену
            ]);
        } else {
            // Создать новую позицию
            Cart::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Показать корзину
     */
    public function index()
    {
        $cartItems = $this->getCartItems();
        
        return view('cart.index', compact('cartItems'));
    }

    /**
     * Получить данные корзины (API для Mini App)
     */
    public function getCartData()
    {
        try {
            $cartItems = $this->getCartItems();
            
            $items = $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'article' => $item->product->article,
                    'photo_url' => $item->product->photo_url,
                    'price' => $item->price,
                    'formatted_price' => $item->product->formatted_price,
                    'quantity' => $item->quantity,
                    'available_quantity' => $item->product->quantity,
                    'total_price' => $item->total_price,
                    'formatted_total' => number_format((float) $item->total_price, 0, ',', ' ') . ' ₽',
                ];
            });

            $totalAmount = $cartItems->sum('total_price');

            return response()->json([
                'success' => true,
                'items' => $items,
                'total' => number_format((float) $totalAmount, 0, ',', ' ') . ' ₽',
                'formatted_total' => number_format((float) $totalAmount, 0, ',', ' ') . ' ₽',
                'total_amount' => $totalAmount,
                'count' => $cartItems->sum('quantity')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cart data retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных корзины',
                'items' => [],
                'total' => '0 ₽',
                'total_amount' => 0,
                'count' => 0
            ], 500);
        }
    }

    /**
     * Обновить количество товара в корзине
     */
    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $cart->product->quantity,
        ]);

        // Проверяем права доступа
        if (!$this->canAccessCartItem($cart)) {
            abort(403);
        }

        $cart->update([
            'quantity' => $request->quantity,
            'price' => $cart->product->price, // Обновляем цену
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Количество обновлено',
            'item_total' => $cart->total_price,
            'formatted_item_total' => number_format($cart->total_price, 0, ',', ' ') . ' ₽',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Удалить товар из корзины
     */
    public function remove(Cart $cart)
    {
        // Проверяем права доступа
        if (!$this->canAccessCartItem($cart)) {
            abort(403);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Товар удален из корзины',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Очистить корзину
     */
    public function clear()
    {
        $sessionId = Session::getId();
        $userId = Auth::id();

        Cart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId)
                  ->orWhere('user_id', $userId);
        })->delete();

        return response()->json([
            'success' => true,
            'message' => 'Корзина очищена'
        ]);
    }

    /**
     * Получить количество товаров в корзине (API)
     */
    public function getCount()
    {
        try {
            $count = $this->getCartCount();
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении счетчика корзины',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Получить товары в корзине
     */
    private function getCartItems()
    {
        $sessionId = Session::getId();
        $userId = Auth::id();

        return Cart::with('product')
            ->where(function ($query) use ($sessionId, $userId) {
                $query->where('session_id', $sessionId)
                      ->orWhere('user_id', $userId);
            })
            ->get();
    }

    /**
     * Получить количество товаров в корзине
     */
    private function getCartCount(): int
    {
        return $this->getCartItems()->sum('quantity');
    }

    /**
     * Оформить заказ
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'bot_short_name' => 'required|string|exists:telegram_bots,mini_app_short_name',
            'user_data' => 'required|array',
            'user_data.id' => 'required|integer',
            'user_data.first_name' => 'required|string',
            'user_data.last_name' => 'nullable|string',
            'user_data.username' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $cartItems = $this->getCartItems();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Корзина пуста'
            ], 400);
        }

        // Найти бота по short_name
        $bot = \App\Models\TelegramBot::where('mini_app_short_name', $request->bot_short_name)
                                     ->where('is_active', true)
                                     ->first();

        if (!$bot) {
            return response()->json([
                'success' => false,
                'message' => 'Бот не найден или не активен'
            ], 404);
        }

        // Проверить наличие товаров
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->product || !$cartItem->product->isAvailable() || $cartItem->product->quantity < $cartItem->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Товар \"{$cartItem->product->name}\" недоступен в нужном количестве"
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            // Создать заказ
            $customerName = trim(($request->user_data['first_name'] ?? '') . ' ' . ($request->user_data['last_name'] ?? ''));
            $totalAmount = $cartItems->sum('total_price');

            $order = \App\Models\Order::create([
                'user_id' => Auth::id(),
                'session_id' => Session::getId(),
                'telegram_chat_id' => $request->user_data['id'],
                'telegram_bot_id' => $bot->id,
                'customer_name' => $customerName,
                'notes' => $request->notes,
                'total_amount' => $totalAmount,
                'status' => \App\Models\Order::STATUS_PENDING,
            ]);

            // Создать позиции заказа и уменьшить количество товаров
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                // Создать позицию заказа
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_article' => $product->article,
                    'product_photo_url' => $product->photo_url,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total_price' => $cartItem->total_price,
                ]);

                // Уменьшить количество товара
                $product->decrement('quantity', $cartItem->quantity);
            }

            // Отправить уведомления через Telegram
            $telegramService = app(\App\Services\TelegramBotService::class);
            
            // Уведомление администратору
            $adminNotificationSent = $telegramService->sendOrderNotificationToAdmin($bot, $order);
            
            // Уведомление клиенту
            $customerNotificationSent = $telegramService->sendOrderConfirmationToCustomer($bot, $order);

            // Очистить корзину после успешного заказа
            $this->clearCartItems();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно оформлен! С вами свяжутся в ближайшее время.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->formatted_total,
                    'status' => $order->status_label,
                ],
                'notifications' => [
                    'admin_sent' => $adminNotificationSent,
                    'customer_sent' => $customerNotificationSent,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'bot_id' => $bot->id,
                'user_data' => $request->user_data,
                'cart_items_count' => $cartItems->count(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при оформлении заказа. Попробуйте позже.'
            ], 500);
        }
    }

    /**
     * Очистить корзину (приватный метод)
     */
    private function clearCartItems(): void
    {
        $sessionId = Session::getId();
        $userId = Auth::id();

        $query = Cart::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $query->delete();
    }

    /**
     * Проверить права доступа к элементу корзины
     */
    private function canAccessCartItem(Cart $cart): bool
    {
        $sessionId = Session::getId();
        $userId = Auth::id();

        return $cart->session_id === $sessionId || 
               ($userId && $cart->user_id === $userId);
    }
}
