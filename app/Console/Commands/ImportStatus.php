<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ImportStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:status 
                            {--bot= : ID телеграм-бота для фильтрации}
                            {--detailed : Показать детальную информацию}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Показать статус импорта товаров и загрузки изображений';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botId = $this->option('bot');
        $detailed = $this->option('detailed');

        $this->info('📊 Статус импорта товаров');
        $this->line('');

        // Общая статистика по товарам
        $query = Product::query();
        if ($botId) {
            $query->where('telegram_bot_id', $botId);
        }

        $totalProducts = $query->count();
        $this->info("Всего товаров: {$totalProducts}");
        $this->line('');

        // Статус загрузки изображений
        $this->info('🖼️ Статус загрузки изображений:');
        
        $statuses = [
            'pending' => 'Ожидает загрузки',
            'processing' => 'В процессе загрузки',
            'completed' => 'Завершено',
            'partial' => 'Частично загружено',
            'failed' => 'Ошибка загрузки',
            null => 'Без загрузки',
        ];

        foreach ($statuses as $status => $label) {
            $count = (clone $query)
                ->where(function($q) use ($status) {
                    if ($status === null) {
                        $q->whereNull('images_download_status');
                    } else {
                        $q->where('images_download_status', $status);
                    }
                })
                ->count();

            if ($count > 0) {
                $icon = match($status) {
                    'completed' => '✅',
                    'processing' => '⏳',
                    'pending' => '⏸️',
                    'partial' => '⚠️',
                    'failed' => '❌',
                    default => '➖',
                };
                $this->line("  {$icon} {$label}: {$count}");
            }
        }

        // Показать товары с ошибками
        $failedProducts = (clone $query)
            ->where('images_download_status', 'failed')
            ->get(['id', 'name', 'images_download_error']);

        if ($failedProducts->isNotEmpty()) {
            $this->line('');
            $this->error('❌ Товары с ошибками загрузки:');
            foreach ($failedProducts as $product) {
                $this->line("  ID {$product->id}: {$product->name}");
                if ($detailed && $product->images_download_error) {
                    $this->line("    Ошибка: {$product->images_download_error}");
                }
            }
        }

        // Статистика по очереди
        $this->line('');
        $this->info('📋 Статус очереди:');
        
        try {
            $jobsCount = DB::table('jobs')->count();
            $this->line("  Задач в очереди: {$jobsCount}");

            $failedJobsCount = DB::table('failed_jobs')->count();
            if ($failedJobsCount > 0) {
                $this->error("  ❌ Провалившихся задач: {$failedJobsCount}");
            } else {
                $this->info("  ✅ Провалившихся задач: 0");
            }
        } catch (\Exception $e) {
            $this->warn("  Не удалось получить информацию об очереди");
        }

        // Статистика по категориям
        $categoryQuery = Category::query();
        if ($botId) {
            $categoryQuery->where('telegram_bot_id', $botId);
        }

        $totalCategories = $categoryQuery->count();
        $categoriesWithPhoto = (clone $categoryQuery)->whereNotNull('photo_url')->count();
        $categoriesWithoutPhoto = $totalCategories - $categoriesWithPhoto;

        $this->line('');
        $this->info('📁 Статус категорий:');
        $this->line("  Всего категорий: {$totalCategories}");
        $this->line("  С фотографиями: {$categoriesWithPhoto}");
        if ($categoriesWithoutPhoto > 0) {
            $this->warn("  Без фотографий: {$categoriesWithoutPhoto}");
        }

        // Детальная информация
        if ($detailed) {
            $this->line('');
            $this->info('📋 Последние импортированные товары:');
            
            $recentProducts = (clone $query)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'created_at', 'images_download_status']);

            foreach ($recentProducts as $product) {
                $status = $product->images_download_status ?? 'нет';
                $this->line("  [{$product->created_at->format('Y-m-d H:i')}] {$product->name} (статус: {$status})");
            }
        }

        $this->line('');
        $this->info('✅ Готово!');
        
        return Command::SUCCESS;
    }
}
