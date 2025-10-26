<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class FixPhotosGalleryData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:photos-gallery';

    /**
     * The console command description.
     */
    protected $description = 'Проверка и исправление данных photos_gallery в продуктах';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Проверка и исправление данных photos_gallery...');
        $this->newLine();

        // Получаем все продукты с фотографиями
        $products = Product::whereNotNull('photos_gallery')->get();

        $this->info("Найдено продуктов с фотографиями: " . $products->count());
        $this->newLine();

        $fixedCount = 0;
        $alreadyValidCount = 0;
        $problemProducts = [];

        foreach ($products as $product) {
            $this->line("Проверяем продукт ID: {$product->id}, Название: {$product->name}");
            
            $photosGallery = $product->photos_gallery;
            $needsFix = false;
            
            if (is_array($photosGallery) && !empty($photosGallery)) {
                foreach ($photosGallery as $index => $photo) {
                    if (is_array($photo)) {
                        // Проверяем, есть ли нужные ключи
                        if (!isset($photo['url']) && !isset($photo['display_url']) && !isset($photo['preview'])) {
                            $this->warn("  - Фото $index: массив без URL ключей");
                            $needsFix = true;
                        } else {
                            $this->line("  - Фото $index: корректный массив");
                        }
                    } elseif (is_string($photo)) {
                        $this->line("  - Фото $index: строка URL (нормально)");
                    } else {
                        $this->error("  - Фото $index: неизвестный тип данных - " . gettype($photo));
                        $needsFix = true;
                    }
                }
                
                if ($needsFix) {
                    $problemProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'photos_gallery' => $photosGallery
                    ];
                    $fixedCount++;
                } else {
                    $alreadyValidCount++;
                }
            } else {
                $this->warn("  - Пустая галерея или некорректный формат");
            }
            
            $this->newLine();
        }

        $this->newLine();
        $this->info('Результаты проверки:');
        $this->line("- Продуктов с корректными данными: $alreadyValidCount");
        $this->line("- Продуктов, требующих исправления: $fixedCount");
        $this->newLine();

        if (!empty($problemProducts)) {
            $this->error('Проблемные продукты:');
            foreach ($problemProducts as $product) {
                $this->line("ID: {$product['id']}, Название: {$product['name']}");
                $this->line("Данные фотогалереи:");
                $this->line(json_encode($product['photos_gallery'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->newLine();
            }
        }

        $this->info('Проверка завершена.');
        
        return 0;
    }
}