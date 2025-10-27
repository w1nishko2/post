<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Автоматическая отмена просроченных заказов каждые 15 минут
        $schedule->command('orders:cancel-expired')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Очистка старых сессий (вместо несуществующей session:clear)
        $schedule->command('app:clear-sessions')
                 ->daily()
                 ->withoutOverlapping();

        // 🚀 СИСТЕМА ИМПОРТА: Обработка очереди импорта (каждую минуту, по 50 товаров)
        $schedule->command('import:process-queue --limit=50')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // 🛒 СИСТЕМА ОФОРМЛЕНИЯ ЗАКАЗОВ: Обработка очереди checkout (каждую минуту, по 100 заказов)
        $schedule->command('checkout:process-queue --limit=100')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
