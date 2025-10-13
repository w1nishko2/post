<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Прямой тест API Forum-Auto
$url = 'https://api.forum-auto.ru/clientinfo';

// Замените на реальные учетные данные
$params = [
    'login' => 'test_login', // Замените на реальный логин
    'pass' => 'test_password' // Замените на реальный пароль
];

echo "Тестируем API Forum-Auto...\n";
echo "URL: $url\n";
echo "Параметры: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n\n";

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
    echo "Длина ответа: " . strlen($response) . " байт\n";
    
    if ($response) {
        $json = json_decode($response, true);
        if ($json) {
            echo "Декодированный JSON: " . json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Не удалось декодировать JSON. Возможно, ответ не в формате JSON.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Исключение: " . $e->getMessage() . "\n";
}