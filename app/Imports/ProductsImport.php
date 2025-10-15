<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class ProductsImport implements ToModel, WithHeadingRow
{
    use Importable;

    private $importErrors = [];
    private $importedCount = 0;
    private $skippedCount = 0;
    private $telegramBotId;

    public function __construct($telegramBotId = null)
    {
        $this->telegramBotId = $telegramBotId;
    }

    /**
     * Создание модели из строки Excel
     */
    public function model(array $row)
    {
        // Laravel Excel конвертирует русские заголовки в транслитерированные ключи
        $nameKey = 'nazvanie_tovara';
        $articleKey = 'artikul';
        $descriptionKey = 'opisanie';
        $categoryKey = 'kategoriia';
        $photoKey = 'url_foto';
        $characteristicsKey = 'xarakteristiki_cerez';
        $quantityKey = 'kolicestvo';
        $priceKey = 'cena';
        $activeKey = 'aktivnyi_10';

        // Пропускаем полностью пустые строки
        if (empty(array_filter($row, function($value) {
            return !empty(trim($value));
        }))) {
            return null;
        }

        // Пропускаем строки, где нет названия товара и артикула
        if (empty(trim($row[$nameKey] ?? '')) && empty(trim($row[$articleKey] ?? ''))) {
            return null;
        }

        // Пропускаем строки-примеры из шаблона (по артикулам)
        if (isset($row[$articleKey]) && 
            (in_array(trim($row[$articleKey]), ['SM001', 'BT002', 'TB003', 'ART001', 'ART002', 'ART003']))) {
            $this->skippedCount++;
            return null;
        }
        
        // Проверяем обязательные поля для валидации
        if (empty(trim($row[$nameKey] ?? '')) || empty(trim($row[$articleKey] ?? ''))) {
            $this->skippedCount++;
            return null;
        }

        // Обрабатываем и очищаем данные
        $name = trim($row[$nameKey] ?? '');
        $description = !empty(trim($row[$descriptionKey] ?? '')) ? trim($row[$descriptionKey]) : null;
        $article = trim($row[$articleKey] ?? '');

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
        if (!empty($row[$characteristicsKey])) {
            $specs = explode(';', $row[$characteristicsKey]);
            foreach ($specs as $spec) {
                $spec = trim($spec);
                if (!empty($spec)) {
                    $specifications[] = $spec;
                }
            }
        }
        
        // Обрабатываем категорию - ищем или создаем
        $category_id = null;
        if (!empty(trim($row[$categoryKey] ?? ''))) {
            $categoryName = trim($row[$categoryKey]);
            
            // Ищем существующую категорию для этого бота
            $category = \App\Models\Category::where('name', $categoryName)
                ->where('user_id', Auth::id())
                ->where('telegram_bot_id', $this->telegramBotId)
                ->first();
            
            // Если категории нет - создаем новую
            if (!$category) {
                try {
                    $category = \App\Models\Category::create([
                        'user_id' => Auth::id(),
                        'telegram_bot_id' => $this->telegramBotId,
                        'name' => $categoryName,
                        'description' => null,
                        'photo_url' => null,
                        'is_active' => true,
                    ]);
                } catch (\Exception $e) {
                    // Если не удалось создать категорию, продолжаем без неё
                    $this->importErrors[] = "Предупреждение: Не удалось создать категорию '$categoryName' - " . $e->getMessage();
                }
            }
            
            if ($category) {
                $category_id = $category->id;
            }
        }

        // Обрабатываем URL фото - проверяем, что это действительно URL
        $photo_url = null;
        if (!empty(trim($row[$photoKey] ?? ''))) {
            $url = trim($row[$photoKey]);
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                $photo_url = $url;
            }
        }
        
        // Обрабатываем количество - приводим к числу, по умолчанию 0
        $quantity = 0;
        if (isset($row[$quantityKey]) && is_numeric($row[$quantityKey])) {
            $quantity = max(0, (int) $row[$quantityKey]);
        }
        
        // Обрабатываем цену - приводим к числу, по умолчанию 0
        $price = 0;
        if (isset($row[$priceKey]) && is_numeric($row[$priceKey])) {
            $price = max(0, (float) $row[$priceKey]);
        }
        
        // Обрабатываем активность - более гибкая проверка
        $is_active = true; // по умолчанию активен
        if (isset($row[$activeKey])) {
            $active_value = $row[$activeKey];
            if (is_numeric($active_value)) {
                $is_active = (int) $active_value > 0;
            } elseif (is_string($active_value)) {
                $active_value = strtolower(trim($active_value));
                $is_active = !in_array($active_value, ['0', 'false', 'нет', 'no', '']);
            } else {
                $is_active = (bool) $active_value;
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
                'photo_url' => $photo_url,
                'specifications' => !empty($specifications) ? $specifications : null,
                'quantity' => $quantity,
                'price' => $price,
                'is_active' => $is_active,
            ]);
        } catch (\Exception $e) {
            $this->importErrors[] = "Строка: Ошибка создания товара - " . $e->getMessage();
            $this->skippedCount++;
            $this->importedCount--;
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