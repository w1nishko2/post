<?php

// Простой тест API Forum-Auto
require_once 'vendor/autoload.php';

use App\Models\TelegramBot;
use App\Services\ForumAutoService;

// Подключение к Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Найдем первого активного бота
$bot = TelegramBot::where('is_active', true)->first();

if (!$bot) {
    echo "Активный бот не найден\n";
    exit(1);
}

echo "Найден бот: {$bot->bot_name} (ID: {$bot->id})\n";
echo "Forum-Auto API настроен: " . ($bot->hasForumAutoApi() ? 'Да' : 'Нет') . "\n";

if ($bot->hasForumAutoApi()) {
    $service = new ForumAutoService($bot);
    
    echo "Тестируем подключение к API...\n";
    $isValid = $service->validateCredentials();
    echo "Валидность учетных данных: " . ($isValid ? 'Да' : 'Нет') . "\n";
    
    if ($isValid) {
        echo "\nТестируем получение популярных товаров...\n";
        $popularGoods = $service->getPopularGoods();
        echo "Получено популярных товаров: " . count($popularGoods) . "\n";
        
        echo "\nТестируем получение случайных товаров...\n";
        $randomGoods = $service->getRandomGoods(5);
        echo "Получено случайных товаров: " . count($randomGoods) . "\n";
        
        if (!empty($randomGoods)) {
            echo "\nПример товара:\n";
            $item = $randomGoods[0];
            echo "- Бренд: {$item['brand']}\n";
            echo "- Артикул: {$item['art']}\n";
            echo "- Название: {$item['name']}\n";
            echo "- Цена: {$item['price']} ₽\n";
            echo "- В наличии: {$item['num']} шт.\n";
        }
    }
} else {
    echo "\nAPI не настроен, тестируем демо-товары...\n";
    $service = new ForumAutoService($bot);
    
    // Получим демо-товары через метод (если он доступен)
    try {
        $popularGoods = $service->getPopularGoods();
        echo "Получено демо-товаров: " . count($popularGoods) . "\n";
        
        if (!empty($popularGoods)) {
            echo "\nПример демо-товара:\n";
            $item = $popularGoods[0];
            echo "- Бренд: {$item['brand']}\n";
            echo "- Артикул: {$item['art']}\n";
            echo "- Название: {$item['name']}\n";
            echo "- Цена: {$item['price']} ₽\n";
            echo "- В наличии: {$item['num']} шт.\n";
        }
    } catch (Exception $e) {
        echo "Ошибка при получении демо-товаров: " . $e->getMessage() . "\n";
    }
}

echo "\nТест завершен.\n";