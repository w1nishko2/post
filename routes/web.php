<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Роуты для управления Telegram ботами (требуют авторизации)
use Illuminate\Support\Facades\URL;

Route::middleware('auth')->group(function () {
    Route::resource('telegram-bots', App\Http\Controllers\TelegramBotController::class)->parameters([
        'telegram-bots' => 'telegramBot'
    ]);
    Route::post('telegram-bots/{telegramBot}/toggle', [App\Http\Controllers\TelegramBotController::class, 'toggle'])->name('telegram-bots.toggle');
    Route::post('telegram-bots/{telegramBot}/setup-mini-app', [App\Http\Controllers\TelegramBotController::class, 'setupMiniApp'])->name('telegram-bots.setup-mini-app');
    Route::post('telegram-bots/{telegramBot}/setup-forum-auto', [App\Http\Controllers\TelegramBotController::class, 'setupForumAuto'])->name('telegram-bots.setup-forum-auto');
    
    // Роут для тестирования Forum-Auto подключения (веб-сессия)
    Route::post('/test-forum-auto-connection', [App\Http\Controllers\TelegramBotController::class, 'testForumAutoConnection'])->name('test-forum-auto-connection');
});
if (app()->environment('production')) {
    URL::forceScheme('https');
}
// Роут для Mini App (должен быть в самом конце, чтобы не конфликтовать с другими роутами)
Route::get('/{shortName}', [App\Http\Controllers\MiniAppController::class, 'show'])
    ->where('shortName', '[a-zA-Z0-9_]+')
    ->name('mini-app.show');
