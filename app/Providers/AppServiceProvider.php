<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Устанавливаем русскую локаль для Carbon
        Carbon::setLocale('ru');
        
        // Устанавливаем московский часовой пояс как дефолтный для всего приложения
        date_default_timezone_set('Europe/Moscow');
    }
}
