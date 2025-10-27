<?php

namespace App\Console\Commands;

use App\Models\ImportQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Команда для мониторинга очереди импорта
 */
class MonitorImportQueue extends Command
{
    protected $signature = 'import:monitor-queue {session_id? : UUID сессии импорта}';
    protected $description = 'Мониторинг статуса очереди импорта';

    public function handle()
    {
        $sessionId = $this->argument('session_id');

        if ($sessionId) {
            $this->monitorSpecificSession($sessionId);
        } else {
            $this->monitorAllSessions();
        }
    }

    private function monitorSpecificSession(string $sessionId)
    {
        $this->info("📊 Мониторинг импорта: {$sessionId}");
        $this->newLine();

        // Статистика по статусам
        $stats = ImportQueue::bySession($sessionId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $pending = $stats['pending'] ?? 0;
        $processing = $stats['processing'] ?? 0;
        $completed = $stats['completed'] ?? 0;
        $failed = $stats['failed'] ?? 0;
        $total = $pending + $processing + $completed + $failed;

        $this->table(
            ['Статус', 'Количество', '%'],
            [
                ['Ожидают (pending)', $pending, $total > 0 ? round($pending / $total * 100, 1) . '%' : '0%'],
                ['Обрабатываются (processing)', $processing, $total > 0 ? round($processing / $total * 100, 1) . '%' : '0%'],
                ['Завершены (completed)', $completed, $total > 0 ? round($completed / $total * 100, 1) . '%' : '0%'],
                ['Ошибки (failed)', $failed, $total > 0 ? round($failed / $total * 100, 1) . '%' : '0%'],
                ['ВСЕГО', $total, '100%'],
            ]
        );

        if ($failed > 0) {
            $this->newLine();
            $this->warn("⚠️ Найдено {$failed} записей с ошибками!");
            
            // Показываем последние ошибки
            $errors = ImportQueue::bySession($sessionId)
                ->where('status', 'failed')
                ->select('id', 'error_message', 'attempts')
                ->limit(5)
                ->get();

            if ($errors->isNotEmpty()) {
                $this->line("Последние ошибки:");
                foreach ($errors as $error) {
                    $this->line("  ID {$error->id}: {$error->error_message} (попыток: {$error->attempts})");
                }
            }
        }

        if ($pending === 0 && $processing === 0) {
            $this->newLine();
            $this->info("✅ Импорт завершён!");
        } else {
            $this->newLine();
            $this->warn("🔄 Импорт в процессе... (осталось: " . ($pending + $processing) . ")");
        }
    }

    private function monitorAllSessions()
    {
        $this->info("📊 Общая статистика очереди импорта");
        $this->newLine();

        // Общая статистика
        $stats = ImportQueue::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $pending = $stats['pending'] ?? 0;
        $processing = $stats['processing'] ?? 0;
        $completed = $stats['completed'] ?? 0;
        $failed = $stats['failed'] ?? 0;
        $total = $pending + $processing + $completed + $failed;

        $this->table(
            ['Статус', 'Количество'],
            [
                ['Ожидают (pending)', $pending],
                ['Обрабатываются (processing)', $processing],
                ['Завершены (completed)', $completed],
                ['Ошибки (failed)', $failed],
                ['ВСЕГО', $total],
            ]
        );

        // Последние сессии
        $recentSessions = ImportQueue::select('session_id', DB::raw('COUNT(*) as total'), DB::raw('MAX(created_at) as last_activity'))
            ->groupBy('session_id')
            ->orderBy('last_activity', 'desc')
            ->limit(5)
            ->get();

        if ($recentSessions->isNotEmpty()) {
            $this->newLine();
            $this->line("Последние сессии импорта:");
            
            foreach ($recentSessions as $session) {
                $this->line("  {$session->session_id} - {$session->total} записей - {$session->last_activity}");
            }
        }

        if ($pending > 0 || $processing > 0) {
            $this->newLine();
            $this->warn("🔄 В очереди: {$pending}, Обрабатывается: {$processing}");
        } else {
            $this->newLine();
            $this->info("✅ Очередь пуста");
        }
    }
}

