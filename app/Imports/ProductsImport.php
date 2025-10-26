<?php

namespace App\Imports;

use App\Models\Product;
use App\Services\YandexDiskService;
use App\Jobs\DownloadProductImagesJob;
use App\Jobs\DownloadCategoryPhotoJob;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;

class ProductsImport implements ToModel, WithStartRow
{
    use Importable;

    private $importErrors = [];
    private $importedCount = 0;
    private $skippedCount = 0;
    private $updatedCount = 0;
    private $telegramBotId;
    private $updateExisting;
    private $downloadImages; // Новый флаг
    private $downloadMode; // 'sync' или 'background'
    private $processedCategories = []; // Кеш обработанных категорий
    private $currentRow = 0;
    private $totalRows = 0;
    private YandexDiskService $yandexService;

    public function __construct($telegramBotId = null, $updateExisting = false, $downloadImages = false, $downloadMode = 'background')
    {
        $this->telegramBotId = $telegramBotId;
        $this->updateExisting = $updateExisting;
        $this->downloadImages = $downloadImages;
        $this->downloadMode = $downloadMode;
        $this->yandexService = app(YandexDiskService::class);
        
        // Полностью убираем ограничения времени и памяти
        @set_time_limit(0); // Без ограничений
        @ini_set('max_execution_time', '0'); // Без ограничений
        @ini_set('memory_limit', '-1'); // Без ограничений памяти
        @ini_set('max_input_time', '0'); // Без ограничений на input
        
        // Отключаем вывод ошибок в браузер (только в лог)
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        
        // Увеличиваем лимиты для POST и загрузки файлов
        @ini_set('post_max_size', '256M');
        @ini_set('upload_max_filesize', '256M');
    }

