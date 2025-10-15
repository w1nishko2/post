<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class TestExcelImportExport extends Command
{
    protected $signature = 'test:excel';
    protected $description = 'Тестирует функционал импорта и экспорта Excel';

    public function handle()
    {
        $this->info('=== Тестирование Excel Import/Export ===');
        
        // 1. Тестируем экспорт
        $this->info('1. Тестирую экспорт...');
        try {
            $export = new ProductsTemplateExport();
            $filePath = storage_path('app/test_template.xlsx');
            Excel::store($export, 'test_template.xlsx');
            $this->info("✅ Шаблон создан: {$filePath}");
        } catch (\Exception $e) {
            $this->error("❌ Ошибка экспорта: " . $e->getMessage());
            return;
        }
        
        // 2. Находим пользователя для теста
        $user = User::first();
        if (!$user) {
            $this->error("❌ Не найден пользователь для теста");
            return;
        }
        
        Auth::login($user);
        $this->info("Используем пользователя: {$user->email}");
        
        // 3. Тестируем импорт того же файла
        $this->info('2. Тестирую импорт...');
        try {
            $import = new ProductsImport();
            Excel::import($import, storage_path('app/test_template.xlsx'));
            
            $this->info("✅ Импорт завершен!");
            $this->info("   - Добавлено товаров: {$import->getImportedCount()}");
            $this->info("   - Пропущено строк: {$import->getSkippedCount()}");
            
            if ($import->hasErrors()) {
                $this->warn("⚠️ Обнаружены ошибки:");
                foreach ($import->getImportErrors() as $error) {
                    $this->line("   Строка {$error['row']}: " . implode(', ', $error['errors']));
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Ошибка импорта: " . $e->getMessage());
            return;
        }
        
        // 4. Проверяем созданные товары
        $products = Product::where('user_id', $user->id)->get();
        $this->info("3. Проверяю созданные товары...");
        $this->info("Всего товаров у пользователя: {$products->count()}");
        
        foreach ($products->take(3) as $product) {
            $this->line("   - {$product->name} (арт: {$product->article})");
        }
        
        // 5. Очистка (опционально)
        if ($this->confirm('Удалить тестовые товары?', true)) {
            $testProducts = Product::where('user_id', $user->id)
                                 ->whereIn('article', ['ART001', 'ART002', 'ART003'])
                                 ->get();
            foreach ($testProducts as $product) {
                $product->delete();
                $this->line("Удален: {$product->name}");
            }
        }
        
        $this->info('=== Тест завершен успешно! ===');
    }
}