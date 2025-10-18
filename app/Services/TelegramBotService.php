<?php

namespace App\Services;

use App\Models\TelegramBot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    /**
     * Получить настроенный HTTP клиент для Telegram API
     */
    private function getTelegramHttpClient()
    {
        return Http::timeout(60) // Увеличиваем общий таймаут
            ->withOptions([
                'verify' => false, // Отключаем проверку SSL для решения проблем с сертификатами
                'http_errors' => false,
                'connect_timeout' => 30, // Увеличиваем таймаут подключения
                'timeout' => 60, // Общий таймаут
                'read_timeout' => 45, // Таймаут чтения
                'curl' => [
                    CURLOPT_CONNECTTIMEOUT => 30,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT => 'Laravel HTTP Client',
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Принудительно используем IPv4
                ]
            ])
            ->retry(3, 2000, null, false); // 3 попытки с задержкой 2 секунды
    }

    /**
     * Проверить валидность токена бота
     */
    public function validateBotToken(string $token): bool
    {
        try {
            // Дополнительная проверка формата токена
            if (!preg_match('/^\d+:[a-zA-Z0-9_-]+$/', $token)) {
                Log::warning('Неверный формат токена бота', [
                    'token_format' => 'Invalid format'
                ]);
                return false;
            }

            // Первая попытка с Laravel HTTP Client
            try {
                $response = $this->getTelegramHttpClient()
                    ->get("https://api.telegram.org/bot{$token}/getMe");
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['ok']) && $data['ok']) {
                        Log::info('Токен бота валиден (Laravel HTTP)', [
                            'bot_username' => $data['result']['username'] ?? 'unknown'
                        ]);
                        return true;
                    }
                }
                
                Log::warning('Первая попытка неудачна, пробуем cURL напрямую', [
                    'status' => $response->status(),
                ]);
            } catch (\Exception $httpException) {
                Log::warning('Laravel HTTP Client неудачен, пробуем cURL', [
                    'error' => $httpException->getMessage()
                ]);
            }

            // Вторая попытка с прямым cURL
            $isValid = $this->validateTokenWithCurl($token);
            if ($isValid) {
                Log::info('Токен бота валиден (cURL)', ['token' => substr($token, 0, 10) . '...']);
                return true;
            }
            
            Log::warning('Токен бота не валиден', [
                'token' => substr($token, 0, 10) . '...'
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Критическая ошибка при валидации токена бота', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Дополнительная проверка токена через прямой cURL
     */
    private function validateTokenWithCurl(string $token): bool
    {
        $url = "https://api.telegram.org/bot{$token}/getMe";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Laravel Bot Validator/1.0',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            Log::error('cURL ошибка при проверке токена', [
                'curl_error' => $curlError,
                'http_code' => $httpCode
            ]);
            return false;
        }
        
        if ($httpCode !== 200) {
            Log::warning('HTTP код не 200 при проверке токена', [
                'http_code' => $httpCode,
                'response' => substr($response, 0, 200)
            ]);
            return false;
        }
        
        $data = json_decode($response, true);
        return isset($data['ok']) && $data['ok'];
    }

    /**
     * Получить информацию о боте
     */
    public function getBotInfo(string $token): ?array
    {
        try {
            // Первая попытка с Laravel HTTP Client
            try {
                $response = $this->getTelegramHttpClient()
                    ->get("https://api.telegram.org/bot{$token}/getMe");
                
                if ($response->successful() && $response->json('ok')) {
                    return $response->json('result');
                }
                
                Log::warning('Laravel HTTP не сработал для getBotInfo, пробуем cURL');
            } catch (\Exception $httpException) {
                Log::warning('Laravel HTTP Client исключение в getBotInfo', [
                    'error' => $httpException->getMessage()
                ]);
            }
            
            // Вторая попытка с прямым cURL
            return $this->getBotInfoWithCurl($token);
            
        } catch (\Exception $e) {
            Log::error('Критическая ошибка при получении информации о боте', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Получить информацию о боте через прямой cURL
     */
    private function getBotInfoWithCurl(string $token): ?array
    {
        $url = "https://api.telegram.org/bot{$token}/getMe";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Laravel Bot Info/1.0',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            Log::error('cURL ошибка при получении информации о боте', [
                'curl_error' => $curlError,
                'http_code' => $httpCode
            ]);
            return null;
        }
        
        if ($httpCode !== 200) {
            Log::warning('HTTP код не 200 при получении информации о боте', [
                'http_code' => $httpCode,
                'response' => substr($response, 0, 200)
            ]);
            return null;
        }
        
        $data = json_decode($response, true);
        if (isset($data['ok']) && $data['ok'] && isset($data['result'])) {
            return $data['result'];
        }
        
        return null;
    }    /**
     * Установить webhook для бота
     */
    public function setWebhook(TelegramBot $bot, string $webhookUrl): bool
    {
        try {
            $response = $this->getTelegramHttpClient()
                ->post("https://api.telegram.org/bot{$bot->bot_token}/setWebhook", [
                    'url' => $webhookUrl,
                    'allowed_updates' => ['message', 'callback_query', 'inline_query', 'web_app_data'],
                    'drop_pending_updates' => true,
                    'secret_token' => $this->generateSecretToken()
                ]);

            $result = $response->successful() && $response->json('ok');
            
            if ($result) {
                Log::info('Webhook успешно установлен', [
                    'bot_id' => $bot->id,
                    'webhook_url' => $webhookUrl
                ]);
            } else {
                Log::error('Ошибка при установке webhook', [
                    'bot_id' => $bot->id,
                    'webhook_url' => $webhookUrl,
                    'response' => $response->json()
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Исключение при установке webhook', [
                'bot_id' => $bot->id,
                'webhook_url' => $webhookUrl,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Удалить webhook бота
     */
    public function deleteWebhook(TelegramBot $bot): bool
    {
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/deleteWebhook", [
                'drop_pending_updates' => true
            ]);

            $result = $response->successful() && $response->json('ok');
            
            if ($result) {
                Log::info('Webhook успешно удален', ['bot_id' => $bot->id]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении webhook', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Установить кнопку меню для бота
     */
    public function setChatMenuButton(TelegramBot $bot, array $menuButton): bool
    {
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/setChatMenuButton", [
                'menu_button' => json_encode($menuButton)
            ]);

            $result = $response->successful() && $response->json('ok');
            
            if ($result) {
                Log::info('Кнопка меню успешно установлена', [
                    'bot_id' => $bot->id,
                    'menu_button' => $menuButton
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Ошибка при установке кнопки меню', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Установить команды бота
     */
    public function setMyCommands(TelegramBot $bot, array $commands): bool
    {
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/setMyCommands", [
                'commands' => json_encode($commands)
            ]);

            $result = $response->successful() && $response->json('ok');
            
            if ($result) {
                Log::info('Команды бота успешно установлены', [
                    'bot_id' => $bot->id,
                    'commands' => $commands
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Ошибка при установке команд бота', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправить сообщение пользователю
     */
    public function sendMessage(TelegramBot $bot, int $chatId, string $text, array $options = []): bool
    {
        try {
            $data = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ], $options);

            // Попробуем сначала HTTP с оптимизированными настройками
            try {
                $response = Http::timeout(10) // Уменьшили таймаут
                    ->withOptions([
                        'verify' => false, // Отключаем SSL проверку
                        'connect_timeout' => 3, // Быстрое подключение
                    ])
                    ->retry(1, 500) // Уменьшили количество попыток и задержку
                    ->post("https://api.telegram.org/bot{$bot->bot_token}/sendMessage", $data);

                if ($response->successful() && $response->json('ok')) {
                    return true;
                }
            } catch (\Exception $httpException) {
                Log::warning('HTTP request failed, trying cURL', [
                    'bot_id' => $bot->id,
                    'chat_id' => $chatId,
                    'http_error' => $httpException->getMessage()
                ]);
            }

            // Если HTTP не сработал, используем cURL напрямую
            return $this->sendMessageWithCurl($bot->bot_token, $chatId, $text, $options);

        } catch (\Exception $e) {
            Log::error('Ошибка при отправке сообщения', [
                'bot_id' => $bot->id,
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправить сообщение через cURL (альтернативный метод)
     */
    private function sendMessageWithCurl(string $botToken, int $chatId, string $text, array $options = []): bool
    {
        $data = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $options);

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15, // Уменьшаем таймаут
            CURLOPT_CONNECTTIMEOUT => 5, // Уменьшаем таймаут подключения
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ],
            CURLOPT_USERAGENT => 'Laravel Telegram Bot Service',
            CURLOPT_SSL_VERIFYPEER => false, // ОТКЛЮЧАЕМ проверку SSL
            CURLOPT_SSL_VERIFYHOST => 0, // ОТКЛЮЧАЕМ проверку хоста
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Принудительно IPv4
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($error)) {
            Log::error('cURL error in sendMessageWithCurl', [
                'error' => $error,
                'chat_id' => $chatId,
                'url' => $url
            ]);
            return false;
        }

        if ($httpCode !== 200) {
            Log::error('HTTP error in sendMessageWithCurl', [
                'http_code' => $httpCode,
                'response' => $response,
                'chat_id' => $chatId
            ]);
            return false;
        }

        $result = json_decode($response, true);
        return isset($result['ok']) && $result['ok'];
    }

    /**
     * Проверить настройку Mini App
     */
    public function validateMiniApp(TelegramBot $bot): array
    {
        $errors = [];
        
        if (empty($bot->mini_app_url)) {
            $errors[] = 'URL Mini App не указан';
        } elseif (!filter_var($bot->mini_app_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Некорректный URL Mini App';
        }

        if (empty($bot->mini_app_short_name)) {
            $errors[] = 'Короткое имя Mini App не указано';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $bot->mini_app_short_name)) {
            $errors[] = 'Короткое имя может содержать только буквы, цифры и подчеркивания';
        }

        // Проверяем доступность URL
        if (!empty($bot->mini_app_url)) {
            try {
                $response = Http::timeout(5)->head($bot->mini_app_url);
                if (!$response->successful()) {
                    $errors[] = 'Mini App URL недоступен (код ответа: ' . $response->status() . ')';
                }
            } catch (\Exception $e) {
                $errors[] = 'Mini App URL недоступен: ' . $e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Настроить Mini App полностью
     */
    public function setupMiniApp(TelegramBot $bot, array $config): bool
    {
        try {
            // Валидируем конфигурацию
            $errors = $this->validateMiniApp($bot);
            if (!empty($errors)) {
                Log::error('Ошибка валидации Mini App', [
                    'bot_id' => $bot->id,
                    'errors' => $errors
                ]);
                return false;
            }

            // Устанавливаем кнопку меню
            $menuButton = [
                'type' => 'web_app',
                'text' => $config['menu_button_text'] ?? 'Открыть приложение',
                'web_app' => [
                    'url' => $bot->mini_app_url
                ]
            ];

            if (!$this->setChatMenuButton($bot, $menuButton)) {
                return false;
            }

            // Устанавливаем команды по умолчанию для Mini App
            $defaultCommands = [
                [
                    'command' => 'start',
                    'description' => 'Запустить приложение'
                ],
                [
                    'command' => 'help',
                    'description' => 'Помощь'
                ]
            ];

            $commands = $config['commands'] ?? $defaultCommands;
            $this->setMyCommands($bot, $commands);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при настройке Mini App', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Получить URL для webhook'а
     */
    public function getWebhookUrl(TelegramBot $bot): string
    {
        return route('telegram.webhook', ['bot' => $bot->id]);
    }

    /**
     * Генерировать секретный токен для webhook'а
     */
    private function generateSecretToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Проверить статус webhook'а
     */
    public function getWebhookInfo(TelegramBot $bot): ?array
    {
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$bot->bot_token}/getWebhookInfo");
            
            if ($response->successful() && $response->json('ok')) {
                return $response->json('result');
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при получении информации о webhook', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Отправить уведомление администратору о покупке
     */
    public function sendPurchaseNotification(TelegramBot $bot, array $purchaseData): bool
    {
        if (!$bot->hasAdminNotifications()) {
            return false;
        }

        try {
            $message = $this->formatPurchaseMessage($purchaseData);
            
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/sendMessage", [
                'chat_id' => $bot->admin_telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true
            ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('Уведомление о покупке отправлено администратору', [
                    'bot_id' => $bot->id,
                    'admin_id' => $bot->admin_telegram_id,
                    'purchase_data' => $purchaseData
                ]);
                return true;
            }

            Log::error('Ошибка при отправке уведомления о покупке', [
                'bot_id' => $bot->id,
                'admin_id' => $bot->admin_telegram_id,
                'response' => $response->json()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Исключение при отправке уведомления о покупке', [
                'bot_id' => $bot->id,
                'admin_id' => $bot->admin_telegram_id,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Форматировать сообщение о покупке для администратора
     */
    private function formatPurchaseMessage(array $purchaseData): string
    {
        $message = "🛍 <b>Новая покупка!</b>\n\n";
        
        if (isset($purchaseData['user_name'])) {
            $message .= "👤 <b>Покупатель:</b> {$purchaseData['user_name']}\n";
        }
        
        if (isset($purchaseData['user_username'])) {
            $message .= "📱 <b>Username:</b> @{$purchaseData['user_username']}\n";
        }
        
        if (isset($purchaseData['products']) && is_array($purchaseData['products'])) {
            $message .= "\n📦 <b>Товары:</b>\n";
            $totalAmount = 0;
            
            foreach ($purchaseData['products'] as $product) {
                $name = $product['name'] ?? 'Неизвестный товар';
                $quantity = $product['quantity'] ?? 1;
                $price = $product['price'] ?? 0;
                $total = $price * $quantity;
                $totalAmount += $total;
                
                $message .= "• {$name} x{$quantity} = " . number_format($total, 0, ',', ' ') . " ₽\n";
            }
            
            $message .= "\n💰 <b>Общая сумма:</b> " . number_format($totalAmount, 0, ',', ' ') . " ₽";
        }
        
        if (isset($purchaseData['order_id'])) {
            $message .= "\n\n📋 <b>ID заказа:</b> {$purchaseData['order_id']}";
        }
        
        $message .= "\n\n⏰ <b>Время:</b> " . date('d.m.Y H:i:s');
        
        return $message;
    }

    /**
     * Отправить уведомление о заказе администратору
     */
    public function sendOrderNotificationToAdmin(\App\Models\TelegramBot $bot, \App\Models\Order $order): bool
    {
        if (!$bot->admin_telegram_id) {
            Log::warning('Bot has no admin_telegram_id configured', ['bot_id' => $bot->id]);
            return false;
        }

        $message = $this->buildAdminOrderMessage($order);
        
        // Создаем inline клавиатуру с кнопкой подтверждения оплаты
        $inlineKeyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '✅ Подтвердить оплату',
                        'callback_data' => "confirm_payment_{$order->id}"
                    ],
                    [
                        'text' => '❌ Отменить заказ',
                        'callback_data' => "cancel_order_{$order->id}"
                    ]
                ]
            ]
        ];

        $success = $this->sendMessage($bot, $bot->admin_telegram_id, $message, [
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode($inlineKeyboard)
        ]);

        if ($success) {
            Log::info('Admin order notification sent successfully', [
                'bot_id' => $bot->id,
                'order_id' => $order->id,
                'admin_telegram_id' => $bot->admin_telegram_id
            ]);
        } else {
            Log::error('Failed to send admin order notification', [
                'bot_id' => $bot->id,
                'order_id' => $order->id,
                'admin_telegram_id' => $bot->admin_telegram_id
            ]);
        }

        return $success;
    }

    /**
     * Отправить подтверждение заказа клиенту
     */
    public function sendOrderConfirmationToCustomer(\App\Models\TelegramBot $bot, \App\Models\Order $order): bool
    {
        if (!$order->telegram_chat_id) {
            Log::warning('Order has no telegram_chat_id', ['order_id' => $order->id]);
            return false;
        }

        $message = $this->buildCustomerOrderMessage($order);

        $success = $this->sendMessage($bot, $order->telegram_chat_id, $message, [
            'disable_web_page_preview' => true,
        ]);

        if ($success) {
            Log::info('Customer order confirmation sent successfully', [
                'bot_id' => $bot->id,
                'order_id' => $order->id,
                'customer_telegram_id' => $order->telegram_chat_id
            ]);
        } else {
            Log::error('Failed to send customer order confirmation', [
                'bot_id' => $bot->id,
                'order_id' => $order->id,
                'customer_telegram_id' => $order->telegram_chat_id
            ]);
        }

        return $success;
    }

    /**
     * Создать сообщение для администратора о новом заказе
     */
    private function buildAdminOrderMessage(\App\Models\Order $order): string
    {
        $message = "🔔 <b>НОВЫЙ ЗАКАЗ!</b>\n\n";
        $message .= "📋 <b>Номер заказа:</b> {$order->order_number}\n";
        $message .= "💰 <b>Сумма:</b> {$order->formatted_total}\n";
        $message .= "⏰ <b>Время на оплату:</b> {$order->time_until_expiration}\n\n";

        // Информация о клиенте
        $message .= "👤 <b>КЛИЕНТ:</b>\n";
        if ($order->customer_name) {
            $message .= "• <b>Имя:</b> {$order->customer_name}\n";
        }
        if ($order->customer_phone) {
            $message .= "• <b>Телефон:</b> {$order->customer_phone}\n";
        }
        if ($order->customer_email) {
            $message .= "• <b>Email:</b> {$order->customer_email}\n";
        }
        if ($order->customer_address) {
            $message .= "• <b>Адрес:</b> {$order->customer_address}\n";
        }
        $message .= "• <b>Telegram ID:</b> {$order->telegram_chat_id}\n\n";

        // Товары в заказе
        $message .= "🛍️ <b>ТОВАРЫ:</b>\n";
        foreach ($order->items as $item) {
            $message .= "• {$item->product_name}";
            if ($item->product_article) {
                $message .= " (арт. {$item->product_article})";
            }
            $message .= "\n  Количество: {$item->quantity} шт.\n";
            $message .= "  Цена: {$item->formatted_price_with_markup}\n";
            $message .= "  Сумма: {$item->formatted_total_price}\n\n";
        }

        if ($order->notes) {
            $message .= "💬 <b>Комментарий:</b>\n{$order->notes}\n\n";
        }

        $message .= "⏰ <b>Время заказа:</b> " . $order->created_at->format('d.m.Y H:i:s') . "\n";
        $message .= "⚠️ <b>Истекает:</b> " . $order->expires_at->format('d.m.Y H:i:s') . "\n\n";
        
        // Добавляем информацию о том, что клиент может связаться с администратором
        $bot = $order->telegramBot;
        if ($bot && $bot->admin_telegram_link) {
            $message .= "💬 <b>Клиент может написать вам:</b> " . $bot->admin_telegram_link;
        } else {
            $message .= "ℹ️ <b>Совет:</b> Настройте username администратора в настройках бота для удобной связи с клиентами";
        }

        return $message;
    }

    /**
     * Создать сообщение подтверждения для клиента
     */
    private function buildCustomerOrderMessage(\App\Models\Order $order): string
    {
        $bot = $order->telegramBot;
        
        $message = "✅ <b>Ваш заказ принят!</b>\n\n";
        $message .= "📋 <b>Номер заказа:</b> {$order->order_number}\n";
        $message .= "💰 <b>Сумма:</b> {$order->formatted_total}\n\n";

        // Товары в заказе
        $message .= "🛍️ <b>Ваши товары:</b>\n";
        foreach ($order->items as $item) {
            $message .= "• {$item->product_name} - {$item->quantity} шт.\n";
        }

        $message .= "\n⏰ <b>ВАЖНО!</b> У вас есть <b>5 часов</b> на оплату заказа.\n";
        $message .= "Заказ истекает: <b>" . $order->expires_at->format('d.m.Y в H:i') . "</b>\n\n";
        
        $message .= "📞 <b>С вами свяжутся в ближайшее время для подтверждения заказа и уточнения деталей доставки.</b>\n\n";
        
        // Добавляем ссылку на админа, если она настроена
        if ($bot && $bot->formatted_admin_link) {
            $message .= "💬 <b>Вопросы по заказу?</b>\n";
            $message .= $bot->formatted_admin_link . "\n\n";
        }
        
        $message .= "Спасибо за ваш заказ! 🙏";

        return $message;
    }

    /**
     * Ответить на callback query
     */
    public function answerCallbackQuery(TelegramBot $bot, string $callbackQueryId, string $text, bool $showAlert = false): bool
    {
        try {
            $data = [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert
            ];

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/answerCallbackQuery", $data);

            return $response->successful() && $response->json('ok');
        } catch (\Exception $e) {
            Log::error('Ошибка при ответе на callback query', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Редактировать текст сообщения
     */
    public function editMessageText(TelegramBot $bot, int $chatId, int $messageId, string $text, array $options = []): bool
    {
        try {
            $data = array_merge([
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ], $options);

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/editMessageText", $data);

            return $response->successful() && $response->json('ok');
        } catch (\Exception $e) {
            Log::error('Ошибка при редактировании сообщения', [
                'bot_id' => $bot->id,
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Удалить inline клавиатуру из сообщения
     */
    public function editMessageReplyMarkup(TelegramBot $bot, int $chatId, int $messageId): bool
    {
        try {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'reply_markup' => json_encode(['inline_keyboard' => []])
            ];

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/editMessageReplyMarkup", $data);

            return $response->successful() && $response->json('ok');
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении клавиатуры', [
                'bot_id' => $bot->id,
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}