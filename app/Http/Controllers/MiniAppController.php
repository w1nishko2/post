<?php

namespace App\Http\Controllers;

use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

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

        return view('mini-app.index', compact('bot', 'shortName'));
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

        // Здесь должна быть валидация подписи от Telegram
        // Для простоты пока пропускаем эту проверку
        
        if (isset($data['user'])) {
            $data['user'] = json_decode($data['user'], true);
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
}