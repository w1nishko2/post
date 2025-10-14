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

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$bot->bot_token}/sendMessage", $data);

            return $response->successful() && $response->json('ok');
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
}