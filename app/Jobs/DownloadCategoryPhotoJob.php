<?php

namespace App\Jobs;

use App\Models\Category;
use App\Services\ImageUploadService;
use App\Services\YandexDiskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class DownloadCategoryPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [60, 300, 900]; // 1 минута, 5 минут, 15 минут

    protected int $categoryId;
    protected string $photoUrl;
    protected bool $isYandexDisk;

    /**
     * Create a new job instance.
     */
    public function __construct(int $categoryId, string $photoUrl, bool $isYandexDisk = false)
    {
        $this->categoryId = $categoryId;
        $this->photoUrl = $photoUrl;
        $this->isYandexDisk = $isYandexDisk;
    }

    /**
     * Execute the job.
     */
    public function handle(ImageUploadService $imageService, YandexDiskService $yandexService): void
    {
        $category = Category::find($this->categoryId);

        if (!$category) {
            Log::warning('Категория не найдена для загрузки фото', [
                'category_id' => $this->categoryId,
            ]);
            return;
        }

        Log::info('Начало загрузки фото для категории', [
            'category_id' => $this->categoryId,
            'category_name' => $category->name,
            'url' => $this->photoUrl,
            'is_yandex' => $this->isYandexDisk,
        ]);

        try {
            $imageUrls = [];

            // Если это Яндекс.Диск - получаем первую картинку из папки
            if ($this->isYandexDisk) {
                Log::info('Получение первой фотографии из Яндекс.Диска', [
                    'category_id' => $this->categoryId,
                    'url' => $this->photoUrl,
                ]);

                $allImageUrls = $yandexService->getImageUrlsFromPublicFolder($this->photoUrl);

                if (empty($allImageUrls)) {
                    throw new Exception("Не найдено изображений в папке Яндекс.Диска");
                }

                // Берём только первое изображение
                $imageUrls = [array_shift($allImageUrls)];

                Log::info('Первое изображение получено из Яндекс.Диска', [
                    'category_id' => $this->categoryId,
                    'url' => $imageUrls[0],
                ]);
            } else {
                // Прямая ссылка на изображение
                $imageUrls = [$this->photoUrl];
            }

            // Скачиваем изображение
            $downloadUrl = $imageUrls[0];

            Log::info('Скачивание фотографии категории', [
                'category_id' => $this->categoryId,
                'url' => $downloadUrl,
            ]);

            $uploadResult = $imageService->downloadFromUrl($downloadUrl, 'categories');

            // Обновляем категорию - сохраняем только путь к файлу
            // Путь уже содержит полный путь относительно storage/app/public
            $category->update([
                'photo_url' => $uploadResult['file_path'],
            ]);

            Log::info('Фото категории успешно загружено', [
                'category_id' => $this->categoryId,
                'category_name' => $category->name,
                'file_path' => $uploadResult['file_path'],
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка загрузки фото категории', [
                'category_id' => $this->categoryId,
                'category_name' => $category->name,
                'url' => $this->photoUrl,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Позволяем Laravel повторить попытку
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Job загрузки фото категории окончательно провалился', [
            'category_id' => $this->categoryId,
            'url' => $this->photoUrl,
            'error' => $exception->getMessage(),
        ]);
    }
}
