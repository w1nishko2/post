<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\TelegramBot;

echo "Testing photo display for products with Yandex Disk...\n";

// Найдем товары с галереей фотографий
$products = Product::whereNotNull('photos_gallery')->take(5)->get();

if ($products->isEmpty()) {
    echo "No products with photo galleries found.\n";
    exit;
}

foreach ($products as $product) {
    echo "\n=== Product ID: {$product->id} ===\n";
    echo "Name: {$product->name}\n";
    echo "Yandex Disk URL: " . ($product->yandex_disk_folder_url ?: 'N/A') . "\n";
    echo "Photos Gallery (raw): " . json_encode($product->getOriginal('photos_gallery')) . "\n";
    echo "Photos Gallery (casted): " . json_encode($product->photos_gallery) . "\n";
    echo "Main Photo URL: " . ($product->main_photo_url ?: 'N/A') . "\n";
    echo "All Photos: " . json_encode($product->all_photos) . "\n";
    echo "Has Multiple Photos: " . ($product->has_multiple_photos ? 'Yes' : 'No') . "\n";
    echo "Main Photo Index: " . $product->main_photo_index . "\n";
}

// Также проверим конкретный продукт через API
echo "\n=== Testing Mini App API ===\n";
$bot = TelegramBot::where('mini_app_short_name', 'daniil')->first();
if ($bot) {
    echo "Bot found: {$bot->name} (Short name: {$bot->mini_app_short_name})\n";
    
    $apiProducts = $bot->activeProducts()
               ->with('category')
               ->orderedForListing()
               ->limit(3)
               ->get()
               ->map(function ($product) {
                   return [
                       'id' => $product->id,
                       'name' => $product->name,
                       'photo_url' => $product->main_photo_url,
                       'photos_gallery' => $product->all_photos,
                       'has_multiple_photos' => $product->has_multiple_photos,
                   ];
               });
    
    echo "API Products (first 3):\n";
    foreach ($apiProducts as $apiProduct) {
        echo "- ID: {$apiProduct['id']}, Name: {$apiProduct['name']}\n";
        echo "  Main Photo: " . ($apiProduct['photo_url'] ?: 'N/A') . "\n";
        echo "  Gallery: " . json_encode($apiProduct['photos_gallery']) . "\n";
        echo "  Multiple Photos: " . ($apiProduct['has_multiple_photos'] ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "Bot with short name 'daniil' not found.\n";
}