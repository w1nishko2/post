<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook роуты для Telegram (пока заглушка)
Route::post('/telegram/webhook/{bot}', function() {
    return response()->json(['ok' => true]);
})->name('telegram.webhook');

// API для Mini App
Route::prefix('mini-app')->group(function () {
    Route::get('/{shortName}/config', [App\Http\Controllers\MiniAppController::class, 'getConfig']);
    Route::get('/user-data', [App\Http\Controllers\MiniAppController::class, 'getUserData']);
    Route::post('/save-data', [App\Http\Controllers\MiniAppController::class, 'saveData']);
});

// API роуты для работы с ботами (требуют авторизации)
Route::middleware('auth:sanctum')->prefix('telegram-bots')->group(function () {
    Route::get('/', [App\Http\Controllers\TelegramBotController::class, 'index']);
    Route::post('/', [App\Http\Controllers\TelegramBotController::class, 'store']);
    Route::get('/{bot}', [App\Http\Controllers\TelegramBotController::class, 'show']);
    Route::put('/{bot}', [App\Http\Controllers\TelegramBotController::class, 'update']);
    Route::delete('/{bot}', [App\Http\Controllers\TelegramBotController::class, 'destroy']);
    Route::post('/{bot}/toggle', [App\Http\Controllers\TelegramBotController::class, 'toggle']);
});
