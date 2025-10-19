<?php

namespace App\Imports;

use App\Models\Product;
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
    private $telegramBotId;
    private $processedCategories = []; // Кеш обработанных категорий

    public function __construct($telegramBotId = null)
    {
        $this->telegramBotId = $telegramBotId;
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

        // Проверяем уникальность артикула
        $existingProduct = Product::where('article', $article)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($existingProduct) {
            $this->importErrors[] = "Строка: Товар с артикулом '$article' уже существует";
            $this->skippedCount++;
            return null;
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
        
        // Обрабатываем категорию - ищем или создаем
        $category_id = null;
        if (!empty($categoryName)) {
            // Проверяем кеш обработанных категорий
            $cacheKey = $categoryName . '|' . $this->telegramBotId;
            
            if (isset($this->processedCategories[$cacheKey])) {
                $category_id = $this->processedCategories[$cacheKey];
            } else {
                // Ищем существующую категорию для этого бота
                $category = \App\Models\Category::where('name', $categoryName)
                    ->where('user_id', Auth::id())
                    ->where('telegram_bot_id', $this->telegramBotId)
                    ->first();
                
                // Проверяем и обрабатываем URL фото категории
                $categoryPhotoUrlClean = null;
                if (!empty($categoryPhotoUrl)) {
                    if (filter_var($categoryPhotoUrl, FILTER_VALIDATE_URL) !== false) {
                        $categoryPhotoUrlClean = $categoryPhotoUrl;
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
                            'photo_url' => $categoryPhotoUrlClean,
                            'is_active' => true,
                        ]);
                        
                        if ($categoryPhotoUrlClean) {
                            Log::info("Created category '{$categoryName}' with photo: {$categoryPhotoUrlClean}");
                        }
                    } catch (\Exception $e) {
                        // Если не удалось создать категорию, продолжаем без неё
                        $this->importErrors[] = "Предупреждение: Не удалось создать категорию '$categoryName' - " . $e->getMessage();
                    }
                } else {
                    // Если категория существует, но у неё нет фото, а в импорте есть - обновляем
                    if (empty($category->photo_url) && $categoryPhotoUrlClean) {
                        try {
                            $category->update(['photo_url' => $categoryPhotoUrlClean]);
                            Log::info("Updated category '{$categoryName}' with photo: {$categoryPhotoUrlClean}");
                        } catch (\Exception $e) {
                            $this->importErrors[] = "Предупреждение: Не удалось обновить фото категории '$categoryName' - " . $e->getMessage();
                        }
                    }
                }
                
                if ($category) {
                    $category_id = $category->id;
                    // Кешируем результат
                    $this->processedCategories[$cacheKey] = $category_id;
                }
            }
        }

        // Обрабатываем URL фото товара - проверяем, что это действительно URL
        $productPhotoUrlClean = null;
        if (!empty($productPhotoUrl)) {
            if (filter_var($productPhotoUrl, FILTER_VALIDATE_URL) !== false) {
                $productPhotoUrlClean = $productPhotoUrl;
            }
        }
        
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
            return new Product([
                'user_id' => Auth::id(),
                'telegram_bot_id' => $this->telegramBotId,
                'category_id' => $category_id,
                'name' => $name,
                'description' => $description,
                'article' => $article,
                'photo_url' => $productPhotoUrlClean,
                'specifications' => !empty($specifications) ? $specifications : null,
                'quantity' => $quantity_clean,
                'price' => $price_clean,
                'markup_percentage' => $markup_clean,
                'is_active' => $is_active,
            ]);
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
}