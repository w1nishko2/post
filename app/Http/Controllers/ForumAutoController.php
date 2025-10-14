<?php

namespace App\Http\Controllers;

use App\Models\TelegramBot;
use App\Services\ForumAutoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ForumAutoController extends Controller
{
    /**
     * Тестировать учетные данные Forum-Auto API
     */
    public function testCredentials(string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $forumAutoService = new ForumAutoService($bot);
            $isValid = $forumAutoService->validateCredentials();

            return response()->json([
                'success' => true,
                'credentials_valid' => $isValid,
                'bot_id' => $bot->id,
                'login' => $bot->forum_auto_login
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to test Forum-Auto credentials', [
                'short_name' => $shortName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при тестировании учетных данных: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить список брендов по артикулу
     */
    public function getBrands(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'art' => 'required|string|min:2|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Артикул обязателен и должен содержать минимум 2 символа',
                    'details' => $validator->errors()
                ], 400);
            }

            $article = $request->get('art');

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getBrandsByArticle($article);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get brands from Forum-Auto', [
                'short_name' => $shortName,
                'article' => $request->get('art'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении брендов'
            ], 500);
        }
    }

    /**
     * Получить список товаров
     */
    public function getGoods(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:100',
                'search' => 'string|max:255',
                'brand_id' => 'integer',
                'goods_code' => 'string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Неверные параметры запроса',
                    'details' => $validator->errors()
                ], 400);
            }

            $params = [];

            // Добавляем поисковый параметр если передан
            if ($request->has('art') && !empty($request->get('art'))) {
                $params['art'] = $request->get('art');
            }

            // Добавляем бренд если передан
            if ($request->has('br') && !empty($request->get('br'))) {
                $params['br'] = $request->get('br');
            }

            // Добавляем поиск кроссов/аналогов
            $params['cross'] = 1;

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getGoods($params);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Не удалось получить список товаров'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get goods from Forum-Auto', [
                'short_name' => $shortName,
                'params' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении товаров'
            ], 500);
        }
    }

    /**
     * Получить популярные товары
     */
    public function getPopularGoods(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot) {
                return response()->json([
                    'success' => false,
                    'error' => 'Бот не найден'
                ], 404);
            }

            // Если API не настроен, возвращаем пустой результат (будут показаны демо-товары в JS)
            if (!$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'API не настроен - демо-режим'
                ]);
            }

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getPopularGoods();

            return response()->json([
                'success' => true,
                'data' => $result ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get popular goods from Forum-Auto', [
                'short_name' => $shortName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении популярных товаров'
            ], 500);
        }
    }

    /**
     * Поиск товаров
     */
    public function searchGoods(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:2|max:255',
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Поисковый запрос должен содержать минимум 2 символа',
                    'details' => $validator->errors()
                ], 400);
            }

            $search = $request->get('search');
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->advancedSearchGoods($search, $page, $limit);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to search goods in Forum-Auto', [
                'short_name' => $shortName,
                'search' => $request->get('search'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при поиске товаров'
            ], 500);
        }
    }

    /**
     * Расширенный поиск товаров с фильтрацией по проценту совпадения
     */
    public function advancedSearch(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:2|max:255',
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:50',
                'min_match' => 'integer|min:0|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Поисковый запрос должен содержать минимум 2 символа',
                    'details' => $validator->errors()
                ], 400);
            }

            $search = $request->get('search');
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);
            $minMatch = $request->get('min_match', 70);

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->advancedSearchGoods($search, $page, $limit);

            // Фильтруем по проценту совпадения
            if (is_array($result) && $minMatch > 0) {
                $filtered = array_filter($result, function($item) use ($minMatch) {
                    return isset($item['match_percent']) && $item['match_percent'] >= $minMatch;
                });
                $result = array_values($filtered);
            }

            return response()->json([
                'success' => true,
                'data' => $result ?? [],
                'filter' => [
                    'min_match' => $minMatch,
                    'total_found' => is_array($result) ? count($result) : 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to perform advanced search in Forum-Auto', [
                'short_name' => $shortName,
                'search' => $request->get('search'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при расширенном поиске товаров'
            ], 500);
        }
    }

    /**
     * Получить случайные товары для отображения
     */
    public function getRandomGoods(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot) {
                return response()->json([
                    'success' => false,
                    'error' => 'Бот не найден'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Неверные параметры запроса',
                    'details' => $validator->errors()
                ], 400);
            }

            $limit = $request->get('limit', 12);
            
            // Если API не настроен, возвращаем пустой результат (будут показаны демо-товары в JS)
            if (!$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'API не настроен - демо-режим'
                ]);
            }
            
            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getRandomGoods($limit);

            return response()->json([
                'success' => true,
                'data' => $result ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get random goods in Forum-Auto', [
                'short_name' => $shortName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении случайных товаров'
            ], 500);
        }
    }

    /**
     * Добавить товар в заказ (корзину)
     */
    public function addToCart(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'goods_code' => 'required|string|max:50',
                'quantity' => 'integer|min:1|max:999',
                'comment' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Неверные данные товара',
                    'details' => $validator->errors()
                ], 400);
            }

            $goodsCode = $request->get('goods_code');
            $quantity = $request->get('quantity', 1);
            $comment = $request->get('comment');

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->addGoodsToOrder($goodsCode, $quantity, $comment);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Не удалось добавить товар в заказ'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Товар успешно добавлен в заказ',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to add goods to cart in Forum-Auto', [
                'short_name' => $shortName,
                'goods_code' => $request->get('goods_code'),
                'quantity' => $request->get('quantity'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при добавлении товара в заказ'
            ], 500);
        }
    }

    /**
     * Получить список заказов
     */
    public function getOrders(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 50);

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getOrders($page, $limit);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Не удалось получить список заказов'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get orders from Forum-Auto', [
                'short_name' => $shortName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении заказов'
            ], 500);
        }
    }

    /**
     * Получить информацию о клиенте
     */
    public function getClientInfo(Request $request, string $shortName): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getClientInfo();

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Не удалось получить информацию о клиенте'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get client info from Forum-Auto', [
                'short_name' => $shortName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении информации о клиенте'
            ], 500);
        }
    }

    /**
     * Получить детали товара
     */
    public function getGoodsDetails(Request $request, string $shortName, string $goodsCode): JsonResponse
    {
        try {
            $bot = $this->findBotByShortName($shortName);
            
            if (!$bot || !$bot->hasForumAutoApi()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forum-Auto API не настроен для данного бота'
                ], 404);
            }

            $forumAutoService = new ForumAutoService($bot);
            $result = $forumAutoService->getGoodsDetails($goodsCode);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Товар не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get goods details from Forum-Auto', [
                'short_name' => $shortName,
                'goods_code' => $goodsCode,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при получении деталей товара'
            ], 500);
        }
    }

    /**
     * Найти бота по короткому имени
     */
    private function findBotByShortName(string $shortName): ?TelegramBot
    {
        return TelegramBot::where('mini_app_short_name', $shortName)
            ->where('is_active', true)
            ->first();
    }
}