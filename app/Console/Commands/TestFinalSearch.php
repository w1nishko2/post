<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestFinalSearch extends Command
{
    protected $signature = 'debug:final-test {bot_id} {search_term}';
    protected $description = 'Final test of search functionality';

    public function handle()
    {
        $botId = $this->argument('bot_id');
        $searchTerm = $this->argument('search_term');
        
        $telegramBot = TelegramBot::find($botId);
        if (!$telegramBot) {
            $this->error("Bot with ID {$botId} not found");
            return;
        }
        
        $this->info("=== FINAL TEST: Search for '{$searchTerm}' in bot {$botId} ===");
        
        // Тест 1: С пустыми параметрами (как раньше ломалось)
        $this->info("\n--- Test 1: With empty is_active parameter ---");
        $request1 = new Request([
            'search' => $searchTerm,
            'category_id' => '',
            'is_active' => '',  // ПУСТОЕ ЗНАЧЕНИЕ
            'sort_by' => 'id',
            'sort_direction' => 'desc'
        ]);
        
        $count1 = $this->runSearch($telegramBot, $request1);
        $this->info("Result: {$count1} products found");
        
        // Тест 2: Без параметра is_active вообще
        $this->info("\n--- Test 2: Without is_active parameter ---");
        $request2 = new Request([
            'search' => $searchTerm,
            'sort_by' => 'id',
            'sort_direction' => 'desc'
        ]);
        
        $count2 = $this->runSearch($telegramBot, $request2);
        $this->info("Result: {$count2} products found");
        
        // Тест 3: С is_active = 1
        $this->info("\n--- Test 3: With is_active = 1 ---");
        $request3 = new Request([
            'search' => $searchTerm,
            'is_active' => '1',
            'sort_by' => 'id',
            'sort_direction' => 'desc'
        ]);
        
        $count3 = $this->runSearch($telegramBot, $request3);
        $this->info("Result: {$count3} products found");
        
        // Результаты
        $this->info("\n=== SUMMARY ===");
        $this->line("Test 1 (empty is_active): {$count1} products");
        $this->line("Test 2 (no is_active): {$count2} products");
        $this->line("Test 3 (is_active=1): {$count3} products");
        
        if ($count1 === $count2 && $count1 > 0) {
            $this->info("✅ SUCCESS: Empty is_active parameter works correctly!");
        } else {
            $this->error("❌ FAILURE: Empty is_active parameter still causes issues");
        }
    }
    
    private function runSearch($telegramBot, $request)
    {
        $query = $telegramBot->products()->with(['category']);
        
        // Поиск (копируем логику из контроллера)
        if ($search = $request->get('search')) {
            $searchTerm = trim(urldecode($search));
            
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('article', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('specifications', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('price', 'LIKE', "%{$searchTerm}%");
                
                $q->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                    $categoryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                });
                
                if (is_numeric($searchTerm)) {
                    $q->orWhere('id', $searchTerm)
                      ->orWhere('quantity', $searchTerm);
                }
                
                $lowerSearch = mb_strtolower($searchTerm);
                if (in_array($lowerSearch, ['активен', 'активный', 'active', 'да', 'yes', '1'])) {
                    $q->orWhere('is_active', 1);
                } elseif (in_array($lowerSearch, ['неактивен', 'неактивный', 'inactive', 'нет', 'no', '0'])) {
                    $q->orWhere('is_active', 0);
                }
            });
        }

        // Фильтр по категории
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Фильтр по статусу (ИСПРАВЛЕННАЯ ВЕРСИЯ)
        $isActiveParam = $request->get('is_active');
        if ($isActiveParam !== null && $isActiveParam !== '') {
            if (in_array($isActiveParam, ['0', '1', 0, 1], true)) {
                $query->where('is_active', (int)$isActiveParam);
            }
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        return $query->count();
    }
}