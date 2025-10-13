<?php

require_once 'vendor/autoload.php';

echo "=== Детальное тестирование API Forum-Auto ===\n\n";

// Тестовые учетные данные
$login = '615286_pynzaru_andrey';
$password = 'ji45fDI9nCbj';

// Список эндпоинтов для тестирования
$endpoints = [
    'clientinfo' => [],
    'listbrands' => ['art' => 'test'],
    'listgoods' => ['art' => 'test', 'cross' => 1]
];

$baseUrl = 'https://api.forum-auto.ru/';

foreach ($endpoints as $endpoint => $extraParams) {
    echo "--- Тестирование эндпоинта: $endpoint ---\n";
    
    $params = array_merge([
        'login' => $login,
        'pass' => $password
    ], $extraParams);
    
    echo "Параметры: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Тестируем GET запрос
    echo "\n>> GET запрос:\n";
    testRequest($baseUrl . $endpoint, $params, 'GET');
    
    // Тестируем POST запрос (form-data)
    echo "\n>> POST запрос (form-data):\n";
    testRequest($baseUrl . $endpoint, $params, 'POST_FORM');
    
    // Тестируем POST запрос (JSON)
    echo "\n>> POST запрос (JSON):\n";
    testRequest($baseUrl . $endpoint, $params, 'POST_JSON');
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}

function testRequest($url, $params, $method) {
    $ch = curl_init();
    
    // Базовые настройки
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_USERAGENT => 'Forum-Auto-Test-Client/1.0',
        CURLOPT_VERBOSE => false
    ]);
    
    switch ($method) {
        case 'GET':
            $fullUrl = $url . '?' . http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            echo "URL: $fullUrl\n";
            break;
            
        case 'POST_FORM':
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            echo "URL: $url\n";
            echo "POST данные: " . http_build_query($params) . "\n";
            break;
            
        case 'POST_JSON':
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            echo "URL: $url\n";
            echo "JSON данные: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    echo "HTTP код: $httpCode\n";
    echo "Content-Type: $contentType\n";
    echo "Время выполнения: {$totalTime}s\n";
    
    if ($error) {
        echo "Ошибка cURL: $error\n";
        return;
    }
    
    echo "Размер ответа: " . strlen($response) . " байт\n";
    
    if (empty($response)) {
        echo "⚠️  ПУСТОЙ ОТВЕТ!\n";
        return;
    }
    
    // Показываем первые 500 символов ответа
    $preview = strlen($response) > 500 ? substr($response, 0, 500) . '...' : $response;
    echo "Ответ: $preview\n";
    
    // Пытаемся декодировать как JSON
    $json = json_decode($response, true);
    if ($json !== null) {
        echo "✅ Валидный JSON\n";
        echo "Декодированные данные: " . json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Не JSON или некорректный JSON\n";
        echo "JSON ошибка: " . json_last_error_msg() . "\n";
        
        // Проверяем, не HTML ли это
        if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {
            echo "📄 Похоже на HTML ответ\n";
        }
    }
}