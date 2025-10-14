<?php

require_once 'vendor/autoload.php';

// Прямой тест Telegram API с улучшенными настройками
$token = '8251179594:AAGvNDON5sPI4pSXp8IXg2o02EuX1Uii1Rc';

echo "=== Тест Telegram API с улучшенными настройками ===\n";
echo "Токен: " . substr($token, 0, 20) . "...\n";
echo "Формат токена: " . (preg_match('/^\d+:[a-zA-Z0-9_-]+$/', $token) ? 'Корректный' : 'Некорректный') . "\n\n";

// Тест 1: Прямой cURL с оптимизированными настройками
echo "--- Тест 1: Прямой cURL с оптимизированными настройками ---\n";
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
    CURLOPT_USERAGENT => 'Laravel Bot Test/1.0',
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Принудительно IPv4
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Cache-Control: no-cache'
    ]
]);

$start = microtime(true);
$response = curl_exec($ch);
$end = microtime(true);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

curl_close($ch);

echo "Время выполнения: " . round(($end - $start) * 1000) . " мс\n";
echo "Время подключения: " . round($connectTime * 1000) . " мс\n";
echo "Общее время cURL: " . round($totalTime * 1000) . " мс\n";
echo "HTTP код: $httpCode\n";

if ($curlError) {
    echo "Ошибка cURL: $curlError\n";
} else {
    echo "cURL выполнен успешно!\n";
}

if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "JSON декодирован успешно\n";
        if (isset($data['ok']) && $data['ok']) {
            echo "✅ Токен валиден!\n";
            echo "Имя бота: " . ($data['result']['first_name'] ?? 'N/A') . "\n";
            echo "Username: @" . ($data['result']['username'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Токен невалиден\n";
            echo "Ошибка: " . ($data['description'] ?? 'Неизвестная ошибка') . "\n";
        }
    } else {
        echo "❌ Ошибка декодирования JSON\n";
        echo "Сырой ответ: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "❌ Нет ответа от сервера\n";
}

// Тест 2: Через Laravel HTTP Client (если возможно)
echo "\n--- Тест 2: Laravel HTTP Client ---\n";
try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $start = microtime(true);
    $response = \Illuminate\Support\Facades\Http::timeout(60)
        ->withOptions([
            'verify' => false,
            'http_errors' => false,
            'connect_timeout' => 30,
            'timeout' => 60,
            'curl' => [
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Laravel HTTP Client Test',
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ]
        ])
        ->retry(2, 2000)
        ->get("https://api.telegram.org/bot{$token}/getMe");
    $end = microtime(true);
    
    echo "Время выполнения Laravel HTTP: " . round(($end - $start) * 1000) . " мс\n";
    echo "HTTP статус: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['ok']) && $data['ok']) {
            echo "✅ Laravel HTTP: Токен валиден!\n";
            echo "Имя бота: " . ($data['result']['first_name'] ?? 'N/A') . "\n";
            echo "Username: @" . ($data['result']['username'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Laravel HTTP: Токен невалиден\n";
        }
    } else {
        echo "❌ Laravel HTTP: Ошибка " . $response->status() . "\n";
        echo "Тело ответа: " . substr($response->body(), 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel HTTP исключение: " . $e->getMessage() . "\n";
}

// Тест 3: Через новый TelegramBotService
echo "\n--- Тест 3: Новый TelegramBotService ---\n";
try {
    $service = new \App\Services\TelegramBotService();
    
    $start = microtime(true);
    $isValid = $service->validateBotToken($token);
    $end = microtime(true);
    
    echo "Время выполнения сервиса: " . round(($end - $start) * 1000) . " мс\n";
    echo "Результат валидации: " . ($isValid ? '✅ Валиден' : '❌ Невалиден') . "\n";
    
    if ($isValid) {
        $botInfo = $service->getBotInfo($token);
        if ($botInfo) {
            echo "Имя бота: " . ($botInfo['first_name'] ?? 'N/A') . "\n";
            echo "Username: @" . ($botInfo['username'] ?? 'N/A') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка сервиса: " . $e->getMessage() . "\n";
}

echo "\n=== Тест завершен ===\n";