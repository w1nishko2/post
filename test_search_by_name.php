<?php

require_once 'vendor/autoload.php';

use App\Models\TelegramBot;
use App\Services\ForumAutoService;

// Загружаем Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Получаем первого бота с настроенным Forum-Auto
$bot = TelegramBot::where('forum_auto_enabled', true)
    ->whereNotNull('forum_auto_login')
    ->whereNotNull('forum_auto_pass')
    ->first();

if (!$bot) {
    echo "Нет ботов с настроенным Forum-Auto API\n";
    exit(1);
}

echo "Тестируем поиск по названию товара для бота ID: {$bot->id}\n\n";

try {
    $service = new ForumAutoService($bot);
    
    // Тесты поиска по разным названиям
    $searchTests = [
        'фильтр масляный',
        'колодки тормозные',
        'свеча зажигания', 
        'масло моторное',
        'амортизатор',
        'лампа H7',
    ];
    
    foreach ($searchTests as $searchTerm) {
        echo "=== Поиск: '$searchTerm' ===\n";
        
        $results = $service->advancedSearchGoods($searchTerm, 1, 5);
        
        if (empty($results)) {
            echo "❌ Ничего не найдено\n\n";
        } else {
            echo "✅ Найдено товаров: " . count($results) . "\n";
            foreach ($results as $i => $item) {
                $percent = $item['match_percent'] ?? 0;
                echo sprintf(
                    "%d. %s %s - %s (совпадение: %d%%)\n", 
                    $i + 1,
                    $item['brand'] ?? 'N/A', 
                    $item['art'] ?? 'N/A', 
                    $item['name'] ?? 'N/A',
                    $percent
                );
            }
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

echo "Тестирование завершено.\n";