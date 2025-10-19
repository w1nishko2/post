<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Product;
use App\Models\TelegramBot;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TestMarkupImportExport extends Command
{
    protected $signature = 'test:markup-excel';
    protected $description = 'Тестирует функционал импорта и экспорта Excel с полем наценки';

    public function handle()
    {
        $this->info('=== Тестирование Excel Import/Export с наценкой ===');
        
        // 1. Находим пользователя и бота для теста
        $user = User::first();
        if (!$user) {
            $this->error("❌ Не найден пользователь для теста");
            return;
        }
        
        Auth::login($user);
        $this->info("Используем пользователя: {$user->email}");
        
        $bot = $user->telegramBots()->first();
        if (!$bot) {
            $this->error("❌ Не найден бот для теста");
            return;
        }
        
        $this->info("Используем бота: {$bot->bot_name}");
        
        // 2. Тестируем экспорт шаблона
        $this->info('1. Тестирую экспорт шаблона...');
        try {
            $export = new ProductsTemplateExport();
            $filePath = storage_path('app/test_markup_template.xlsx');
            Excel::store($export, 'test_markup_template.xlsx');
            $this->info("✅ Шаблон создан: {$filePath}");
        } catch (\Exception $e) {
            $this->error("❌ Ошибка экспорта: " . $e->getMessage());
            return;
        }
        
        // 3. Создаем тестовые данные с наценкой
        $this->info('2. Создаю тестовые данные с наценкой...');
        $testData = [
            ['Товар с наценкой 1', 'Описание товара 1', 'MARKUP001', 'Тестовая категория', '', '', 'Характеристика 1', '10', '1000', '15', '1'],
            ['Товар с наценкой 2', 'Описание товара 2', 'MARKUP002', 'Тестовая категория', '', '', 'Характеристика 2', '5', '2000', '25.5', '1'],
            ['Товар без наценки', 'Описание товара 3', 'MARKUP003', 'Тестовая категория', '', '', 'Характеристика 3', '8', '1500', '0', '1'],
            ['Товар с большой наценкой', 'Описание товара 4', 'MARKUP004', 'Тестовая категория', '', '', '', '3', '500', '100', '1'],
        ];
        
        // Создаем Excel файл с тестовыми данными
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовки
        $headers = [
            'Название товара', 'Описание', 'Артикул', 'Категория', 
            'URL фото категории', 'URL фото товара', 'Характеристики (через ;)', 
            'Количество', 'Цена', 'Наценка (%)', 'Активный (1/0)'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($testData, null, 'A2');
        
        $testFilePath = storage_path('app/test_markup_data.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($testFilePath);
        
        $this->info("✅ Тестовые данные созданы: {$testFilePath}");
        
        // 4. Тестируем импорт
        $this->info('3. Тестирую импорт с наценкой...');
        try {
            $import = new ProductsImport($bot->id);
            Excel::import($import, $testFilePath);
            
            $this->info("✅ Импорт завершен!");
            $this->info("   - Добавлено товаров: {$import->getImportedCount()}");
            $this->info("   - Пропущено строк: {$import->getSkippedCount()}");
            
            if ($import->hasErrors()) {
                $this->warn("⚠️ Обнаружены ошибки:");
                foreach ($import->getImportErrors() as $error) {
                    $this->line("   " . $error);
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Ошибка импорта: " . $e->getMessage());
            return;
        }
        
        // 5. Проверяем созданные товары с наценкой
        $testProducts = Product::where('user_id', $user->id)
                             ->whereIn('article', ['MARKUP001', 'MARKUP002', 'MARKUP003', 'MARKUP004'])
                             ->get();
        
        $this->info('4. Проверяю импортированные товары с наценкой...');
        $this->info("Найдено тестовых товаров: {$testProducts->count()}");
        
        foreach ($testProducts as $product) {
            $this->line("   - {$product->name}");
            $this->line("     Артикул: {$product->article}");
            $this->line("     Цена: {$product->price} ₽");
            $this->line("     Наценка: {$product->markup_percentage}%");
            $this->line("     Цена с наценкой: " . number_format($product->price_with_markup, 2) . " ₽");
            $this->line("");
        }
        
        // 6. Очистка (опционально)
        if ($this->confirm('Удалить тестовые товары?', true)) {
            foreach ($testProducts as $product) {
                $product->delete();
                $this->line("Удален: {$product->name}");
            }
        }
        
        // Удаляем тестовые файлы
        if (file_exists($testFilePath)) {
            unlink($testFilePath);
        }
        
        $this->info('=== Тест с наценкой завершен успешно! ===');
    }
}