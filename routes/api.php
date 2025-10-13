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

// API для Forum-Auto (для Mini App)
Route::prefix('forum-auto')->group(function () {
    // Роуты для тестирования подключения (требуют авторизации)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/test-connection', function(\Illuminate\Http\Request $request) {
            try {
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'login' => 'required|string',
                    'pass' => 'required|string'
                ]);
                
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'error' => 'Неверные данные']);
                }
                
                $login = $request->get('login');
                $pass = $request->get('pass');
                
                // Тестируем подключение напрямую к API
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withoutVerifying() // Игнорируем SSL сертификаты
                    ->get('https://api.forum-auto.ru/v2/clientinfo', [
                        'login' => $login,
                        'pass' => $pass
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && !empty($data)) {
                        // Преобразуем массив в более удобный формат
                        $clientInfo = [];
                        foreach ($data as $item) {
                            if (isset($item['name']) && isset($item['value'])) {
                                $clientInfo[$item['name']] = $item['value'];
                            }
                        }
                        
                        return response()->json([
                            'success' => true,
                            'client_info' => $clientInfo
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => false,
                    'error' => 'Неверные данные для входа или ошибка API'
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка соединения с сервером Forum-Auto'
                ]);
            }
        });
    });
    
    // Роуты для Mini App (по короткому имени бота)
    Route::prefix('{shortName}')->group(function () {
        Route::get('/test-credentials', [App\Http\Controllers\ForumAutoController::class, 'testCredentials']);
        Route::get('/brands', [App\Http\Controllers\ForumAutoController::class, 'getBrands']);
        Route::get('/goods', [App\Http\Controllers\ForumAutoController::class, 'getGoods']);
        Route::get('/goods/popular', [App\Http\Controllers\ForumAutoController::class, 'getPopularGoods']);
        Route::get('/goods/search', [App\Http\Controllers\ForumAutoController::class, 'searchGoods']);
        Route::get('/goods/{goodsCode}', [App\Http\Controllers\ForumAutoController::class, 'getGoodsDetails']);
        Route::post('/cart/add', [App\Http\Controllers\ForumAutoController::class, 'addToCart']);
        Route::get('/orders', [App\Http\Controllers\ForumAutoController::class, 'getOrders']);
        Route::get('/client-info', [App\Http\Controllers\ForumAutoController::class, 'getClientInfo']);
    });
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
