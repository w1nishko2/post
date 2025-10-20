<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "Testing Yandex Disk proxy functionality...\n";

// Найдем товары с фотографиями из Яндекс.Диска
$products = Product::whereNotNull('photos_gallery')->take(3)->get();

foreach ($products as $product) {
    echo "\n=== Product ID: {$product->id} ===\n";
    echo "Name: {$product->name}\n";
    
    echo "Raw photos_gallery:\n";
    foreach ($product->photos_gallery as $index => $photo) {
        echo "  [{$index}]: " . (strlen($photo) > 100 ? substr($photo, 0, 100) . '...' : $photo) . "\n";
    }
    
    echo "Processed main_photo_url: " . ($product->main_photo_url ?: 'N/A') . "\n";
    
    echo "Processed all_photos:\n";
    foreach ($product->all_photos as $index => $photo) {
        echo "  [{$index}]: " . (strlen($photo) > 100 ? substr($photo, 0, 100) . '...' : $photo) . "\n";
    }
}

echo "\nTesting proxy URL generation...\n";

$testUrl = 'https://downloader.disk.yandex.ru/disk/test123/test.jpg';
$expectedProxy = url('/api/yandex-image-proxy?url=' . urlencode($testUrl));
echo "Test URL: {$testUrl}\n";
echo "Expected proxy URL: {$expectedProxy}\n";

echo "\nDone!\n";