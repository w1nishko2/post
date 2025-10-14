<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\ForumAutoService;
use App\Models\TelegramBot;
use Illuminate\Support\Facades\Log;

// Инициализация Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Получаем первого бота для тестирования
    $bot = TelegramBot::first();
    
    if (!$bot) {
        echo "Бот не найден в базе данных\n";
        exit(1);
    }
    
    echo "Найден бот: {$bot->name} (ID: {$bot->id})\n\n";
    
    $service = new ForumAutoService($bot);
    
    // Тестируем только описательные запросы
    $searchQueries = [
        'масляный фильтр',  
        'свеча зажигания',  
        'тормозные колодки'
    ];
    
    foreach ($searchQueries as $query) {
        echo "=== Тестируем поиск: '{$query}' ===\n";
        
        // Используем рефлексию чтобы вызвать приватный метод getSuggestedArticles
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getSuggestedArticles');
        $method->setAccessible(true);
        
        $suggestedArticles = $method->invoke($service, $query);
        
        echo "Предложенные артикулы для '{$query}': " . implode(', ', $suggestedArticles) . "\n";
        
        if (!empty($suggestedArticles)) {
            echo "Тестируем поиск по первому предложенному артикулу: {$suggestedArticles[0]}\n";
            
            $directResults = $service->getGoods(['art' => $suggestedArticles[0], 'cross' => 1]);
            if (is_array($directResults)) {
                echo "Найдено товаров по артикулу {$suggestedArticles[0]}: " . count($directResults) . "\n";
                if (count($directResults) > 0) {
                    $item = $directResults[0];
                    echo "Первый товар: {$item['art']} - {$item['name']} ({$item['brand']})\n";
                }
            } else {
                echo "Ошибка при поиске по артикулу {$suggestedArticles[0]}\n";
            }
        }
        
        echo str_repeat('-', 50) . "\n\n";
    }

} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}