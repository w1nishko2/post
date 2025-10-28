<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CheckoutQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

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

        // Убедимся, что сессия запущена
        if (!Session::isStarted()) {
            Session::start();
        }

        $sessionId = Session::getId();
        $userId = Auth::id();
        $telegramUserId = $this->getTelegramUserId();
        $quantity = $request->quantity;

        Log::info('Adding product to cart', [
            'product_id' => $product->id,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'telegram_user_id' => $telegramUserId,
            'quantity' => $quantity
        ]);

        // Найти существующую позицию в корзине
        $cartItem = Cart::where('product_id', $product->id)
            ->where(function ($query) use ($sessionId, $userId, $telegramUserId) {
                $query->where('session_id', $sessionId);
                
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
                
                if ($telegramUserId) {
                    $query->orWhere('telegram_user_id', $telegramUserId);
                }
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
                'price' => $product->price_with_markup, // Обновляем цену с учетом наценки
            ]);
            
            Log::info('Cart item updated', ['cart_item_id' => $cartItem->id, 'new_quantity' => $newQuantity]);
        } else {
            // Создать новую позицию
            $newCartItem = Cart::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'telegram_user_id' => $telegramUserId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price_with_markup, // Цена с учетом наценки
            ]);
            
            Log::info('New cart item created', ['cart_item_id' => $newCartItem->id]);
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
            $sessionId = Session::getId();
            $userId = Auth::id();
            
            Log::info('Getting cart data', [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'telegram_user_id' => $this->getTelegramUserId()
            ]);
            
            $cartItems = $this->getCartItems();
            
            Log::info('Cart items count', [
                'count' => $cartItems->count()
            ]);
            
            $items = $cartItems->map(function ($item) {
                // Проверяем наличие продукта
                if (!$item->product) {
                    Log::warning('Cart item without product', ['cart_item_id' => $item->id]);
                    return null;
                }
                
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'article' => $item->product->article,
                        'photo_url' => $item->product->photo_url,
                        'main_photo_url' => $item->product->main_photo_url,
                    ],
                    'name' => $item->product->name,
                    'article' => $item->product->article,
                    'photo_url' => $item->product->photo_url,
                    'price' => $item->price,
                    'formatted_price' => $item->product->formatted_price_with_markup,
                    'quantity' => $item->quantity,
                    'available_quantity' => $item->product->quantity,
                    'total_price' => $item->total_price,
                    'formatted_total' => number_format((float) $item->total_price, 0, ',', ' ') . ' ₽',
                ];
            })->filter(); // Убираем null значения

            $totalAmount = $cartItems->sum('total_price');

            return response()->json([
                'success' => true,
                'items' => $items->values(), // Переиндексируем массив
                'total' => number_format((float) $totalAmount, 0, ',', ' ') . ' ₽',
                'formatted_total' => number_format((float) $totalAmount, 0, ',', ' ') . ' ₽',
                'total_amount' => $totalAmount,
                'count' => $cartItems->sum('quantity')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cart data retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных корзины: ' . $e->getMessage(),
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
            'price' => $cart->product->price_with_markup, // Обновляем цену с учетом наценки
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
        $telegramUserId = $this->getTelegramUserId();

        Cart::where(function ($query) use ($sessionId, $userId, $telegramUserId) {
            $query->where('session_id', $sessionId);
            
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
            
            if ($telegramUserId) {
                $query->orWhere('telegram_user_id', $telegramUserId);
            }
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
        $telegramUserId = $this->getTelegramUserId();

        return Cart::with('product')
            ->where(function ($query) use ($sessionId, $userId, $telegramUserId) {
                $query->where('session_id', $sessionId);
                
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
                
                if ($telegramUserId) {
                    $query->orWhere('telegram_user_id', $telegramUserId);
                }
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
     * Оформить заказ (БЫСТРЫЙ ВАРИАНТ - через очередь)
     * 
     * Вместо синхронного создания заказа:
     * 1. Быстро валидируем корзину
     * 2. Сохраняем данные в очередь checkout_queue
     * 3. Мгновенно возвращаем session_id
     * 4. CRON обрабатывает очередь в фоне
     * 
     * Преимущества:
     * - Мгновенный ответ (1-2 сек вместо 10-30 сек)
     * - Нет блокировки UI
     * - Масштабируемость при высокой нагрузке
     * - Повторные попытки при ошибках
     */
    public function checkout(Request $request)
    {
        Log::info('Checkout started (queue mode)', [
            'session_id' => Session::getId(),
            'user_id' => Auth::id(),
            'telegram_user_id' => $request->input('user_data.id'),
            'bot_short_name' => $request->input('bot_short_name')
        ]);

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

        // БЫСТРАЯ проверка наличия товаров (БЕЗ резервирования - резервируем в фоне)
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->product || !$cartItem->product->isAvailableForReservation($cartItem->quantity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Товар \"{$cartItem->product->name}\" недоступен в нужном количестве"
                ], 400);
            }
        }

        // Проверяем на дублирование заказов (не более одного заказа от пользователя за последние 10 секунд)
        $recentCheckout = CheckoutQueue::where('telegram_user_id', $request->user_data['id'])
                                      ->where('created_at', '>=', now()->subSeconds(10))
                                      ->first();

        if ($recentCheckout) {
            Log::warning('Duplicate checkout attempt detected', [
                'telegram_user_id' => $request->user_data['id'],
                'recent_session_id' => $recentCheckout->session_id,
                'session_id' => Session::getId()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Заказ уже оформляется. Подождите немного.',
                'checkout_session_id' => $recentCheckout->session_id
            ], 429);
        }

        try {
            // Генерируем уникальный ID сессии оформления
            $checkoutSessionId = (string) Str::uuid();

            // Подготавливаем данные корзины для очереди
            $cartData = $cartItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'photo_url' => $item->product->photo_url,
                ];
            })->toArray();

            // Добавляем в очередь оформления
            $checkoutItem = CheckoutQueue::create([
                'session_id' => $checkoutSessionId,
                'user_id' => Auth::id(),
                'session_cart_id' => Session::getId(),
                'telegram_user_id' => $request->user_data['id'],
                'telegram_bot_id' => $bot->id,
                'cart_data' => $cartData,
                'user_data' => $request->user_data,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Очищаем корзину СРАЗУ (товары уже сохранены в checkout_queue)
            $this->clearCartItems();

            Log::info('Checkout added to queue', [
                'checkout_session_id' => $checkoutSessionId,
                'telegram_user_id' => $request->user_data['id'],
                'bot_id' => $bot->id,
                'items_count' => count($cartData)
            ]);

            // ВАЖНО: Обрабатываем заказ СРАЗУ (fallback для отсутствия CRON)
            try {
                Artisan::call('checkout:process-queue', ['--limit' => 1]);
                Log::info('Checkout processed immediately after adding to queue');
            } catch (\Exception $e) {
                Log::warning('Failed to process checkout immediately: ' . $e->getMessage());
            }

            // Мгновенный ответ!
            return response()->json([
                'success' => true,
                'message' => 'Заказ принят! Обрабатывается...',
                'checkout_session_id' => $checkoutSessionId,
                'mode' => 'queue', // Указываем, что работаем через очередь
                'estimated_time' => '10-30 секунд', // Примерное время обработки
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to add checkout to queue', [
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
        $telegramUserId = $this->getTelegramUserId();

        Cart::where(function ($query) use ($sessionId, $userId, $telegramUserId) {
            $query->where('session_id', $sessionId);
            
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
            
            if ($telegramUserId) {
                $query->orWhere('telegram_user_id', $telegramUserId);
            }
        })->delete();
    }

    /**
     * Проверить права доступа к элементу корзины
     */
    private function canAccessCartItem(Cart $cart): bool
    {
        $sessionId = Session::getId();
        $userId = Auth::id();
        $telegramUserId = $this->getTelegramUserId();

        return $cart->session_id === $sessionId || 
               ($userId && $cart->user_id === $userId) ||
               ($telegramUserId && $cart->telegram_user_id === $telegramUserId);
    }

    /**
     * Получить Telegram User ID из заголовков запроса
     */
    private function getTelegramUserId()
    {
        $initData = request()->header('X-Telegram-Web-App-Init-Data') ?? request()->input('_auth');
        
        if (!$initData) {
            return null;
        }

        parse_str($initData, $data);
        
        if (isset($data['user'])) {
            $userData = json_decode($data['user'], true);
            return $userData['id'] ?? null;
        }

        return null;
    }

    /**
     * Проверить статус обработки заказа по checkout_session_id
     * 
     * Возвращает:
     * - pending: заказ в очереди
     * - processing: заказ обрабатывается
     * - completed: заказ создан, данные заказа в ответе
     * - failed: ошибка при обработке
     */
    public function checkCheckoutStatus(Request $request)
    {
        $request->validate([
            'checkout_session_id' => 'required|string|exists:checkout_queue,session_id'
        ]);

        try {
            $checkout = CheckoutQueue::where('session_id', $request->checkout_session_id)->first();

            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сессия оформления не найдена'
                ], 404);
            }

            $response = [
                'success' => true,
                'status' => $checkout->status,
                'attempts' => $checkout->attempts,
            ];

            // Если заказ создан - возвращаем данные заказа
            if ($checkout->status === 'completed' && $checkout->order) {
                $order = $checkout->order;
                $response['order'] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->formatted_total,
                    'status' => $order->status_label,
                    'customer_name' => $order->customer_name,
                    'created_at' => $order->created_at->format('d.m.Y H:i'),
                ];
                $response['message'] = 'Заказ успешно оформлен!';
            } elseif ($checkout->status === 'failed') {
                $response['message'] = 'Ошибка при оформлении заказа';
                $response['error'] = $checkout->error_message;
            } elseif ($checkout->status === 'processing') {
                $response['message'] = 'Заказ обрабатывается...';
            } else {
                $response['message'] = 'Заказ в очереди на обработку';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to check checkout status', [
                'checkout_session_id' => $request->checkout_session_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при проверке статуса заказа'
            ], 500);
        }
    }

    /**
     * Оформление заказа из веб-версии (для браузера)
     * Создает заказ с резервированием товаров и отправляет данные администратору бота в Telegram
     */
    public function webCheckout(Request $request)
    {
        try {
            // Валидация данных
            $request->validate([
                'bot_short_name' => 'required|string',
                'customer_name' => 'required|string|min:2|max:100',
                'customer_phone' => 'required|string|regex:/^[\+]?[0-9]{10,15}$/',
                'customer_comment' => 'nullable|string|max:500',
            ], [
                'customer_name.required' => 'Введите ваше имя',
                'customer_name.min' => 'Имя должно содержать минимум 2 символа',
                'customer_phone.required' => 'Введите номер телефона',
                'customer_phone.regex' => 'Некорректный формат номера телефона',
            ]);

            // Получаем данные корзины
            $cartItems = $this->getCartItems();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Корзина пуста'
                ], 400);
            }

            // Получаем бота по mini_app_short_name
            $bot = \App\Models\TelegramBot::where('mini_app_short_name', $request->bot_short_name)->first();

            if (!$bot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Бот не найден'
                ], 404);
            }

            // Проверяем, настроен ли admin_telegram_id для уведомлений
            if (empty($bot->admin_telegram_id)) {
                Log::warning('Admin Telegram ID not configured for bot', [
                    'bot_id' => $bot->id,
                    'bot_username' => $bot->bot_username
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Уведомления администратору не настроены. Пожалуйста, свяжитесь с владельцем магазина.'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Проверяем наличие товаров и резервируем их
                $reservationErrors = [];
                $totalAmount = 0;

                foreach ($cartItems as $cartItem) {
                    if (!$cartItem->product) {
                        $reservationErrors[] = "Товар ID {$cartItem->product_id} не найден";
                        continue;
                    }

                    if (!$cartItem->product->isAvailableForReservation($cartItem->quantity)) {
                        $reservationErrors[] = "Товар \"{$cartItem->product->name}\" недоступен в нужном количестве";
                        continue;
                    }

                    // Резервируем товар
                    if (!$cartItem->product->reserve($cartItem->quantity)) {
                        $reservationErrors[] = "Не удалось зарезервировать товар \"{$cartItem->product->name}\"";
                        continue;
                    }

                    $totalAmount += $cartItem->total_price;
                }

                // Если есть ошибки резервирования - откатываем транзакцию
                if (!empty($reservationErrors)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Ошибки при резервировании товаров: ' . implode(', ', $reservationErrors)
                    ], 400);
                }

                // Создаём заказ в базе данных
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'session_id' => Session::getId(),
                    'telegram_bot_id' => $bot->id,
                    'customer_name' => $request->customer_name,
                    'notes' => $request->customer_phone . ($request->customer_comment ? "\n" . $request->customer_comment : ''),
                    'total_amount' => $totalAmount,
                    'status' => Order::STATUS_PENDING,
                    'expires_at' => \Carbon\Carbon::now('Europe/Moscow')->addHours(5),
                ]);

                // Создаём позиции заказа
                foreach ($cartItems as $cartItem) {
                    if (!$cartItem->product) {
                        continue;
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'product_article' => $cartItem->product->article,
                        'product_photo_url' => $cartItem->product->photo_url,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'total_price' => $cartItem->total_price,
                    ]);
                }

                DB::commit();

                Log::info('Web checkout order created', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $request->customer_name,
                    'total_amount' => $totalAmount
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to create web checkout order', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Произошла ошибка при создании заказа. Попробуйте позже.'
                ], 500);
            }

            // Формируем сообщение для администратора
            $message = "🛒 <b>Новый заказ с сайта!</b>\n\n";
            $message .= "📋 <b>Заказ:</b> #{$order->order_number}\n";
            $message .= "👤 <b>Клиент:</b> " . htmlspecialchars($request->customer_name) . "\n";
            $message .= "📞 <b>Телефон:</b> " . htmlspecialchars($request->customer_phone) . "\n";
            
            if ($request->customer_comment) {
                $message .= "💬 <b>Комментарий:</b> " . htmlspecialchars($request->customer_comment) . "\n";
            }
            
            $message .= "\n<b>📦 Состав заказа:</b>\n";
            
            foreach ($order->items as $item) {
                $message .= "\n• " . htmlspecialchars($item->product_name) . "\n";
                $message .= "  Количество: {$item->quantity} шт.\n";
                $message .= "  Цена: " . number_format($item->total_price, 0, ',', ' ') . " ₽\n";
            }
            
            $message .= "\n💰 <b>Итого:</b> " . number_format($totalAmount, 0, ',', ' ') . " ₽";
            $message .= "\n\n⏰ <b>Действителен до:</b> " . $order->formatted_expires_at;

            // Отправляем уведомление администратору через Job
            \App\Jobs\SendTelegramNotifications::dispatch($order, $bot);

            // Очищаем корзину
            $this->clearCartItems();

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно оформлен! Мы свяжемся с вами в ближайшее время.',
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации данных',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to process web checkout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при оформлении заказа. Попробуйте позже.'
            ], 500);
        }
    }
}
