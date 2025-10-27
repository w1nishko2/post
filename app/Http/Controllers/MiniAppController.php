<?php

namespace App\Http\Controllers;

use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MiniAppController extends Controller
{
    /**
     * Отобразить Mini App по короткому имени
     */
    public function show(string $shortName)
    {
        // Находим активного бота по короткому имени
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            abort(404, 'Mini App не найден или неактивен');
        }

        // Проверяем, что у бота настроен Mini App
        if (!$bot->hasMiniApp()) {
            abort(404, 'Mini App не настроен для данного бота');
        }

        // Получаем активные товары конкретного бота
    $products = $bot->activeProducts()
               ->with('category')
               ->orderedForListing()
               ->paginate(12);

        // Получаем активные категории с количеством товаров
        $categories = $bot->activeCategories()
                         ->withCount(['products as products_count' => function ($query) {
                             $query->where('is_active', true);
                         }])
                         ->having('products_count', '>', 0)
                         ->orderBy('name')
                         ->get();

        // Получаем цветовую схему владельца бота
        $owner = $bot->user;
        $colorScheme = $owner ? $owner->getColorSchemeCss() : [];

        return view('mini-app.index', compact('bot', 'shortName', 'products', 'categories', 'colorScheme'));
    }

    /**
     * API для получения данных пользователя (для Mini App)
     */
    public function getUserData(Request $request)
    {
        // Здесь будет логика для получения данных пользователя Telegram
        // На основе данных из Telegram WebApp
        
        $telegramData = $this->validateTelegramWebAppData($request);
        
        if (!$telegramData) {
            return response()->json(['error' => 'Неверные данные Telegram'], 401);
        }

        return response()->json([
            'user' => $telegramData['user'] ?? null,
            'query_id' => $telegramData['query_id'] ?? null,
            'auth_date' => $telegramData['auth_date'] ?? null,
        ]);
    }

    /**
     * API для сохранения данных (для Mini App)
     */
    public function saveData(Request $request)
    {
        $telegramData = $this->validateTelegramWebAppData($request);
        
        if (!$telegramData) {
            return response()->json(['error' => 'Неверные данные Telegram'], 401);
        }

        $validated = $request->validate([
            'data' => 'required|array',
            'bot_id' => 'required|integer|exists:telegram_bots,id'
        ]);

        // Здесь можно сохранить данные пользователя
        // Например, в отдельную таблицу user_data

        return response()->json([
            'success' => true,
            'message' => 'Данные сохранены'
        ]);
    }

    /**
     * Валидация данных от Telegram WebApp
     */
    private function validateTelegramWebAppData(Request $request)
    {
        $initData = $request->header('X-Telegram-Web-App-Init-Data') ?? $request->input('_auth');
        
        if (!$initData) {
            return null;
        }

        // Парсим данные
        parse_str($initData, $data);

        // Базовая валидация структуры данных
        if (!isset($data['user']) || !isset($data['auth_date']) || !isset($data['hash'])) {
            Log::warning('Invalid Telegram WebApp data structure', [
                'init_data_keys' => array_keys($data),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            return null;
        }

        // Проверяем подпись данных от Telegram
        $hash = $data['hash'];
        unset($data['hash']);

        // Формируем data_check_string согласно документации Telegram
        ksort($data);
        $data_check_arr = [];
        foreach ($data as $k => $v) {
            $data_check_arr[] = $k . '=' . $v;
        }
        $data_check_string = implode("\n", $data_check_arr);

        // Получаем токен бота для проверки подписи
        $botToken = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            Log::error('Telegram bot token not configured');
            return null;
        }

        // Вычисляем HMAC согласно документации Telegram
        $secret_key = hash('sha256', $botToken, true);
        $hmac = hash_hmac('sha256', $data_check_string, $secret_key);

        if (!hash_equals($hmac, $hash)) {
            Log::warning('Telegram WebApp data hash verification failed', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expected_hash' => $hmac,
                'received_hash' => $hash
            ]);
            return null;
        }

        // Проверяем время авторизации (не более 24 часов назад)
        $authDate = intval($data['auth_date']);
        $currentTime = time();
        $maxAge = 24 * 60 * 60; // 24 часа

        if (($currentTime - $authDate) > $maxAge) {
            Log::warning('Telegram WebApp data is too old', [
                'auth_date' => $authDate,
                'current_time' => $currentTime,
                'age_hours' => round(($currentTime - $authDate) / 3600, 2)
            ]);
            return null;
        }

        // Парсим данные пользователя
        if (isset($data['user'])) {
            $userData = json_decode($data['user'], true);
            
            if (!$userData || !isset($userData['id']) || !is_numeric($userData['id'])) {
                Log::warning('Invalid user data in Telegram WebApp', [
                    'user_data' => $data['user']
                ]);
                return null;
            }
            
            $data['user'] = $userData;
        }

        return $data;
    }

    /**
     * Получить конфигурацию Mini App
     */
    public function getConfig(string $shortName)
    {
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            return response()->json(['error' => 'Bot not found'], 404);
        }

        return response()->json([
            'bot_username' => $bot->bot_username,
            'app_name' => $bot->bot_name,
            'app_url' => $bot->getMiniAppUrl(),
            'menu_button' => $bot->menu_button,
        ]);
    }

    /**
     * API для получения всех товаров (для поиска)
     */
    public function getProducts(string $shortName)
    {
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            return response()->json(['error' => 'Bot not found'], 404);
        }

    $products = $bot->activeProducts()
               ->with('category')
               ->orderedForListing()
                       ->get()
                       ->map(function ($product) {
                           return [
                               'id' => $product->id,
                               'name' => $product->name,
                               'description' => $product->description,
                               'article' => $product->article,
                               'photo_url' => $product->main_photo_url,
                               'main_photo_url' => $product->main_photo_url,
                               'photos_gallery' => $product->all_photos,
                               'has_multiple_photos' => $product->has_multiple_photos,
                               'price' => $product->price,
                               'price_with_markup' => $product->price_with_markup,
                               'formatted_price' => $product->formatted_price_with_markup,
                               'created_at' => $product->created_at ? $product->created_at->toISOString() : null,
                               'quantity' => $product->quantity,
                               'category_id' => $product->category_id,
                               'category_name' => $product->category ? $product->category->name : null,
                               'is_active' => $product->is_active,
                               'isAvailable' => $product->isAvailable(),
                               'availability_status' => $product->availability_status,
                               'specifications' => $product->specifications,
                           ];
                       });

        return response()->json($products);
    }

    /**
     * API для получения категорий с количеством товаров
     */
    public function getCategories(string $shortName)
    {
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            return response()->json(['error' => 'Bot not found'], 404);
        }

        $categories = $bot->activeCategories()
                         ->withCount(['products as products_count' => function ($query) {
                             $query->where('is_active', true);
                         }])
                         ->having('products_count', '>', 0) // Показываем только категории с товарами
                         ->orderBy('name')
                         ->get()
                         ->map(function ($category) {
                             // Формируем правильный URL для изображения категории
                             $photoUrl = null;
                             if ($category->photo_url) {
                                 $photoUrl = asset('storage/' . ltrim($category->photo_url, '/'));
                             }
                             
                             return [
                                 'id' => $category->id,
                                 'name' => $category->name,
                                 'description' => $category->description,
                                 'photo_url' => $photoUrl,
                                 'products_count' => $category->products_count,
                             ];
                         });

        return response()->json($categories);
    }

    /**
     * API для поиска товаров
     */
    public function searchProducts(Request $request, string $shortName)
    {
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            return response()->json(['error' => 'Bot not found'], 404);
        }

        $query = $request->get('q', '');
        $categoryId = $request->get('category_id');

    $productsQuery = $bot->activeProducts()->with('category')->orderedForListing();

        if (!empty($query)) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('article', 'LIKE', "%{$query}%");
            });
        }

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        $products = $productsQuery->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'article' => $product->article,
                'photo_url' => $product->main_photo_url,
                'main_photo_url' => $product->main_photo_url,
                'price' => $product->price,
                'price_with_markup' => $product->price_with_markup,
                'formatted_price' => $product->formatted_price_with_markup,
                'created_at' => $product->created_at ? $product->created_at->toISOString() : null,
                'quantity' => $product->quantity,
                'category_id' => $product->category_id,
                'category_name' => $product->category ? $product->category->name : null,
                'is_active' => $product->is_active,
                'isAvailable' => $product->isAvailable(),
                'availability_status' => $product->availability_status,
                'specifications' => $product->specifications,
                'photos_gallery' => $product->all_photos,
                'has_multiple_photos' => $product->has_multiple_photos,
            ];
        });

        return response()->json($products);
    }

    /**
     * API для получения актуальной информации о товаре
     */
    public function getProduct(string $shortName, int $productId)
    {
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            return response()->json(['error' => 'Bot not found'], 404);
        }

    $product = $bot->products()
              ->where('id', $productId)
              ->with('category')
              ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'article' => $product->article,
            'photo_url' => $product->main_photo_url,
            'photos_gallery' => $product->all_photos,
            'has_multiple_photos' => $product->has_multiple_photos,
            'main_photo_index' => $product->main_photo_index,
            'specifications' => $product->specifications,
            'price' => $product->price,
            'price_with_markup' => $product->price_with_markup,
            'quantity' => $product->quantity,
            'formatted_price' => $product->formatted_price_with_markup,
            'availability_status' => $product->availability_status,
            'isAvailable' => $product->isAvailable(),
            'is_active' => $product->is_active,
            'category_id' => $product->category_id,
            'category_name' => $product->category ? $product->category->name : null,
            'updated_at' => $product->updated_at->toISOString(),
        ]);
    }

    /**
     * API для проверки актуальности корзины
     */
    public function validateCart(Request $request, string $shortName)
    {
        $bot = TelegramBot::where('mini_app_short_name', $shortName)
                         ->where('is_active', true)
                         ->first();

        if (!$bot) {
            return response()->json(['error' => 'Bot not found'], 404);
        }

        $cartItems = $request->input('cart', []);
        $validatedCart = [];
        $issues = [];

        // Оптимизация: получаем все товары одним запросом вместо N+1
        $productIds = collect($cartItems)->pluck('id')->unique()->filter()->all();
        $products = $bot->products()
                       ->whereIn('id', $productIds)
                       ->get()
                       ->keyBy('id');

        foreach ($cartItems as $item) {
            $productId = $item['id'] ?? null;
            $product = $products->get($productId);

            if (!$product) {
                $issues[] = [
                    'type' => 'product_not_found',
                    'product_id' => $productId,
                    'message' => 'Товар не найден'
                ];
                continue;
            }

            if (!$product->is_active) {
                $issues[] = [
                    'type' => 'product_inactive',
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'message' => 'Товар недоступен'
                ];
                continue;
            }

            $requestedQuantity = max(1, intval($item['quantity'] ?? 1));
            $availableQuantity = $product->quantity;

            if ($availableQuantity <= 0) {
                $issues[] = [
                    'type' => 'out_of_stock',
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'message' => 'Товар закончился'
                ];
                continue;
            }

            if ($requestedQuantity > $availableQuantity) {
                $issues[] = [
                    'type' => 'insufficient_quantity',
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'requested' => $requestedQuantity,
                    'available' => $availableQuantity,
                    'message' => "Доступно только {$availableQuantity} шт."
                ];
                
                // Корректируем количество до доступного
                $requestedQuantity = $availableQuantity;
            }

            $validatedCart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'formatted_price' => $product->formatted_price_with_markup,
                'photo_url' => $product->main_photo_url,
                'quantity' => $requestedQuantity,
                'available_quantity' => $availableQuantity,
                'isAvailable' => $product->isAvailable(),
                'availability_status' => $product->availability_status,
                'total_price' => $product->price * $requestedQuantity
            ];
        }

        return response()->json([
            'cart' => $validatedCart,
            'issues' => $issues,
            'has_issues' => count($issues) > 0,
            'total_amount' => array_sum(array_column($validatedCart, 'total_price'))
        ]);
    }
    
    /**
     * Отследить посещение Mini App
     */
    private function trackMiniAppVisit($bot, $request)
    {
        try {
            // Получаем данные из Telegram WebApp
            $telegramData = $this->extractTelegramDataFromRequest($request);
            
            \App\Models\VisitorStatistics::create([
                'user_id' => $bot->user_id, // ID владельца бота
                'telegram_bot_id' => $bot->id,
                'session_id' => $request->session()->getId(),
                'telegram_chat_id' => $telegramData['chat_id'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('Referer'),
                'page_url' => $request->fullUrl(),
                'visited_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при отслеживании посещения Mini App: ' . $e->getMessage());
        }
    }
    
    /**
     * Извлечь данные Telegram из запроса
     */
    private function extractTelegramDataFromRequest($request)
    {
        $data = [];
        
        // Пытаемся получить chat_id из различных источников
        if ($request->has('tgChatId')) {
            $data['chat_id'] = $request->get('tgChatId');
        }
        
        if ($request->has('chat_id')) {
            $data['chat_id'] = $request->get('chat_id');
        }
        
        // Пытаемся извлечь из Telegram WebApp Init Data
        $initData = $request->header('X-Telegram-Web-App-Init-Data') ?? $request->input('_auth');
        if ($initData) {
            parse_str($initData, $parsedData);
            if (isset($parsedData['user'])) {
                $userData = json_decode($parsedData['user'], true);
                if ($userData && isset($userData['id'])) {
                    $data['chat_id'] = $userData['id'];
                }
            }
        }
        
        return $data;
    }
}