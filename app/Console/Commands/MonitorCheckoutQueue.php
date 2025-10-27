<?php

namespace App\Console\Commands;

use App\Models\CheckoutQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Команда для мониторинга очереди оформления заказов
 */
class MonitorCheckoutQueue extends Command
{
    protected $signature = 'checkout:monitor {session_id? : UUID сессии оформления}';
    protected $description = 'Мониторинг статуса очереди оформления заказов';

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
        $this->info("📊 Мониторинг оформления: {$sessionId}");
        $this->newLine();

        $checkout = CheckoutQueue::where('session_id', $sessionId)->first();

        if (!$checkout) {
            $this->error("❌ Сессия оформления не найдена!");
            return 1;
        }

        $this->table(
            ['Параметр', 'Значение'],
            [
                ['Сессия', $checkout->session_id],
                ['Статус', $checkout->status],
                ['Попыток', $checkout->attempts . '/' . $checkout->max_attempts],
                ['Telegram ID', $checkout->telegram_user_id],
                ['Бот ID', $checkout->telegram_bot_id],
                ['Создано', $checkout->created_at->format('d.m.Y H:i:s')],
                ['ID заказа', $checkout->order_id ?? 'Не создан'],
            ]
        );

        if ($checkout->status === 'failed' && $checkout->error_message) {
            $this->newLine();
            $this->warn("⚠️ Ошибка: {$checkout->error_message}");
        }

        if ($checkout->status === 'completed' && $checkout->order) {
            $this->newLine();
            $this->info("✅ Заказ #{$checkout->order->order_number} успешно создан!");
        }

        if ($checkout->status === 'pending' || $checkout->status === 'processing') {
            $this->newLine();
            $this->warn("🔄 Оформление в процессе...");
        }

        return 0;
    }

    private function monitorAllSessions()
    {
        $this->info("📊 Общая статистика очереди оформления заказов");
        $this->newLine();

        // Общая статистика
        $stats = CheckoutQueue::select('status', DB::raw('COUNT(*) as count'))
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
                ['⏳ В очереди', $pending],
                ['🔄 Обрабатываются', $processing],
                ['✅ Завершено', $completed],
                ['❌ Ошибок', $failed],
                ['───────', '───────'],
                ['📊 Всего', $total],
            ]
        );

        // Последние сессии
        $recentSessions = CheckoutQueue::select('session_id', 'status', 'telegram_user_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentSessions->isNotEmpty()) {
            $this->newLine();
            $this->line("Последние оформления:");
            $this->table(
                ['Сессия', 'Статус', 'Telegram ID', 'Создано'],
                $recentSessions->map(fn($s) => [
                    substr($s->session_id, 0, 8) . '...',
                    $s->status,
                    $s->telegram_user_id,
                    $s->created_at->format('d.m.Y H:i:s'),
                ])
            );
        }

        if ($pending > 0 || $processing > 0) {
            $this->newLine();
            $this->warn("🔄 В очереди: {$pending}, Обрабатывается: {$processing}");
        } else {
            $this->newLine();
            $this->info("✅ Очередь пуста, все заказы обработаны!");
        }

        return 0;
    }
}
