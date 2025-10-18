<?php

namespace App\Console\Commands;

use App\Models\TelegramBot;
use App\Exports\ProductsDataExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class TestExport extends Command
{
    protected $signature = 'test:export {bot_id}';
    protected $description = 'Test export functionality with UTF-8 encoding';

    public function handle()
    {
        $botId = $this->argument('bot_id');
        
        $bot = TelegramBot::find($botId);
        if (!$bot) {
            $this->error("Bot with ID {$botId} not found");
            return 1;
        }

        $this->info("Testing export for bot: {$bot->bot_name}");
        
        // Получаем товары для проверки
        $products = $bot->products()->with('category')->get();
        $this->info("Found {$products->count()} products");
        
        // Показываем примеры товаров с русскими названиями
        foreach ($products->take(3) as $product) {
            $this->line("- {$product->name} (Category: " . ($product->category ? $product->category->name : 'None') . ")");
        }
        
        // Создаем экспорт
        $export = new ProductsDataExport($bot);
        
        // Тестируем заголовки
        $this->info("\nTesting headings:");
        $headings = $export->headings();
        foreach ($headings as $heading) {
            $this->line("- {$heading}");
        }
        
        // Тестируем маппинг первого товара
        if ($products->count() > 0) {
            $this->info("\nTesting mapping for first product:");
            $firstProduct = $products->first();
            $mapped = $export->map($firstProduct);
            foreach ($mapped as $index => $value) {
                $displayValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                $this->line("- {$headings[$index]}: {$displayValue}");
            }
        }
        
        // Создаем файл экспорта
        $fileName = 'test_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/' . $fileName);
        
        try {
            Excel::store($export, $fileName, 'local', \Maatwebsite\Excel\Excel::XLSX);
            $this->info("\nExport created successfully: {$filePath}");
            
            // Проверяем размер файла
            $fileSize = filesize($filePath);
            $this->info("File size: " . number_format($fileSize) . " bytes");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            return 1;
        }
    }
}