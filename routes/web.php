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

Route::middleware(['auth'])->group(function () {
    
    // Роуты для товаров в контексте бота (с проверкой владения)
    Route::middleware(['ownership:telegramBot'])->group(function () {
        Route::get('/bots/{telegramBot}/products', [App\Http\Controllers\ProductController::class, 'index'])->name('bot.products.index');
        Route::get('/bots/{telegramBot}/products/create', [App\Http\Controllers\ProductController::class, 'create'])->name('bot.products.create');
        
        // Критические операции с Rate Limiting
        Route::post('/bots/{telegramBot}/products', [App\Http\Controllers\ProductController::class, 'store'])
            ->middleware('throttle:10,1') // Максимум 10 товаров в минуту
            ->name('bot.products.store');
            
        Route::get('/bots/{telegramBot}/products/{product}', [App\Http\Controllers\ProductController::class, 'show'])->name('bot.products.show');
        Route::get('/bots/{telegramBot}/products/{product}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('bot.products.edit');
        
        // Табличное представление товаров
        Route::get('/bots/{telegramBot}/products-table', [App\Http\Controllers\ProductController::class, 'table'])->name('bot.products.table');
        Route::patch('/bots/{telegramBot}/products/{product}/quick-update', [App\Http\Controllers\ProductController::class, 'quickUpdate'])->name('bot.products.quick-update');
        
        Route::put('/bots/{telegramBot}/products/{product}', [App\Http\Controllers\ProductController::class, 'update'])
            ->middleware('throttle:20,1') // Максимум 20 обновлений в минуту
            ->name('bot.products.update');
            
        Route::delete('/bots/{telegramBot}/products/{product}', [App\Http\Controllers\ProductController::class, 'destroy'])
            ->middleware('throttle:5,1') // Максимум 5 удалений в минуту
            ->name('bot.products.destroy');
        
        // Роуты для импорта/экспорта товаров в контексте бота
        Route::get('/bots/{telegramBot}/products-template/download', [App\Http\Controllers\ProductController::class, 'downloadTemplate'])->name('bot.products.download-template');
        Route::get('/bots/{telegramBot}/products-data/export', [App\Http\Controllers\ProductController::class, 'exportData'])->name('bot.products.export-data');
        
        Route::post('/bots/{telegramBot}/products/import', [App\Http\Controllers\ProductController::class, 'importFromExcel'])
            ->middleware('throttle:10,5') // Максимум 10 импортов в 5 минут
            ->name('bot.products.import');
        
        // Роуты для категорий в контексте бота (с проверкой владения)
        Route::get('/bots/{telegramBot}/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('bot.categories.index');
        Route::get('/bots/{telegramBot}/categories/create', [App\Http\Controllers\CategoryController::class, 'create'])->name('bot.categories.create');
        
        Route::post('/bots/{telegramBot}/categories', [App\Http\Controllers\CategoryController::class, 'store'])
            ->middleware('throttle:5,1') // Максимум 5 категорий в минуту
            ->name('bot.categories.store');
            
        Route::get('/bots/{telegramBot}/categories/{category}', [App\Http\Controllers\CategoryController::class, 'show'])->name('bot.categories.show');
        Route::get('/bots/{telegramBot}/categories/{category}/edit', [App\Http\Controllers\CategoryController::class, 'edit'])->name('bot.categories.edit');
        
        Route::put('/bots/{telegramBot}/categories/{category}', [App\Http\Controllers\CategoryController::class, 'update'])
            ->middleware('throttle:10,1') // Максимум 10 обновлений категорий в минуту
            ->name('bot.categories.update');
            
        Route::delete('/bots/{telegramBot}/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])
            ->middleware('throttle:3,1') // Максимум 3 удаления категорий в минуту
            ->name('bot.categories.destroy');
        Route::get('/bots/{telegramBot}/categories-api', [App\Http\Controllers\CategoryController::class, 'apiIndex'])->name('bot.categories.api');
    });
    
    // Старые роуты для товаров (для обратной совместимости - перенаправляют на выбор бота)
    Route::get('products', [App\Http\Controllers\ProductController::class, 'selectBot'])->name('products.select-bot');
    Route::get('products/{any}', [App\Http\Controllers\ProductController::class, 'redirectToBot'])->where('any', '.*')->name('products.redirect');
    
    // Роуты для Telegram ботов
    Route::resource('telegram-bots', App\Http\Controllers\TelegramBotController::class)->parameters([
        'telegram-bots' => 'telegramBot'
    ]);
    Route::patch('telegram-bots/{telegramBot}/toggle', [App\Http\Controllers\TelegramBotController::class, 'toggle'])->name('telegram-bots.toggle');
    Route::post('telegram-bots/{telegramBot}/setup-mini-app', [App\Http\Controllers\TelegramBotController::class, 'setupMiniApp'])->name('telegram-bots.setup-mini-app');

    // Роуты для заказов
    Route::resource('orders', App\Http\Controllers\OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('api/orders/bot', [App\Http\Controllers\OrderController::class, 'botOrders'])->name('orders.bot');
    Route::get('api/orders/stats', [App\Http\Controllers\OrderController::class, 'stats'])->name('orders.stats');

    // Роуты для профиля пользователя
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
        Route::patch('/email', [App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('update.email');
        Route::patch('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('update.password');
        Route::patch('/name', [App\Http\Controllers\ProfileController::class, 'updateName'])->name('update.name');
    });
});

// Роуты для корзины (доступны всем, включая неавторизованных через сессию)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'getCartData'])->name('api');
    Route::get('/view', [App\Http\Controllers\CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [App\Http\Controllers\CartController::class, 'add'])->name('add');
    Route::patch('/update/{cart}', [App\Http\Controllers\CartController::class, 'update'])->name('update');
    Route::delete('/remove/{cart}', [App\Http\Controllers\CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('clear');
    Route::get('/count', [App\Http\Controllers\CartController::class, 'getCount'])->name('count');
    Route::post('/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('checkout');
});
if (app()->environment('production')) {
    URL::forceScheme('https');
}

// Webhook для Telegram ботов
Route::post('/telegram/webhook/{bot}', [App\Http\Controllers\TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook')
    ->where('bot', '[0-9]+');

// API роуты для Mini App
Route::prefix('{shortName}/api')->where(['shortName' => '[a-zA-Z0-9_]+'])->group(function () {
    Route::get('/products', [App\Http\Controllers\MiniAppController::class, 'getProducts'])->name('mini-app.api.products');
    Route::get('/categories', [App\Http\Controllers\MiniAppController::class, 'getCategories'])->name('mini-app.api.categories');
    Route::get('/search', [App\Http\Controllers\MiniAppController::class, 'searchProducts'])->name('mini-app.api.search');
    Route::get('/config', [App\Http\Controllers\MiniAppController::class, 'getConfig'])->name('mini-app.api.config');
    Route::get('/products/{productId}', [App\Http\Controllers\MiniAppController::class, 'getProduct'])->name('mini-app.api.product');
    Route::post('/validate-cart', [App\Http\Controllers\MiniAppController::class, 'validateCart'])->name('mini-app.api.validate-cart');
});

// Роут для Mini App (должен быть в самом конце, чтобы не конфликтовать с другими роутами)
Route::get('/{shortName}', [App\Http\Controllers\MiniAppController::class, 'show'])
    ->where('shortName', '[a-zA-Z0-9_]+')
    ->name('mini-app.show');
