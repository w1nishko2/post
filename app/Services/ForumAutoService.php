<?php

namespace App\Services;

use App\Models\TelegramBot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ForumAutoService
{
    private const API_BASE_URL = 'https://api.forum-auto.ru/';
    
    protected TelegramBot $bot;
    protected string $login;
    protected string $password;

    /**
     * Создать экземпляр сервиса для конкретного бота
     */
    public function __construct(TelegramBot $bot)
    {
        $this->bot = $bot;
        
        if (!$bot->forum_auto_enabled || !$bot->forum_auto_login || !$bot->forum_auto_pass) {
            throw new \InvalidArgumentException('Forum-Auto API не настроен для данного бота');
        }
        
        $this->login = $bot->forum_auto_login;
        $this->password = decrypt($bot->forum_auto_pass);
    }

    /**
     * Проверить валидность API данных
     */
    public function validateCredentials(): bool
    {
        try {
            $response = $this->makeRequest('clientinfo');
            
            Log::info('Forum-Auto API credentials validation - raw response', [
                'bot_id' => $this->bot->id,
                'status' => $response->status(),
                'body' => $response->body(),
                'body_length' => strlen($response->body()),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $body = trim($response->body());
                
                // Если тело ответа пустое, это может быть ошибкой API
                if (empty($body)) {
                    Log::warning('Forum-Auto API returned empty response on credentials check', [
                        'bot_id' => $this->bot->id,
                        'login' => $this->login
                    ]);
                    return false;
                }

                // Пытаемся декодировать JSON
                $data = null;
                try {
                    $data = json_decode($body, true);
                } catch (\Exception $e) {
                    Log::warning('Forum-Auto API returned non-JSON response', [
                        'bot_id' => $this->bot->id,
                        'body' => $body,
                        'error' => $e->getMessage()
                    ]);
                    // Если это не JSON, но статус успешный и есть контент, 
                    // возможно API работает, но возвращает другой формат
                    return !empty($body);
                }

                Log::info('Forum-Auto API credentials validation result', [
                    'bot_id' => $this->bot->id,
                    'data' => $data,
                    'is_valid' => is_array($data) && !empty($data)
                ]);
                
                return is_array($data) && !empty($data);
            }
            
            Log::warning('Forum-Auto API credentials validation failed - HTTP error', [
                'bot_id' => $this->bot->id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Forum-Auto API credentials validation failed', [
                'bot_id' => $this->bot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Получить информацию о клиенте
     */
    public function getClientInfo(): ?array
    {
        try {
            $cacheKey = "forum_auto_client_info_{$this->bot->id}";
            
            return Cache::remember($cacheKey, 3600, function () {
                $response = $this->makeRequest('clientinfo');
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Проверяем на ошибки API
                    if (isset($data['errors'])) {
                        Log::warning('Forum-Auto API returned error', [
                            'bot_id' => $this->bot->id,
                            'error' => $data['errors']
                        ]);
                        return null;
                    }
                    
                    return $data;
                }
                
                return null;
            });
        } catch (\Exception $e) {
            Log::error('Failed to get Forum-Auto client info', [
                'bot_id' => $this->bot->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Получить список брендов по артикулу
     */
    public function getBrandsByArticle(string $article): ?array
    {
        try {
            $article = trim($article);
            if (empty($article)) {
                return [];
            }

            $cacheKey = "forum_auto_brands_{$this->bot->id}_" . md5($article);
            
            return Cache::remember($cacheKey, 600, function () use ($article) {
                $response = $this->makeRequest('listbrands', ['art' => $article]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Проверяем на ошибки API
                    if (isset($data['errors'])) {
                        Log::warning('Forum-Auto API returned error for brands', [
                            'bot_id' => $this->bot->id,
                            'error' => $data['errors']
                        ]);
                        return [];
                    }
                    
                    return $data ?: [];
                }
                
                return [];
            });
        } catch (\Exception $e) {
            Log::error('Failed to get Forum-Auto brands', [
                'bot_id' => $this->bot->id,
                'article' => $article,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Получить список товаров
     */
    public function getGoods(array $params = []): ?array
    {
        try {
            // API Forum-Auto требует обязательный параметр 'art' для поиска
            if (empty($params['art'])) {
                return []; // Возвращаем пустой массив вместо рекурсивного вызова
            }

            $cacheKey = "forum_auto_goods_{$this->bot->id}_" . md5(serialize($params));
            
            return Cache::remember($cacheKey, 600, function () use ($params) {
                $requestParams = [
                    'art' => $params['art']
                ];
                
                // Добавляем дополнительные параметры если есть
                if (!empty($params['br'])) {
                    $requestParams['br'] = $params['br'];
                }
                if (!empty($params['cross'])) {
                    $requestParams['cross'] = $params['cross'];
                }
                if (!empty($params['gid'])) {
                    $requestParams['gid'] = $params['gid'];
                }

                Log::info('Forum-Auto API request params', [
                    'bot_id' => $this->bot->id,
                    'params' => $requestParams
                ]);

                $response = $this->makeRequest('listgoods', $requestParams);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    Log::info('Forum-Auto API response', [
                        'bot_id' => $this->bot->id,
                        'data_type' => gettype($data),
                        'data_count' => is_array($data) ? count($data) : 0
                    ]);
                    
                    // Проверяем на ошибки API
                    if (isset($data['errors']) || isset($data['error'])) {
                        Log::warning('Forum-Auto API returned error', [
                            'bot_id' => $this->bot->id,
                            'error' => $data['errors'] ?? $data['error'] ?? 'Unknown error'
                        ]);
                        return [];
                    }
                    
                    // API возвращает массив товаров напрямую
                    return is_array($data) ? $data : [];
                }
                
                Log::warning('Forum-Auto API request failed', [
                    'bot_id' => $this->bot->id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [];
            });
        } catch (\Exception $e) {
            Log::error('Failed to get Forum-Auto goods', [
                'bot_id' => $this->bot->id,
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            return [];
        }
    }

    /**
     * Получить популярные товары
     */
    public function getPopularGoods(): array
    {
        try {
            $cacheKey = "forum_auto_popular_goods_{$this->bot->id}";
            
            return Cache::remember($cacheKey, 1800, function () {
                // Список популярных артикулов для демонстрации
                $popularArticles = ['OC47', 'LF787', 'W712', 'OX123D', 'HU7008z'];
                $goods = [];
                
                foreach ($popularArticles as $article) {
                    $result = $this->getGoods(['art' => $article, 'cross' => 1]);
                    if (is_array($result) && !empty($result)) {
                        // Берем только первый товар из результата
                        $goods[] = $result[0];
                    }
                    
                    // Ограничиваем количество товаров
                    if (count($goods) >= 10) {
                        break;
                    }
                }
                
                return $goods;
            });
        } catch (\Exception $e) {
            Log::error('Failed to get popular goods from Forum-Auto', [
                'bot_id' => $this->bot->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Поиск товаров по всем полям с процентом совпадения
     */
    public function advancedSearchGoods(string $search, int $page = 1, int $limit = 20): array
    {
        $search = trim($search);
        if (empty($search) || mb_strlen($search) < 2) {
            return [];
        }

        try {
            $cacheKey = "forum_auto_advanced_search_{$this->bot->id}_" . md5($search . $page . $limit);
            
            return Cache::remember($cacheKey, 300, function () use ($search, $page, $limit) {
                // Получаем товары по артикулу и кроссам
                $goods = $this->getGoods(['art' => $search, 'cross' => 1]);
                if (!is_array($goods)) {
                    $goods = [];
                }

                // Получаем бренды для расширенного поиска
                $brands = $this->getBrandsByArticle($search);
                if (is_array($brands)) {
                    foreach ($brands as $brand) {
                        $brandGoods = $this->getGoods(['art' => $search, 'br' => $brand['brand'], 'cross' => 1]);
                        if (is_array($brandGoods)) {
                            $goods = array_merge($goods, $brandGoods);
                        }
                    }
                }

                // Фильтрация и ранжирование по всем полям
                $unique = [];
                $results = [];
                foreach ($goods as $item) {
                    $key = $item['gid'] ?? ($item['art'] . $item['brand']);
                    if (isset($unique[$key])) continue;
                    $unique[$key] = true;

                    $fields = [$item['art'] ?? '', $item['name'] ?? '', $item['brand'] ?? '', $item['gid'] ?? ''];
                    $maxPercent = 0;
                    foreach ($fields as $field) {
                        $percent = $this->calculateSimilarityPercent($search, $field);
                        if ($percent > $maxPercent) {
                            $maxPercent = $percent;
                        }
                    }
                    $item['match_percent'] = $maxPercent;
                    $results[] = $item;
                }

                // Сортировка по убыванию совпадения
                usort($results, function($a, $b) {
                    return ($b['match_percent'] ?? 0) <=> ($a['match_percent'] ?? 0);
                });

                // Пагинация
                $offset = ($page - 1) * $limit;
                return array_slice($results, $offset, $limit);
            });
        } catch (\Exception $e) {
            Log::error('Failed to perform advanced search in Forum-Auto', [
                'bot_id' => $this->bot->id,
                'search' => $search,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Вычислить процент совпадения двух строк
     */
    private function calculateSimilarityPercent(string $needle, string $haystack): int
    {
        if (empty($needle) || empty($haystack)) return 0;
        similar_text(mb_strtolower($needle), mb_strtolower($haystack), $percent);
        return (int)round($percent);
    }

    /**
     * Найти товары по поисковому запросу (поиск по артикулу)
     */
    public function searchGoods(string $search, int $page = 1, int $limit = 20): ?array
    {
        // Оставляем поисковый запрос как есть, только обрезаем лишние пробелы
        $search = trim($search);
        
        if (empty($search) || mb_strlen($search) < 2) {
            return [];
        }

        try {
            $cacheKey = "forum_auto_search_{$this->bot->id}_" . md5($search);
            
            return Cache::remember($cacheKey, 300, function () use ($search) {
                // Пробуем найти товары с поисковым запросом как артикул
                $result = $this->getGoods([
                    'art' => $search,
                    'cross' => 1 // Включаем поиск аналогов/кроссов
                ]);

                // Если результат пустой или null, возвращаем пустой массив
                if (empty($result) || !is_array($result)) {
                    return [];
                }

                return $result;
            });
        } catch (\Exception $e) {
            Log::error('Failed to search goods in Forum-Auto', [
                'bot_id' => $this->bot->id,
                'search' => $search,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Получить товары по бренду
     */
    public function getGoodsByBrand(int $brandId, int $page = 1, int $limit = 20): ?array
    {
        return $this->getGoods([
            'brand_id' => $brandId,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * Получить список заказов
     */
    public function getOrders(int $page = 1, int $limit = 20): ?array
    {
        try {
            $cacheKey = "forum_auto_orders_{$this->bot->id}_{$page}_{$limit}";
            
            return Cache::remember($cacheKey, 300, function () use ($page, $limit) {
                $response = $this->makeRequest('listorders', [
                    'page' => $page,
                    'limit' => $limit
                ]);
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return null;
            });
        } catch (\Exception $e) {
            Log::error('Failed to get Forum-Auto orders', [
                'bot_id' => $this->bot->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Добавить товар в заказ
     */
    public function addGoodsToOrder(string $goodsCode, int $quantity = 1, ?string $comment = null): ?array
    {
        try {
            $params = [
                'goods_code' => $goodsCode,
                'quantity' => $quantity
            ];
            
            if ($comment) {
                $params['comment'] = $comment;
            }
            
            $response = $this->makeRequest('addgoodstoorder', $params);
            
            if ($response->successful()) {
                // Очищаем кеш заказов после добавления товара
                $this->clearOrdersCache();
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to add goods to Forum-Auto order', [
                'bot_id' => $this->bot->id,
                'goods_code' => $goodsCode,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Получить детали товара по коду
     */
    public function getGoodsDetails(string $goodsCode): ?array
    {
        try {
            $cacheKey = "forum_auto_goods_details_{$this->bot->id}_{$goodsCode}";
            
            return Cache::remember($cacheKey, 3600, function () use ($goodsCode) {
                $response = $this->getGoods(['goods_code' => $goodsCode]);
                
                if ($response && isset($response['goods']) && !empty($response['goods'])) {
                    return $response['goods'][0];
                }
                
                return null;
            });
        } catch (\Exception $e) {
            Log::error('Failed to get Forum-Auto goods details', [
                'bot_id' => $this->bot->id,
                'goods_code' => $goodsCode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Выполнить запрос к API
     */
    private function makeRequest(string $endpoint, array $params = []): \Illuminate\Http\Client\Response
    {
        $url = self::API_BASE_URL . $endpoint;
        
        $params['login'] = $this->login;
        $params['pass'] = $this->password;
        
        Log::info('Making Forum-Auto API request', [
            'bot_id' => $this->bot->id,
            'endpoint' => $endpoint,
            'url' => $url,
            'params' => $params
        ]);

        // Пробуем разные способы отправки запроса
        $response = null;
        $attempts = [
            'GET' => function() use ($url, $params) {
                return Http::timeout(30)
                    ->withoutVerifying()
                    ->retry(2, 500)
                    ->get($url, $params);
            },
            'POST_FORM' => function() use ($url, $params) {
                return Http::timeout(30)
                    ->withoutVerifying()
                    ->retry(2, 500)
                    ->asForm()
                    ->post($url, $params);
            },
            'POST_JSON' => function() use ($url, $params) {
                return Http::timeout(30)
                    ->withoutVerifying()
                    ->retry(2, 500)
                    ->post($url, $params);
            }
        ];

        foreach ($attempts as $method => $makeRequest) {
            try {
                $response = $makeRequest();
                
                Log::info("Forum-Auto API response attempt ($method)", [
                    'bot_id' => $this->bot->id,
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->body(),
                    'body_length' => strlen($response->body())
                ]);

                // Если получили непустой ответ или успешный статус с контентом, используем его
                if (strlen($response->body()) > 0 || ($response->successful() && $response->header('Content-Length') !== '0')) {
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("Forum-Auto API request failed ($method)", [
                    'bot_id' => $this->bot->id,
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        if (!$response) {
            // Если все методы не сработали, делаем последнюю попытку с GET
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get($url, $params);
        }
            
        Log::info('Forum-Auto API final response', [
            'bot_id' => $this->bot->id,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'body_length' => strlen($response->body()),
            'is_json' => $this->isJsonResponse($response),
            'content_type' => $response->header('Content-Type')
        ]);
        
        return $response;
    }

    /**
     * Проверить, является ли ответ JSON
     */
    private function isJsonResponse(\Illuminate\Http\Client\Response $response): bool
    {
        $contentType = $response->header('Content-Type') ?? '';
        return str_contains($contentType, 'application/json') || 
               str_contains($contentType, 'text/json') ||
               $this->isValidJson($response->body());
    }

    /**
     * Проверить, является ли строка валидным JSON
     */
    private function isValidJson(string $string): bool
    {
        if (empty($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Очистить кеш заказов
     */
    private function clearOrdersCache(): void
    {
        $pattern = "forum_auto_orders_{$this->bot->id}_*";
        // В реальном проекте здесь нужно использовать более сложную логику очистки кеша по паттерну
        Cache::flush(); // Временное решение
    }

    /**
     * Очистить весь кеш для бота
     */
    public function clearCache(): void
    {
        Cache::flush(); // В реальном проекте нужно очищать только кеш конкретного бота
    }

    /**
     * Получить статистику использования API
     */
    public function getApiStats(): array
    {
        $clientInfo = $this->getClientInfo();
        
        return [
            'client_info' => $clientInfo,
            'last_check' => $this->bot->forum_auto_last_check,
            'enabled' => $this->bot->forum_auto_enabled,
        ];
    }
}