    /**
     * Указываем, что данные начинаются со 2-й строки (пропускаем заголовки)
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Создание модели из строки Excel
     */
    public function model(array $row)
    {
        // Сбрасываем таймер выполнения на каждой строке
        @set_time_limit(0);
        
        $this->currentRow++;
        
        // Логируем прогресс каждые 10 строк
        if ($this->currentRow % 10 == 0) {
            Log::info("Import progress: {$this->currentRow} rows processed", [
                'imported' => $this->importedCount,
                'updated' => $this->updatedCount,
                'skipped' => $this->skippedCount,
                'errors' => count($this->importErrors)
            ]);
            
            // Принудительно очищаем память каждые 10 строк
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
        
        // Добавляем отладочную информацию
        Log::info('Row data:', $row);
        
        // Если это первая строка, выводим все ключи для отладки
        static $headerLogged = false;
        if (!$headerLogged) {
            Log::info('Available headers/keys:', array_keys($row));
            $headerLogged = true;
        }
        
        // Работаем с индексами столбцов согласно обновленному шаблону:
        // 0: Название товара
        // 1: Описание
        // 2: Артикул  
        // 3: Категория
        // 4: URL фото категории
        // 5: URL фото товара
        // 6: Характеристики (через ;)
        // 7: Количество
        // 8: Цена
        // 9: Наценка (%)
        // 10: Активный (1/0)
        
        $name = isset($row[0]) ? trim($row[0]) : '';
        $description = isset($row[1]) ? trim($row[1]) : '';
        $article = isset($row[2]) ? trim($row[2]) : '';
        $categoryName = isset($row[3]) ? trim($row[3]) : '';
        $categoryPhotoUrl = isset($row[4]) ? trim($row[4]) : '';
        $productPhotoUrl = isset($row[5]) ? trim($row[5]) : '';
        $characteristicsRaw = isset($row[6]) ? trim($row[6]) : '';
        $quantity = isset($row[7]) ? $row[7] : 0;
        $price = isset($row[8]) ? $row[8] : 0;
        $markup = isset($row[9]) ? $row[9] : 0;
        $active = isset($row[10]) ? $row[10] : 1;

        // Пропускаем полностью пустые строки
        if (empty(array_filter($row, function($value) {
            return !empty(trim($value));
        }))) {
            return null;
        }

        // Пропускаем строки, где нет названия товара и артикула
        if (empty($name) && empty($article)) {
            return null;
        }
        
        // Проверяем обязательные поля для валидации
        if (empty($name) || empty($article)) {
            $this->skippedCount++;
            Log::warning('Skipping row due to missing required fields', [
                'name' => $name,
                'article' => $article,
                'row' => $row
            ]);
            return null;
        }

        // Обрабатываем и очищаем данные
        $description = !empty($description) ? $description : null;

        // Проверяем существующий товар
        $existingProduct = Product::where('article', $article)
            ->where('user_id', Auth::id())
            ->where('telegram_bot_id', $this->telegramBotId)
            ->first();
            
        if ($existingProduct) {
            if ($this->updateExisting) {
                // Если разрешено обновление - обновляем существующий товар
                return $this->updateExistingProduct($existingProduct, $name, $description, $article, $categoryName, $categoryPhotoUrl, $productPhotoUrl, $characteristicsRaw, $quantity, $price, $markup, $active);
            } else {
                $this->importErrors[] = "Строка: Товар с артикулом '$article' уже существует (включите 'Обновлять существующие товары' для обновления)";
                $this->skippedCount++;
                return null;
            }
        }

        // Проверяем длину полей
        if (strlen($name) > 255) {
            $this->importErrors[] = "Строка: Название товара не должно превышать 255 символов";
            $this->skippedCount++;
            return null;
        }

        if ($description && strlen($description) > 2000) {
            $this->importErrors[] = "Строка: Описание не должно превышать 2000 символов";
            $this->skippedCount++;
            return null;
        }

        if (strlen($article) > 100) {
            $this->importErrors[] = "Строка: Артикул не должен превышать 100 символов";
            $this->skippedCount++;
            return null;
        }
        
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
        
        // Обрабатываем категорию - ищем или создаем с поддержкой загрузки фото
        $category_id = $this->processCategoryWithPhoto($categoryName, $categoryPhotoUrl);

        // Обрабатываем URL фото товара - используем улучшенную функцию с определением типа ссылки
        $photoData = $this->processProductPhotos($productPhotoUrl);
        
        // Обрабатываем количество - приводим к числу, по умолчанию 0
        $quantity_clean = 0;
        if (isset($quantity) && is_numeric($quantity)) {
            $quantity_clean = max(0, (int) $quantity);
        }
        
        // Обрабатываем цену - приводим к числу, по умолчанию 0
        // Заменяем запятые на точки для корректного парсинга
        $price_clean = 0;
        if (isset($price)) {
            $price_str = str_replace(',', '.', trim($price));
            if (is_numeric($price_str)) {
                $price_clean = max(0, (float) $price_str);
            }
        }
        
        // Обрабатываем наценку - приводим к числу, по умолчанию 0
        // Заменяем запятые на точки для корректного парсинга
        // Ограничиваем от 0 до 1000%
        $markup_clean = 0;
        if (isset($markup)) {
            $markup_str = str_replace(',', '.', trim($markup));
            if (is_numeric($markup_str)) {
                $markup_clean = max(0, min(1000, (float) $markup_str));
            }
        }
        
        // Обрабатываем активность - более гибкая проверка
        $is_active = true; // по умолчанию активен
        if (isset($active)) {
            if (is_numeric($active)) {
                $is_active = (int) $active > 0;
            } elseif (is_string($active)) {
                $active_value = strtolower(trim($active));
                $is_active = !in_array($active_value, ['0', 'false', 'нет', 'no', '']);
            } else {
                $is_active = (bool) $active;
            }
        }

        $this->importedCount++;

        try {
            // Создаём товар
            $product = Product::create([
                'user_id' => Auth::id(),
                'telegram_bot_id' => $this->telegramBotId,
                'category_id' => $category_id,
                'name' => $name,
                'description' => $description,
                'article' => $article,
                'photo_url' => $photoData['main_photo'],
                'photos_gallery' => $photoData['photos_gallery'],
                'main_photo_index' => $photoData['main_photo_index'] ?? 0,
                'specifications' => !empty($specifications) ? $specifications : null,
                'quantity' => $quantity_clean,
                'price' => $price_clean,
                'markup_percentage' => $markup_clean,
                'is_active' => $is_active,
                'images_download_status' => null, // Пока null
            ]);

            // Если включена загрузка изображений и есть URL
            if ($this->downloadImages && !empty($photoData['photo_urls'])) {
                // Устанавливаем статус
                $product->update(['images_download_status' => 'pending']);
                
                // Добавляем Job для загрузки (с увеличенной задержкой для снижения нагрузки)
                // Увеличена задержка с 2 до 5 секунд между товарами
                $delay = now()->addSeconds($this->importedCount * 5); 
                
                DownloadProductImagesJob::dispatch(
                    $product->id,
                    $photoData['photo_urls'],
                    $photoData['is_yandex_disk']
                )->delay($delay);

                Log::info('Job загрузки изображений добавлен в очередь', [
                    'product_id' => $product->id,
                    'product_name' => $name,
                    'urls_count' => count($photoData['photo_urls']),
                    'is_yandex' => $photoData['is_yandex_disk'],
                    'delay_seconds' => $this->importedCount * 5,
                ]);
            }

            return null; // Возвращаем null т.к. товар уже создан
            
        } catch (\Exception $e) {
            $this->importErrors[] = "Строка: Ошибка создания товара - " . $e->getMessage();
            $this->skippedCount++;
            $this->importedCount--;
            Log::error('Error creating product', [
                'error' => $e->getMessage(),
                'data' => [
                    'name' => $name,
                    'article' => $article,
                    'price' => $price_clean,
                    'markup_percentage' => $markup_clean,
                    'quantity' => $quantity_clean
                ]
            ]);
            return null;
        }
    }

    /**
     * Получить ошибки импорта
     */
    public function getImportErrors(): array
    {
        return $this->importErrors;
    }

    /**
     * Получить количество импортированных записей
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Получить количество пропущенных записей
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Есть ли ошибки импорта
     */
    public function hasErrors(): bool
    {
        return !empty($this->importErrors);
    }

    /**
     * Обработка URL изображения (только прямые ссылки)
     */
    private function processImageUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        
        // Проверяем, что это валидная прямая ссылка на изображение
        if (filter_var($url, FILTER_VALIDATE_URL) && 
            (preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg|tiff|tif|heic|heif|avif|ico|raw|dng|cr2|nef|arw|psd|ai|eps)(\?.*)?$/i', $url) ||
             str_contains($url, 'image') || str_contains($url, 'photo') || str_contains($url, 'picture'))) {
            return $url;
        }

