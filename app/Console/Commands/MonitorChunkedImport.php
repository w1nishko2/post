<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Команда для мониторинга чанкового импорта
 */
class MonitorChunkedImport extends Command
{
    protected $signature = 'import:monitor {session_id? : UUID сессии импорта}';
    protected $description = 'Мониторинг статуса chunked импорта';

    public function handle()
    {
        $sessionId = $this->argument('session_id');

        if ($sessionId) {
            $this->monitorSpecificSession($sessionId);
        } else {
            $this->monitorAllImports();
        }
    }

    private function monitorSpecificSession(string $sessionId)
    {
        $this->info("📊 Мониторинг импорта: {$sessionId}");
        $this->newLine();

        // Проверяем jobs в очереди imports
        $pendingJobs = DB::table('jobs')
            ->where('queue', 'imports')
            ->where('payload', 'like', "%{$sessionId}%")
            ->count();

        // Проверяем failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', "%{$sessionId}%")
            ->count();

        // Логи успешных импортов
        $successLog = DB::table('products')
            ->where('updated_at', '>=', now()->subHours(1))
            ->count();

        $this->table(
            ['Параметр', 'Значение'],
            [
                ['Сессия', $sessionId],
                ['Jobs в очереди', $pendingJobs],
                ['Провалившихся jobs', $failedJobs],
                ['Товаров создано (1ч)', $successLog],
                ['Статус', $pendingJobs > 0 ? '🔄 В процессе' : '✅ Завершено'],
            ]
        );

        if ($failedJobs > 0) {
            $this->warn("⚠️ Найдено {$failedJobs} проваленных задач!");
            $this->info("Используйте: php artisan queue:retry --queue=imports");
        }
    }

    private function monitorAllImports()
    {
        $this->info("📊 Общая статистика импорта");
        $this->newLine();

        // Очередь imports
        $importsPending = DB::table('jobs')->where('queue', 'imports')->count();
        $importsReserved = DB::table('jobs')->where('queue', 'imports')->whereNotNull('reserved_at')->count();

        // Очередь images
        $imagesPending = DB::table('jobs')->where('queue', 'images')->count();
        $imagesReserved = DB::table('jobs')->where('queue', 'images')->whereNotNull('reserved_at')->count();

        // Failed jobs
        $failedTotal = DB::table('failed_jobs')->count();
        $failedImports = DB::table('failed_jobs')->where('queue', 'imports')->count();
        $failedImages = DB::table('failed_jobs')->where('queue', 'images')->count();

        $this->table(
            ['Очередь', 'Ожидают', 'Обрабатываются', 'Провалились'],
            [
                ['imports (товары)', $importsPending, $importsReserved, $failedImports],
                ['images (изображения)', $imagesPending, $imagesReserved, $failedImages],
                ['ИТОГО', $importsPending + $imagesPending, $importsReserved + $imagesReserved, $failedTotal],
            ]
        );

        if ($failedTotal > 0) {
            $this->warn("⚠️ Есть проваленные задачи!");
            $this->newLine();
            $this->info("Команды для восстановления:");
            $this->line("  php artisan queue:retry --queue=imports");
            $this->line("  php artisan queue:retry --queue=images");
            $this->line("  php artisan queue:retry all");
        }

        if ($importsPending + $imagesPending === 0) {
            $this->info("✅ Все задачи выполнены!");
        } else {
            $this->warn("🔄 Обработка продолжается...");
        }
    }
}
