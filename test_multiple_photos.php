<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$product = Product::find(46);
if ($product) {
    echo "Product 46 photos test:\n";
    echo "- all_photos count: " . count($product->all_photos) . "\n";
    echo "- has_multiple_photos: " . ($product->has_multiple_photos ? 'Yes' : 'No') . "\n";
    echo "- hasMultiplePhotos: " . ($product->hasMultiplePhotos ? 'Yes' : 'No') . "\n";
    
    // Проверим, что конкретно в all_photos
    echo "- all_photos content:\n";
    foreach ($product->all_photos as $index => $photo) {
        echo "  [$index]: " . (strlen($photo) > 100 ? substr($photo, 0, 100) . '...' : $photo) . "\n";
    }
}