<?php

require_once 'vendor/autoload.php';

use App\Models\TelegramBot;
use App\Services\TelegramBotService;

// Подключение к Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Проверка настроек Mini App ===\n\n";

// Найдем всех активных ботов с Mini App
$bots = TelegramBot::where('is_active', true)
                  ->whereNotNull('mini_app_short_name')
                  ->get();

if ($bots->isEmpty()) {
    echo "Активные боты с Mini App не найдены.\n";
    echo "Проверим всех ботов...\n\n";
    
    $allBots = TelegramBot::all();
    foreach ($allBots as $bot) {
        echo "Бот ID: {$bot->id}\n";
        echo "Имя: {$bot->bot_name}\n";
        echo "Активен: " . ($bot->is_active ? 'Да' : 'Нет') . "\n";
        echo "Mini App URL: " . ($bot->mini_app_url ?? 'Не настроен') . "\n";
        echo "Mini App Short Name: " . ($bot->mini_app_short_name ?? 'Не настроен') . "\n";
        echo "Имеет Mini App: " . ($bot->hasMiniApp() ? 'Да' : 'Нет') . "\n";
        echo "---\n";
    }
    exit(1);
}

$telegramService = new TelegramBotService();

foreach ($bots as $bot) {
    echo "Проверяем бота: {$bot->bot_name} (ID: {$bot->id})\n";
    echo "Short Name: {$bot->mini_app_short_name}\n";
    echo "URL: {$bot->mini_app_url}\n";
    echo "Display URL: {$bot->getDisplayMiniAppUrl()}\n";
    
    // Проверяем токен бота
    echo "Проверка токена бота...\n";
    $tokenValid = $telegramService->validateBotToken($bot->bot_token);
    echo "Токен валиден: " . ($tokenValid ? 'Да' : 'Нет') . "\n";
    
    if ($tokenValid) {
        // Получаем информацию о боте
        $botInfo = $telegramService->getBotInfo($bot->bot_token);
        if ($botInfo) {
            echo "Имя бота в Telegram: {$botInfo['first_name']}\n";
            echo "Username бота: @{$botInfo['username']}\n";
        }
        
        // Проверяем валидацию Mini App
        $errors = $telegramService->validateMiniApp($bot);
        if (empty($errors)) {
            echo "✅ Mini App корректно настроен\n";
            
            // Проверяем webhook info
            $webhookInfo = $telegramService->getWebhookInfo($bot);
            if ($webhookInfo) {
                echo "Webhook URL: " . ($webhookInfo['url'] ?? 'Не настроен') . "\n";
                echo "Webhook активен: " . ($webhookInfo['has_custom_certificate'] ? 'Да' : 'Нет') . "\n";
            }
            
        } else {
            echo "❌ Ошибки в настройке Mini App:\n";
            foreach ($errors as $error) {
                echo "  - $error\n";
            }
        }
        
        // Тестируем URL Mini App
        echo "Проверка доступности Mini App URL...\n";
        $url = $bot->getDisplayMiniAppUrl();
        
        if ($url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "HTTP код ответа: $httpCode\n";
            if ($httpCode == 200) {
                echo "✅ Mini App доступен\n";
            } else {
                echo "❌ Mini App недоступен\n";
            }
        }
        
    } else {
        echo "❌ Невозможно проверить Mini App - токен бота недействителен\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "Тест завершен.\n";