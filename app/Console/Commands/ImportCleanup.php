<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ImportCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cleanup 
                            {--reset-pending : Сбросить статус "pending" в null}
                            {--reset-processing : Сбросить статус "processing" в null}
                            {--retry-failed : Перезапустить провалившиеся задачи очереди}
                            {--all : Выполнить все операции очистки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка зависших импортов и перезапуск задач';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resetPending = $this->option('reset-pending');
        $resetProcessing = $this->option('reset-processing');
        $retryFailed = $this->option('retry-failed');
        $all = $this->option('all');

        if (!$resetPending && !$resetProcessing && !$retryFailed && !$all) {
            $this->error('Укажите хотя бы одну опцию или используйте --all');
            $this->info('Доступные опции:');
            $this->line('  --reset-pending      Сбросить "ожидающие" загрузки');
            $this->line('  --reset-processing   Сбросить "в процессе" загрузки');
            $this->line('  --retry-failed       Перезапустить провалившиеся задачи');
            $this->line('  --all                Выполнить все операции');
            return Command::FAILURE;
        }

        if ($all) {
            $resetPending = true;
            $resetProcessing = true;
            $retryFailed = true;
        }

        // Сброс pending статусов
        if ($resetPending) {
            $this->info('🔄 Сброс статусов "pending"...');
            
            $count = Product::where('images_download_status', 'pending')
                ->whereDate('updated_at', '<', now()->subHours(1))
                ->update([
                    'images_download_status' => null,
                    'images_download_error' => 'Сброшено автоматически: зависло более 1 часа'
                ]);

            if ($count > 0) {
                $this->info("  ✅ Сброшено {$count} товаров в статусе 'pending'");
            } else {
                $this->line("  ℹ️  Нет зависших товаров в статусе 'pending'");
            }
        }

        // Сброс processing статусов
        if ($resetProcessing) {
            $this->info('🔄 Сброс статусов "processing"...');
            
            $count = Product::where('images_download_status', 'processing')
                ->whereDate('updated_at', '<', now()->subMinutes(30))
                ->update([
                    'images_download_status' => 'failed',
                    'images_download_error' => 'Превышено время обработки (30 минут)'
                ]);

            if ($count > 0) {
                $this->warn("  ⚠️  Сброшено {$count} товаров в статусе 'processing'");
            } else {
                $this->line("  ℹ️  Нет зависших товаров в статусе 'processing'");
            }
        }

        // Перезапуск провалившихся задач
        if ($retryFailed) {
            $this->info('🔄 Перезапуск провалившихся задач очереди...');
            
            try {
                $failedCount = DB::table('failed_jobs')->count();
                
                if ($failedCount > 0) {
                    $this->call('queue:retry', ['id' => ['all']]);
                    $this->info("  ✅ Перезапущено {$failedCount} провалившихся задач");
                } else {
                    $this->line("  ℹ️  Нет провалившихся задач");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Ошибка перезапуска задач: " . $e->getMessage());
            }
        }

        $this->line('');
        $this->info('✅ Очистка завершена!');
        $this->line('');
        $this->info('💡 Рекомендации:');
        $this->line('  1. Проверьте статус: php artisan import:status');
        $this->line('  2. Убедитесь, что queue worker запущен: php artisan queue:work');
        $this->line('  3. Проверьте логи: tail storage/logs/laravel.log');

        return Command::SUCCESS;
    }
}
