<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageUploadService;
use App\Services\YandexDiskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Фоновая задача для скачивания изображений товара по URL
 */
class DownloadProductImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задачи
     */
    public $tries = 3;

    /**
     * Таймаут выполнения задачи (в секундах)
     */
    public $timeout = 180; // Увеличен с 120 до 180 секунд

    /**
     * Задержка между попытками (в секундах)
     */
    public $backoff = [60, 300, 900]; // 1 мин, 5 мин, 15 мин
    
    /**
     * Максимальное количество исключений до отказа
     */
    public $maxExceptions = 3;

    protected int $productId;
    protected array $imageUrls;
    protected bool $isYandexDisk;

    /**
     * Create a new job instance.
     */
    public function __construct(int $productId, array $imageUrls, bool $isYandexDisk = false)
    {
        $this->productId = $productId;
        $this->imageUrls = $imageUrls;
        $this->isYandexDisk = $isYandexDisk;
    }

    /**
     * Execute the job.
     */
    public function handle(ImageUploadService $imageService, YandexDiskService $yandexService): void
    {
        try {
            $product = Product::find($this->productId);

            if (!$product) {
                Log::error('Товар не найден для загрузки изображений', ['product_id' => $this->productId]);
                return;
            }

            Log::info('Начало загрузки изображений для товара', [
                'product_id' => $this->productId,
                'product_name' => $product->name,
                'urls_count' => count($this->imageUrls),
                'is_yandex' => $this->isYandexDisk,
            ]);

            // Обновляем статус
            $product->update(['images_download_status' => 'processing']);
            
            // Увеличиваем лимит времени для PHP
            @set_time_limit(180);
            @ini_set('max_execution_time', '180');

            $urls = $this->imageUrls;

            // Если это Яндекс.Диск - получаем список файлов
            if ($this->isYandexDisk && count($urls) === 1) {
                try {
                    $yandexUrl = $yandexService->normalizePublicUrl($urls[0]);
                    $urls = $yandexService->getImageUrlsFromPublicFolder($yandexUrl, 5);
                    
                    Log::info('Получен список файлов из Яндекс.Диска', [
                        'product_id' => $this->productId,
                        'files_count' => count($urls),
                    ]);
                } catch (Exception $e) {
                    Log::error('Ошибка получения файлов из Яндекс.Диска', [
                        'product_id' => $this->productId,
                        'url' => $urls[0],
                        'error' => $e->getMessage(),
                    ]);
                    
                    $product->update([
                        'images_download_status' => 'failed',
                        'images_download_error' => 'Яндекс.Диск: ' . $e->getMessage(),
                    ]);
                    
                    return;
                }
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            // Проверяем, сколько изображений уже загружено
            $existingImagesCount = $product->images()->count();
            $lastOrder = $product->images()->max('order') ?? -1;

            foreach ($urls as $index => $url) {
                // Ограничиваем максимум 5 изображений
                if ($existingImagesCount + $successCount >= 5) {
                    Log::info('Достигнут лимит изображений (5)', ['product_id' => $this->productId]);
                    break;
                }

                try {
                    Log::info('Скачивание изображения', [
                        'product_id' => $this->productId,
                        'url' => $url,
                        'index' => $index + 1,
                    ]);

                    // Скачиваем и обрабатываем изображение
                    $uploadData = $imageService->downloadFromUrl($url, 'products');

                    // Создаём запись ProductImage
                    $image = $product->images()->create([
                        'file_path' => $uploadData['file_path'],
                        'thumbnail_path' => $uploadData['thumbnail_path'],
                        'original_name' => $uploadData['original_name'],
                        'file_size' => $uploadData['file_size'],
                        'is_main' => $existingImagesCount === 0 && $successCount === 0, // Первое изображение - главное
                        'order' => ++$lastOrder,
                    ]);

                    $successCount++;

                    Log::info('Изображение успешно загружено', [
                        'product_id' => $this->productId,
                        'image_id' => $image->id,
                        'file_path' => $uploadData['file_path'],
                    ]);

                    // Небольшая задержка между загрузками для снижения нагрузки
                    if ($index < count($urls) - 1) {
                        sleep(1);
                    }

                } catch (Exception $e) {
                    $errorCount++;
                    $errorMessage = $e->getMessage();
                    $errors[] = "URL {$index}: {$errorMessage}";

                    Log::warning('Ошибка загрузки изображения', [
                        'product_id' => $this->productId,
                        'url' => $url,
                        'error' => $errorMessage,
                    ]);

                    // Продолжаем со следующим изображением
                    continue;
                }
            }

            // Обновляем photos_gallery в таблице products
            $this->updateProductGallery($product);

            // Обновляем статус в зависимости от результата
            if ($successCount > 0) {
                $status = $errorCount > 0 ? 'partial' : 'completed';
                $errorText = $errorCount > 0 ? implode('; ', $errors) : null;

                $product->update([
                    'images_download_status' => $status,
                    'images_download_error' => $errorText,
                ]);

                Log::info('Загрузка изображений завершена', [
                    'product_id' => $this->productId,
                    'success' => $successCount,
                    'errors' => $errorCount,
                    'status' => $status,
                ]);
            } else {
                // Ни одно изображение не загрузилось
                $product->update([
                    'images_download_status' => 'failed',
                    'images_download_error' => implode('; ', $errors),
                ]);

                Log::error('Не удалось загрузить ни одного изображения', [
                    'product_id' => $this->productId,
                    'errors' => $errors,
                ]);
            }

        } catch (Exception $e) {
            Log::error('Критическая ошибка в Job загрузки изображений', [
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Обновляем статус товара
            if ($product = Product::find($this->productId)) {
                $product->update([
                    'images_download_status' => 'failed',
                    'images_download_error' => $e->getMessage(),
                ]);
            }

            // Пробрасываем исключение для retry механизма
            throw $e;
        }
    }

    /**
     * Обновить поле photos_gallery в таблице products
     */
    private function updateProductGallery(Product $product): void
    {
        try {
            $images = $product->images()->ordered()->get();

            $photosGallery = $images->map(function($image) {
                return $image->url;
            })->values()->toArray();

            $mainPhotoIndex = 0;
            foreach ($images as $index => $image) {
                if ($image->is_main) {
                    $mainPhotoIndex = $index;
                    break;
                }
            }

            $product->update([
                'photos_gallery' => $photosGallery,
                'main_photo_index' => $mainPhotoIndex,
                'photo_url' => $photosGallery[$mainPhotoIndex] ?? ($photosGallery[0] ?? null)
            ]);

            Log::info('Photos gallery обновлена', [
                'product_id' => $product->id,
                'photos_count' => count($photosGallery),
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка обновления photos_gallery', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Обработка неудачи задачи после всех попыток
     */
    public function failed(Exception $exception): void
    {
        Log::error('Job загрузки изображений провалился после всех попыток', [
            'product_id' => $this->productId,
            'error' => $exception->getMessage(),
        ]);

        // Обновляем статус товара
        if ($product = Product::find($this->productId)) {
            $product->update([
                'images_download_status' => 'failed',
                'images_download_error' => 'Превышено количество попыток: ' . $exception->getMessage(),
            ]);
        }
    }
}
