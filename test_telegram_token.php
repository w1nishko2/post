<?php

require_once 'vendor/autoload.php';

// Тест токена напрямую через Telegram API
$token = '8251179594:AAGvNDON5sPI4pSXp8IXg2o02EuX1Uii1Rc';

echo "Тестируем токен бота: " . substr($token, 0, 20) . "...\n";
echo "Формат токена: " . (preg_match('/^\d+:[a-zA-Z0-9_-]+$/', $token) ? 'Корректный' : 'Некорректный') . "\n\n";

// Прямая проверка через cURL
$url = "https://api.telegram.org/bot{$token}/getMe";

echo "URL запроса: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP код: $httpCode\n";
if ($error) {
    echo "Ошибка cURL: $error\n";
}
echo "Ответ: $response\n";

if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "\nДекодированный ответ:\n";
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        if (isset($data['ok']) && $data['ok']) {
            echo "\n✅ Токен ВАЛИДЕН!\n";
            if (isset($data['result']['username'])) {
                echo "Username бота: @" . $data['result']['username'] . "\n";
            }
        } else {
            echo "\n❌ Токен НЕ ВАЛИДЕН!\n";
            if (isset($data['description'])) {
                echo "Описание ошибки: " . $data['description'] . "\n";
            }
        }
    }
}

// Теперь проверим через Laravel HTTP Client
echo "\n--- Проверка через Laravel HTTP Client ---\n";

try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $response = \Illuminate\Support\Facades\Http::timeout(15)->get("https://api.telegram.org/bot{$token}/getMe");
    
    echo "HTTP статус: " . $response->status() . "\n";
    echo "Успешный: " . ($response->successful() ? 'Да' : 'Нет') . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "Ответ через Laravel HTTP:\n";
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Ошибка через Laravel HTTP:\n";
        echo $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "Исключение при проверке через Laravel: " . $e->getMessage() . "\n";
}