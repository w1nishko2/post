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
    
    echo "Найден бот: {$bot->name} (ID: {$bot->id})\n";
    echo "API URL: {$bot->forum_auto_api_url}\n";
    echo "API Key: " . substr($bot->forum_auto_api_key, 0, 10) . "...\n\n";
    
    $service = new ForumAutoService($bot);
    
    // Тестовые поисковые запросы
    $searchQueries = [
        'OC90',        // Прямой артикул
        'масляный фильтр',  // Описательный запрос
        'W933',        // Популярный артикул
        'свеча зажигания',  // Популярный товар
        'BOSCH',       // Бренд
        '1234567890',  // Числовой запрос
    ];
    
    foreach ($searchQueries as $query) {
        echo "=== Поиск: '{$query}' ===\n";
        
        $startTime = microtime(true);
        $results = $service->advancedSearchGoods($query, 1, 5);
        $endTime = microtime(true);
        
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        echo "Найдено товаров: " . count($results) . " (за {$duration}мс)\n";
        
        if (count($results) > 0) {
            foreach ($results as $index => $item) {
                echo ($index + 1) . ". {$item['art']} - {$item['name']} ({$item['brand']})\n";
                echo "   Совпадение: {$item['match_percent']}% по полю '{$item['matching_field']}'\n";
                if (isset($item['search_strategy'])) {
                    echo "   Стратегия: {$item['search_strategy']}\n";
                }
                echo "   Наличие: {$item['num']} шт.\n";
                echo "\n";
            }
        } else {
            echo "Товары не найдены.\n";
        }
        
        echo str_repeat('-', 50) . "\n\n";
    }

} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}