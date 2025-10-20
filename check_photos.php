<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "Checking products with photos_gallery field...\n";

$products = Product::whereNotNull('photos_gallery')->get();

foreach ($products as $product) {
    $originalValue = $product->getOriginal('photos_gallery');
    echo "Product ID: {$product->id}\n";
    echo "Original value: " . var_export($originalValue, true) . "\n";
    echo "Casted value: " . var_export($product->photos_gallery, true) . "\n";
    echo "Is array: " . (is_array($product->photos_gallery) ? 'yes' : 'no') . "\n";
    echo "---\n";
}