        return null;
    }



    /**
     * Обработка фотографий товара (прямые ссылки, несколько URL, Яндекс.Диск)
     */
    private function processProductPhotos($url): array
    {
        $result = [
            'main_photo' => null,
            'photos_gallery' => null,
            'main_photo_index' => 0,
            'photo_urls' => [], // Для фоновой загрузки
            'is_yandex_disk' => false,
        ];

        if (empty($url)) {
            return $result;
        }

        $url = trim($url);

        // Проверяем, является ли это ссылкой на Яндекс.Диск
        if ($this->yandexService->isYandexDiskUrl($url)) {
            Log::info('Обнаружена ссылка на Яндекс.Диск', ['url' => $url]);
            
            $result['photo_urls'] = [$url]; // Сохраняем ссылку на папку для Job
            $result['is_yandex_disk'] = true;
            
            return $result;
        }

        // Парсим несколько URL через разделители
        $urls = $this->parsePhotoUrls($url);

        if (!empty($urls)) {
            Log::info('Найдено URL изображений', [
                'count' => count($urls),
                'urls' => $urls
            ]);

            $result['photo_urls'] = $urls;
            
            // Если не включена загрузка - сохраняем первую ссылку как главное фото (старое поведение)
            if (!$this->downloadImages) {
                $result['main_photo'] = $urls[0];
            }
        }

        return $result;
    }

    /**
     * Парсинг нескольких URL из строки (через разделители ;, |, запятая)
     */
    private function parsePhotoUrls(string $photoUrlString): array
    {
        if (empty($photoUrlString)) {
            return [];
        }
        
        // Поддерживаем разделители: ; , | или перенос строки
        $urls = preg_split('/[;,|\n\r]+/', $photoUrlString);
        
        // Очищаем и фильтруем
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls, function($url) {
            if (empty($url)) {
                return false;
            }
            
            // Проверяем, что это валидный URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }
            
            // Проверяем, что это похоже на изображение
            return preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg|tiff|tif|heic|heif|avif|ico)(\?.*)?$/i', $url) ||
                   str_contains($url, 'image') || 
                   str_contains($url, 'photo') || 
                   str_contains($url, 'picture');
        });
        
        // Максимум 5 фото
        return array_values(array_slice($urls, 0, 5));
    }

    /**
     * Получить количество обновленных записей
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Обновление существующего товара
     */
    private function updateExistingProduct($existingProduct, $name, $description, $article, $categoryName, $categoryPhotoUrl, $productPhotoUrl, $characteristicsRaw, $quantity, $price, $markup, $active)
    {
        // Обрабатываем категорию - ищем или создаем с поддержкой загрузки фото
        $category_id = $this->processCategoryWithPhoto($categoryName, $categoryPhotoUrl);

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

        // Обрабатываем URL фото товара - используем улучшенную функцию с определением типа ссылки
        $photoData = $this->processProductPhotos($productPhotoUrl);
        
        // Обрабатываем количество - приводим к числу, по умолчанию оставляем существующее
        $quantity_clean = $existingProduct->quantity;
        if (isset($quantity) && is_numeric($quantity)) {
            $quantity_clean = max(0, (int) $quantity);
        }
        
        // Обрабатываем цену - приводим к числу, по умолчанию оставляем существующую
        $price_clean = $existingProduct->price;
        if (isset($price)) {
            $price_str = str_replace(',', '.', trim($price));
            if (is_numeric($price_str)) {
                $price_clean = max(0, (float) $price_str);
            }
        }
        
        // Обрабатываем наценку - приводим к числу, по умолчанию оставляем существующую
        $markup_clean = $existingProduct->markup_percentage;
        if (isset($markup)) {
            $markup_str = str_replace(',', '.', trim($markup));
            if (is_numeric($markup_str)) {
                $markup_clean = max(0, min(1000, (float) $markup_str));
            }
        }
        
        // Обрабатываем активность - более гибкая проверка
        $is_active = $existingProduct->is_active;
        if (isset($active)) {
            if (is_numeric($active)) {
                $is_active = (int) $active > 0;
            } elseif (is_string($active)) {
                $active_value = strtolower(trim($active));
                $is_active = !in_array($active_value, ['0', 'false', 'нет', 'no', '']);
            } else {
                $is_active = (bool) $active;
            }
        }

        try {
            // Обновляем существующий товар
            $existingProduct->update([
                'category_id' => $category_id,
                'name' => $name,
                'description' => !empty($description) ? $description : $existingProduct->description,
                'photo_url' => $photoData['main_photo'] ?: $existingProduct->photo_url,
                'photos_gallery' => $photoData['photos_gallery'] ?: $existingProduct->photos_gallery,
                'main_photo_index' => $photoData['main_photo_index'] ?? $existingProduct->main_photo_index ?? 0,
                'specifications' => !empty($specifications) ? $specifications : $existingProduct->specifications,
                'quantity' => $quantity_clean,
                'price' => $price_clean,
                'markup_percentage' => $markup_clean,
                'is_active' => $is_active,
            ]);

            // Если включена загрузка изображений и есть URL
            if ($this->downloadImages && !empty($photoData['photo_urls'])) {
                // Устанавливаем статус
                $existingProduct->update(['images_download_status' => 'pending']);
                
                // Добавляем Job для загрузки с увеличенной задержкой
                $delay = now()->addSeconds($this->updatedCount * 5);
                
                DownloadProductImagesJob::dispatch(
                    $existingProduct->id,
                    $photoData['photo_urls'],
                    $photoData['is_yandex_disk']
                )->delay($delay);

                Log::info('Job загрузки изображений добавлен для обновлённого товара', [
                    'product_id' => $existingProduct->id,
                    'urls_count' => count($photoData['photo_urls']),
                    'delay_seconds' => $this->updatedCount * 5,
                ]);
            }

            $this->updatedCount++;
            return null; // Возвращаем null, так как товар уже обновлен

        } catch (\Exception $e) {
            $this->importErrors[] = "Строка: Ошибка обновления товара '$article' - " . $e->getMessage();
            $this->skippedCount++;
            Log::error('Error updating product', [
                'error' => $e->getMessage(),
                'article' => $article,
                'product_id' => $existingProduct->id
            ]);
            return null;
        }
    }

    /**
     * Обработка категории - создание или обновление с поддержкой загрузки фото
     * 
     * @param string $categoryName Название категории
     * @param string|null $categoryPhotoUrl URL фото категории
     * @return int|null ID категории
     */
    private function processCategoryWithPhoto($categoryName, $categoryPhotoUrl = null)
    {
        if (empty($categoryName)) {
            return null;
        }

        // Проверяем кеш обработанных категорий
        $cacheKey = $categoryName . '|' . $this->telegramBotId;
        
        if (isset($this->processedCategories[$cacheKey])) {
            return $this->processedCategories[$cacheKey];
        }

        // Ищем существующую категорию для этого бота
        $category = \App\Models\Category::where('name', $categoryName)
            ->where('user_id', Auth::id())
            ->where('telegram_bot_id', $this->telegramBotId)
            ->first();
        
        // Проверяем и обрабатываем URL фото категории
        $categoryPhotoUrlClean = null;
        $isCategoryYandexDisk = false;
        
        if (!empty($categoryPhotoUrl)) {
            if (filter_var($categoryPhotoUrl, FILTER_VALIDATE_URL) !== false) {
                // Проверяем, является ли это ссылкой на Яндекс.Диск
                $yandexService = app(YandexDiskService::class);
                if ($yandexService->isYandexDiskUrl($categoryPhotoUrl)) {
                    $isCategoryYandexDisk = true;
                    $categoryPhotoUrlClean = $categoryPhotoUrl;
                    Log::info("Обнаружена ссылка на Яндекс.Диск для категории", [
                        'category' => $categoryName,
                        'url' => $categoryPhotoUrl
                    ]);
                } else {
                    // Прямая ссылка на изображение
                    $categoryPhotoUrlClean = $categoryPhotoUrl;
                }
            }
        }
        
        // Если категории нет - создаем новую
        if (!$category) {
            try {
                $category = \App\Models\Category::create([
                    'user_id' => Auth::id(),
                    'telegram_bot_id' => $this->telegramBotId,
                    'name' => $categoryName,
                    'description' => null,
                    'photo_url' => null, // Временно null, обновим после загрузки
                    'is_active' => true,
                ]);
                
                // Если нужно скачивать фото и есть URL - запускаем Job с задержкой
                if ($this->downloadImages && $categoryPhotoUrlClean) {
                    Log::info("Запуск загрузки фото для новой категории", [
                        'category_id' => $category->id,
                        'category_name' => $categoryName,
                        'url' => $categoryPhotoUrlClean,
                        'is_yandex' => $isCategoryYandexDisk
                    ]);
                    
                    // Увеличена задержка до 3 секунд
                    DownloadCategoryPhotoJob::dispatch(
                        $category->id,
                        $categoryPhotoUrlClean,
                        $isCategoryYandexDisk
                    )->delay(now()->addSeconds(3));
                } elseif (!$this->downloadImages && $categoryPhotoUrlClean) {
                    // Если не скачиваем, просто сохраняем URL
                    $category->update(['photo_url' => $categoryPhotoUrlClean]);
                    Log::info("Created category '{$categoryName}' with photo URL: {$categoryPhotoUrlClean}");
                }
            } catch (\Exception $e) {
                // Если не удалось создать категорию, продолжаем без неё
                $this->importErrors[] = "Предупреждение: Не удалось создать категорию '$categoryName' - " . $e->getMessage();
                return null;
            }
        } else {
            // Если категория существует - проверяем, нужно ли обновить фото
            // Обновляем если:
            // 1. Фото вообще нет (пустое)
            // 2. Или текущее фото - это URL (http/https), а не путь к файлу
            $shouldUpdatePhoto = empty($category->photo_url) || 
                                 (filter_var($category->photo_url, FILTER_VALIDATE_URL) !== false);
            
            if ($shouldUpdatePhoto && $categoryPhotoUrlClean) {
                try {
                    if ($this->downloadImages) {
                        Log::info("Запуск загрузки фото для существующей категории", [
                            'category_id' => $category->id,
                            'category_name' => $categoryName,
                            'url' => $categoryPhotoUrlClean,
                            'is_yandex' => $isCategoryYandexDisk,
                            'current_photo' => $category->photo_url
                        ]);
                        
                        // Увеличена задержка до 3 секунд
                        DownloadCategoryPhotoJob::dispatch(
                            $category->id,
                            $categoryPhotoUrlClean,
                            $isCategoryYandexDisk
                        )->delay(now()->addSeconds(3));
                    } else {
                        // Если не скачиваем, просто сохраняем URL
                        $category->update(['photo_url' => $categoryPhotoUrlClean]);
                        Log::info("Updated category '{$categoryName}' with photo URL: {$categoryPhotoUrlClean}");
                    }
                } catch (\Exception $e) {
                    $this->importErrors[] = "Предупреждение: Не удалось обновить фото категории '$categoryName' - " . $e->getMessage();
                }
            }
        }
        
        if ($category) {
            $category_id = $category->id;
            // Кешируем результат
            $this->processedCategories[$cacheKey] = $category_id;
            return $category_id;
        }

        return null;
    }
}