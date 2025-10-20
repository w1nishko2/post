<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\TelegramBot;
use App\Models\User;

echo "Testing Yandex Disk URL saving...\n";

// Найдем первого пользователя и его бота
$user = User::first();
if (!$user) {
    echo "No users found!\n";
    exit;
}

$bot = $user->telegramBots()->first();
if (!$bot) {
    echo "No bots found for user!\n";
    exit;
}

// Создаем тестовый товар
$testYandexUrl = 'https://disk.yandex.ru/d/test123456';
$testPhotosGallery = ['https://example.com/photo1.jpg', 'https://example.com/photo2.jpg'];

echo "Creating test product...\n";
echo "User ID: {$user->id}\n";
echo "Bot ID: {$bot->id}\n";
echo "Yandex URL: {$testYandexUrl}\n";
echo "Photos Gallery: " . json_encode($testPhotosGallery) . "\n";

$product = Product::create([
    'user_id' => $user->id,
    'telegram_bot_id' => $bot->id,
    'name' => 'Test Product with Yandex',
    'article' => 'TEST_YANDEX_' . time(),
    'quantity' => 10,
    'price' => 1000.00,
    'is_active' => true,
    'yandex_disk_folder_url' => $testYandexUrl,
    'photos_gallery' => $testPhotosGallery,
    'main_photo_index' => 0
]);

echo "Product created with ID: {$product->id}\n";

// Проверяем сохранение
$savedProduct = Product::find($product->id);
echo "\nChecking saved data:\n";
echo "Yandex URL saved: " . var_export($savedProduct->yandex_disk_folder_url, true) . "\n";
echo "Photos Gallery saved: " . var_export($savedProduct->photos_gallery, true) . "\n";
echo "Photos Gallery (original): " . var_export($savedProduct->getOriginal('photos_gallery'), true) . "\n";
echo "Main Photo Index: " . var_export($savedProduct->main_photo_index, true) . "\n";

// Пробуем обновить
echo "\nTesting update...\n";
$newYandexUrl = 'https://disk.yandex.ru/d/updated123456';
$newPhotosGallery = ['https://example.com/new1.jpg', 'https://example.com/new2.jpg', 'https://example.com/new3.jpg'];

$savedProduct->update([
    'yandex_disk_folder_url' => $newYandexUrl,
    'photos_gallery' => $newPhotosGallery,
    'main_photo_index' => 1
]);

$updatedProduct = Product::find($product->id);
echo "Updated Yandex URL: " . var_export($updatedProduct->yandex_disk_folder_url, true) . "\n";
echo "Updated Photos Gallery: " . var_export($updatedProduct->photos_gallery, true) . "\n";
echo "Updated Photos Gallery (original): " . var_export($updatedProduct->getOriginal('photos_gallery'), true) . "\n";
echo "Updated Main Photo Index: " . var_export($updatedProduct->main_photo_index, true) . "\n";

// Удаляем тестовый товар
$product->delete();
echo "\nTest product deleted.\n";