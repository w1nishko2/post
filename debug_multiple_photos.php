<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$product = Product::find(46);
if ($product) {
    echo "Debugging has_multiple_photos:\n";
    
    $allPhotos = $product->all_photos;
    echo "- all_photos result: " . var_export($allPhotos, true) . "\n";
    echo "- all_photos count: " . count($allPhotos) . "\n";
    echo "- count > 1: " . (count($allPhotos) > 1 ? 'true' : 'false') . "\n";
    
    // Проверим напрямую метод
    $result = $product->getHasMultiplePhotosAttribute();
    echo "- getHasMultiplePhotosAttribute() returns: " . ($result ? 'true' : 'false') . "\n";
    
    // Проверим через accessor
    $accessor = $product->getAttribute('has_multiple_photos');
    echo "- getAttribute('has_multiple_photos'): " . ($accessor ? 'true' : 'false') . "\n";
}