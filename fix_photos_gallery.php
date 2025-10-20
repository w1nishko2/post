<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "Fixing photos_gallery data in database...\n";

// Получаем все товары с галереей фотографий
$products = Product::whereNotNull('photos_gallery')->get();

echo "Found {$products->count()} products with photo galleries\n";

$fixed = 0;
$errors = 0;

foreach ($products as $product) {
    echo "Processing Product ID: {$product->id}... ";
    
    $rawValue = $product->getOriginal('photos_gallery');
    
    if (is_string($rawValue)) {
        // Пытаемся декодировать JSON
        $decoded = json_decode($rawValue, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Данные корректные, обновляем запись напрямую в базе
            // чтобы Laravel правильно сохранил их как JSON
            try {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'photos_gallery' => json_encode($decoded),
                        'updated_at' => now()
                    ]);
                echo "FIXED\n";
                $fixed++;
            } catch (Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
                $errors++;
            }
        } else {
            echo "SKIP (invalid JSON)\n";
        }
    } else {
        echo "SKIP (not string)\n";
    }
}

echo "\nSummary:\n";
echo "- Fixed: {$fixed} products\n";
echo "- Errors: {$errors} products\n";
echo "- Total processed: " . $products->count() . " products\n";

// Проверяем результат
echo "\nVerifying fixes...\n";
$product = Product::find(46);
if ($product) {
    echo "Test product 46:\n";
    echo "- photos_gallery type: " . gettype($product->photos_gallery) . "\n";
    echo "- is array: " . (is_array($product->photos_gallery) ? 'Yes' : 'No') . "\n";
    if (is_array($product->photos_gallery)) {
        echo "- count: " . count($product->photos_gallery) . "\n";
        echo "- first URL: " . ($product->photos_gallery[0] ?? 'N/A') . "\n";
        echo "- main_photo_url: " . ($product->main_photo_url ?? 'N/A') . "\n";
    }
}