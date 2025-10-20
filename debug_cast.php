<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "Testing photos_gallery cast issue...\n";

$product = Product::find(46);
if (!$product) {
    echo "Product 46 not found\n";
    exit;
}

echo "Product ID: {$product->id}\n";
echo "Raw photos_gallery: " . var_export($product->getOriginal('photos_gallery'), true) . "\n";
echo "Casted photos_gallery: " . var_export($product->photos_gallery, true) . "\n";
echo "Type of casted: " . gettype($product->photos_gallery) . "\n";

// Попробуем декодировать
if (is_string($product->photos_gallery)) {
    $decoded = json_decode($product->photos_gallery, true);
    echo "Manually decoded: " . var_export($decoded, true) . "\n";
    echo "JSON error: " . json_last_error_msg() . "\n";
}

// Проверим, как работает cast Laravel
echo "\nTesting cast behavior:\n";
$rawValue = $product->getOriginal('photos_gallery');
echo "Raw value type: " . gettype($rawValue) . "\n";

if (is_string($rawValue)) {
    $decoded = json_decode($rawValue, true);
    echo "Raw decoded: " . var_export($decoded, true) . "\n";
    if (is_array($decoded)) {
        echo "First URL: " . ($decoded[0] ?? 'N/A') . "\n";
        echo "URL contains downloader: " . (isset($decoded[0]) && str_contains($decoded[0], 'downloader.disk.yandex.ru') ? 'Yes' : 'No') . "\n";
    }
}