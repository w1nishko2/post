<?php

namespace App\Console\Commands;

use App\Models\ImportQueue;
use App\Models\Product;
use App\Models\Category;
use App\Services\YandexDiskService;
use App\Jobs\DownloadProductImagesJob;
use App\Jobs\DownloadCategoryPhotoJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Команда для обработки очереди импорта
 * Запускается через CRON каждую минуту
 */
class ProcessImportQueue extends Command
{
    protected $signature = 'import:process-queue {--limit=50 : Количество записей за раз}';
    protected $description = 'Обработка очереди импорта товаров';

    protected YandexDiskService $yandexService;

    public function __construct(YandexDiskService $yandexService)
    {
        parent::__construct();
        $this->yandexService = $yandexService;
    }

    public function handle()
    {
        // Убираем ограничения
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '-1');

        $limit = $this->option('limit');

        // Получаем pending записи
        $items = ImportQueue::pending()
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($items->isEmpty()) {
            $this->info('✅ Очередь пуста');
            return 0;
        }

        $this->info("🔄 Обработка {$items->count()} записей...");

        $processed = 0;
        $failed = 0;

        foreach ($items as $item) {
            try {
                // Отмечаем как processing
                $item->markAsProcessing();

                // Обрабатываем товар
                $product = $this->processItem($item);

                if ($product) {
                    // Отмечаем как completed
                    $item->markAsCompleted($product->id);
                    $processed++;
                    
                    $this->line("  ✅ Товар #{$product->id}: {$product->name}");
                } else {
                    throw new \Exception('Не удалось создать товар');
                }

            } catch (\Throwable $e) {
                $failed++;
                $item->markAsFailed($e->getMessage());
                
                $this->error("  ❌ Ошибка: " . $e->getMessage());
                
                Log::error("Ошибка обработки импорта", [
                    'item_id' => $item->id,
                    'session' => $item->session_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Сбор мусора каждые 10 записей
            if ($processed % 10 === 0) {
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }

        $this->info("✅ Обработано: {$processed}, Ошибок: {$failed}");
        
        Log::info("Import queue processed", [
            'processed' => $processed,
            'failed' => $failed,
            'total' => $items->count()
        ]);

        return 0;
    }

    /**
     * Обработка одной записи из очереди
     */
    protected function processItem(ImportQueue $item): ?Product
    {
        $row = $item->row_data;

        // Парсим данные из строки (ключи - заголовки из Excel на русском)
        $name = $row['nazvanie_tovara'] ?? '';
        $description = $row['opisanie'] ?? '';
        $article = $row['artikul'] ?? '';
        $categoryName = $row['kategoriia'] ?? '';
        $categoryPhotoUrl = $row['url_foto_kategorii'] ?? '';
        $productPhotoUrl = $row['url_foto_tovara'] ?? '';
        $characteristicsRaw = $row['xarakteristiki_cerez'] ?? '';
        $quantity = $row['kolicestvo'] ?? 0;
        $price = $row['cena'] ?? 0;
        $markup = $row['nacenka'] ?? 0;
        $active = $row['aktivnost'] ?? 1;

        // Пропускаем пустые строки
        if (empty(trim($name)) && empty(trim($article))) {
            return null;
        }

        // Обрабатываем категорию
        $category_id = $this->processCategory(
            $categoryName,
            $categoryPhotoUrl,
            $item->user_id,
            $item->telegram_bot_id,
            $item->download_images
        );

        // Обрабатываем характеристики
        $specifications = [];
        if (!empty($characteristicsRaw)) {
            $specs = explode(';', $characteristicsRaw);
            foreach ($specs as $spec) {
                $spec = trim($spec);
                if (!empty($spec)) {
                    $specifications[] = $spec;
                }
            }
        }

        // Обрабатываем URL фото
        $photoData = $this->processProductPhotos($productPhotoUrl);

        // Обрабатываем числовые значения
        $quantity_clean = max(0, (int)($quantity ?? 0));
        $price_clean = max(0, (float)str_replace(',', '.', trim($price ?? 0)));
        $markup_clean = max(0, min(1000, (float)str_replace(',', '.', trim($markup ?? 0))));
        $is_active = !in_array(strtolower(trim($active ?? '1')), ['0', 'false', 'нет', 'no', '']);

        // Проверяем существующий товар
        $existingProduct = Product::where('article', $article)
            ->where('user_id', $item->user_id)
            ->where('telegram_bot_id', $item->telegram_bot_id)
            ->first();

        if ($existingProduct && !$item->update_existing) {
            Log::info("Товар пропущен (уже существует)", [
                'session' => $item->session_id,
                'article' => $article
            ]);
            return $existingProduct;
        }

        // Создаём или обновляем товар
        $productData = [
            'user_id' => $item->user_id,
            'telegram_bot_id' => $item->telegram_bot_id,
            'category_id' => $category_id,
            'name' => $name,
            'description' => !empty($description) ? $description : null,
            'article' => $article,
            'photo_url' => $photoData['main_photo'],
            'photos_gallery' => $photoData['photos_gallery'],
            'main_photo_index' => $photoData['main_photo_index'] ?? 0,
            'specifications' => !empty($specifications) ? $specifications : null,
            'quantity' => $quantity_clean,
            'price' => $price_clean,
            'markup_percentage' => $markup_clean,
            'is_active' => $is_active,
            'images_download_status' => null,
        ];

        try {
            if ($existingProduct) {
                $existingProduct->update($productData);
                $product = $existingProduct;
            } else {
                $product = Product::create($productData);
            }
        } catch (\Throwable $e) {
            // Детальное логирование ошибки создания товара
            Log::error("Ошибка создания товара в БД", [
                'session' => $item->session_id,
                'item_id' => $item->id,
                'product_data' => $productData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Ошибка создания товара: " . $e->getMessage());
        }

        // Запускаем загрузку изображений (если нужно)
        if ($item->download_images && !empty($photoData['photo_urls'])) {
            $product->update(['images_download_status' => 'pending']);
            
            DownloadProductImagesJob::dispatch(
                $product->id,
                $photoData['photo_urls'],
                $photoData['is_yandex_disk']
            )->onQueue('images');
        }

        return $product;
    }

    protected function processCategory(
        string $categoryName,
        ?string $categoryPhotoUrl,
        int $userId,
        int $botId,
        bool $downloadImages
    ): ?int {
        if (empty($categoryName)) {
            return null;
        }

        $category = Category::where('name', $categoryName)
            ->where('user_id', $userId)
            ->where('telegram_bot_id', $botId)
            ->first();

        if (!$category) {
            $category = Category::create([
                'user_id' => $userId,
                'telegram_bot_id' => $botId,
                'name' => $categoryName,
                'photo_url' => null,
                'is_active' => true,
            ]);
        }

        // Загрузка фото категории (если нужно и если ещё нет)
        if ($downloadImages && !empty($categoryPhotoUrl) && empty($category->photo_url)) {
            $isYandexDisk = $this->yandexService->isYandexDiskUrl($categoryPhotoUrl);
            
            DownloadCategoryPhotoJob::dispatch(
                $category->id,
                $categoryPhotoUrl,
                $isYandexDisk
            )->onQueue('images');
        }

        return $category->id;
    }

    protected function processProductPhotos(string $url): array
    {
        $result = [
            'main_photo' => null,
            'photos_gallery' => null,
            'main_photo_index' => 0,
            'photo_urls' => [],
            'is_yandex_disk' => false,
        ];

        if (empty($url)) {
            return $result;
        }

        $url = trim($url);

        if ($this->yandexService->isYandexDiskUrl($url)) {
            $result['photo_urls'] = [$url];
            $result['is_yandex_disk'] = true;
            return $result;
        }

        // Парсим несколько URL
        $urls = preg_split('/[;,|\n\r]+/', $url);
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls, function($u) {
            return filter_var($u, FILTER_VALIDATE_URL) !== false;
        });

        $result['photo_urls'] = array_values(array_slice($urls, 0, 5));

        return $result;
    }
}

