<?php

namespace App\Services;

use App\Models\TelegramBot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ForumAutoService
{
    private const API_BASE_URL = 'https://api.forum-auto.ru/v2/';
    
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
        $this->password = $bot->forum_auto_pass;
    }

    /**
     * Проверить валидность API данных
     */
    public function validateCredentials(): bool
    {
        try {
            $response = $this->makeRequest('clientinfo');
            
            if ($response->successful()) {
                $data = $response->json();
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
                'error' => $e->getMessage()
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
                    try {
                        $result = $this->getGoods(['art' => $article, 'cross' => 1]);
                        if (is_array($result) && !empty($result)) {
                            // Берем только первый товар из результата
                            $goods[] = $result[0];
                        }
                    } catch (\Exception $e) {
                        Log::debug('Failed to get goods for article', [
                            'article' => $article,
                            'error' => $e->getMessage()
                        ]);
                        continue;
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
            
            // Возвращаем пустой массив в случае ошибки
            return [];
        }
    }

    /**
     * Получить случайные товары для отображения
     */
    public function getRandomGoods(int $limit = 12): array
    {
        try {
            $cacheKey = "forum_auto_random_{$this->bot->id}_{$limit}";
            
            return Cache::remember($cacheKey, 300, function () use ($limit) {
                // Используем различные поисковые термины для получения разнообразных товаров
                $searchTerms = [
                    // Популярные артикулы
                    'OC47', 'W712', 'LF787', 'OX123D', 'HU7008z',
                    'FK0179', 'P550', 'W920', 'OE674', 'LX815',
                    // Короткие термины для широкого поиска
                    'filter', 'oil', 'brake', 'belt', 'pump'
                ];
                
                $allGoods = [];
                $attemptCount = 0;
                $maxAttempts = min(10, count($searchTerms));
                
                // Перемешиваем термины для случайности
                shuffle($searchTerms);
                
                foreach (array_slice($searchTerms, 0, $maxAttempts) as $term) {
                    try {
                        $goods = $this->getGoods(['art' => $term, 'cross' => 1]);
                        if (is_array($goods) && count($goods) > 0) {
                            // Берем до 3 товаров из результата
                            $randomGoods = array_slice($goods, 0, min(3, count($goods)));
                            $allGoods = array_merge($allGoods, $randomGoods);
                        }
                        
                        $attemptCount++;
                        if (count($allGoods) >= $limit * 1.5) break; // Набираем с небольшим запасом
                    } catch (\Exception $e) {
                        Log::debug('Search term failed during random goods generation', [
                            'bot_id' => $this->bot->id,
                            'term' => $term,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
                
                // Если ничего не нашли, возвращаем пустой массив
                if (empty($allGoods)) {
                    return [];
                }
                
                // Удаляем дубликаты
                $unique = [];
                foreach ($allGoods as $item) {
                    $key = $item['gid'] ?? ($item['art'] . '_' . $item['brand']);
                    if (!isset($unique[$key])) {
                        $unique[$key] = $item;
                    }
                }
                
                $uniqueGoods = array_values($unique);
                
                // Перемешиваем и берем нужное количество
                shuffle($uniqueGoods);
                return array_slice($uniqueGoods, 0, $limit);
            });
        } catch (\Exception $e) {
            Log::error('Failed to get random goods from Forum-Auto', [
                'bot_id' => $this->bot->id,
                'limit' => $limit,
                'error' => $e->getMessage()
            ]);
            
            // Возвращаем пустой массив в случае ошибки
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
                $allGoods = [];
                
                // 1. Прямой поиск по артикулу с кроссами
                $directGoods = $this->getGoods(['art' => $search, 'cross' => 1]);
                if (is_array($directGoods)) {
                    $allGoods = array_merge($allGoods, $directGoods);
                }
                
                // 2. Поиск по брендам для данного артикула
                $brands = $this->getBrandsByArticle($search);
                if (is_array($brands)) {
                    foreach ($brands as $brand) {
                        $brandGoods = $this->getGoods(['art' => $search, 'br' => $brand['brand'], 'cross' => 1]);
                        if (is_array($brandGoods)) {
                            $allGoods = array_merge($allGoods, $brandGoods);
                        }
                    }
                }
                
                // 3. Дополнительный поиск по частичным совпадениям
                if (mb_strlen($search) >= 3) {
                    // Поиск по частичным артикулам (если поиск содержит более 3 символов)
                    $partialSearches = [
                        mb_substr($search, 0, 3),  // Первые 3 символа
                        mb_substr($search, 0, 4),  // Первые 4 символа
                        mb_substr($search, -3),    // Последние 3 символа
                    ];
                    
                    foreach ($partialSearches as $partial) {
                        if (mb_strlen($partial) >= 2) {
                            $partialGoods = $this->getGoods(['art' => $partial, 'cross' => 1]);
                            if (is_array($partialGoods)) {
                                $allGoods = array_merge($allGoods, $partialGoods);
                            }
                        }
                    }
                }
                
                // 4. Поиск по ключевым словам и типам товаров
                $searchKeywords = $this->extractKeywordsFromSearch($search);
                foreach ($searchKeywords as $keyword) {
                    // Поиск популярных артикулов, содержащих ключевое слово
                    $keywordGoods = $this->searchByKeyword($keyword);
                    if (is_array($keywordGoods)) {
                        $allGoods = array_merge($allGoods, $keywordGoods);
                    }
                }
                
                // 5. Поиск по популярным брендам если поиск похож на название товара
                $popularBrands = ['BOSCH', 'MANN', 'MAHLE', 'KNECHT', 'FILTRON', 'JP GROUP', 'FEBI'];
                foreach ($popularBrands as $brandName) {
                    if ($this->calculateSimilarityPercent($search, $brandName) >= 60) {
                        // Если поиск похож на название бренда, ищем популярные товары этого бренда
                        $brandSearch = ['br' => $brandName];
                        $brandGoods = $this->getGoods($brandSearch);
                        if (is_array($brandGoods)) {
                            $allGoods = array_merge($allGoods, array_slice($brandGoods, 0, 10));
                        }
                    }
                }

                // Удаление дубликатов и вычисление процента совпадения
                $unique = [];
                $results = [];
                
                foreach ($allGoods as $item) {
                    $key = $item['gid'] ?? ($item['art'] . '_' . $item['brand']);
                    if (isset($unique[$key])) continue;
                    $unique[$key] = true;

                    // Рассчитываем максимальный процент совпадения по всем полям
                    $fields = [
                        'art' => $item['art'] ?? '',
                        'name' => $item['name'] ?? '',
                        'brand' => $item['brand'] ?? '',
                        'gid' => $item['gid'] ?? ''
                    ];
                    
                    $maxPercent = 0;
                    $matchingField = '';
                    
                    foreach ($fields as $fieldName => $fieldValue) {
                        if (empty($fieldValue)) continue;
                        
                        // Точное совпадение
                        if (mb_strtolower($search) === mb_strtolower($fieldValue)) {
                            $maxPercent = 100;
                            $matchingField = $fieldName;
                            break;
                        }
                        
                        // Содержит поисковой запрос
                        if (mb_stripos($fieldValue, $search) !== false) {
                            $percent = max(85, $this->calculateSimilarityPercent($search, $fieldValue));
                            if ($percent > $maxPercent) {
                                $maxPercent = $percent;
                                $matchingField = $fieldName;
                            }
                        }
                        
                        // Похожесть строк
                        $percent = $this->calculateSimilarityPercent($search, $fieldValue);
                        if ($percent > $maxPercent) {
                            $maxPercent = $percent;
                            $matchingField = $fieldName;
                        }
                    }
                    
                    $item['match_percent'] = $maxPercent;
                    $item['matching_field'] = $matchingField;
                    
                    // Фильтруем только товары с совпадением 70% и выше
                    if ($maxPercent >= 70) {
                        $results[] = $item;
                    }
                }

                // Сортировка по убыванию совпадения
                usort($results, function($a, $b) {
                    $aPercent = $a['match_percent'] ?? 0;
                    $bPercent = $b['match_percent'] ?? 0;
                    
                    if ($aPercent === $bPercent) {
                        // При одинаковом проценте сортируем по наличию товара
                        $aStock = $a['num'] ?? 0;
                        $bStock = $b['num'] ?? 0;
                        return $bStock <=> $aStock;
                    }
                    
                    return $bPercent <=> $aPercent;
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
        
        $needle = mb_strtolower(trim($needle));
        $haystack = mb_strtolower(trim($haystack));
        
        // Точное совпадение
        if ($needle === $haystack) return 100;
        
        // Один содержит другой
        if (mb_strpos($haystack, $needle) !== false || mb_strpos($needle, $haystack) !== false) {
            $percent = max(80, (mb_strlen($needle) / mb_strlen($haystack)) * 100);
            return min(95, (int)round($percent));
        }
        
        // Используем similar_text для подсчета схожести
        similar_text($needle, $haystack, $percent);
        
        // Дополнительная проверка на схожие начала/концы
        $needleLen = mb_strlen($needle);
        $haystackLen = mb_strlen($haystack);
        $minLen = min($needleLen, $haystackLen);
        
        if ($minLen >= 2) {
            // Проверяем совпадение начала
            $startMatch = 0;
            for ($i = 0; $i < $minLen; $i++) {
                if (mb_substr($needle, $i, 1) === mb_substr($haystack, $i, 1)) {
                    $startMatch++;
                } else {
                    break;
                }
            }
            
            // Проверяем совпадение конца
            $endMatch = 0;
            for ($i = 1; $i <= $minLen; $i++) {
                if (mb_substr($needle, -$i, 1) === mb_substr($haystack, -$i, 1)) {
                    $endMatch++;
                } else {
                    break;
                }
            }
            
            $positionBonus = (($startMatch + $endMatch) / $minLen) * 20;
            $percent += $positionBonus;
        }
        
        return (int)round(min(99, $percent));
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
        
        $response = Http::timeout(30)
            ->withoutVerifying() // Игнорируем SSL сертификаты
            ->retry(3, 1000)
            ->get($url, $params);
            
        Log::info('Forum-Auto API raw response', [
            'bot_id' => $this->bot->id,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'body_length' => strlen($response->body())
        ]);
        
        return $response;
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

    /**
     * Извлечь ключевые слова для поиска по названию товара
     */
    private function extractKeywordsFromSearch(string $search): array
    {
        $search = mb_strtolower(trim($search));
        
        // Словарь соответствий для поиска товаров
        $keywords = [
            // Фильтры
            'фильтр' => ['OC', 'LF', 'W7', 'HU', 'P55', 'WK'],
            'масляный' => ['OC', 'W7', 'LF'],
            'воздушный' => ['LX', 'C14', 'SB'],
            'топливный' => ['WK', 'KL', 'P55'],
            'салонный' => ['LAK', 'CU', 'AH'],
            
            // Тормоза
            'колодки' => ['GDB', 'P85', 'FDB', '0986'],
            'тормозные' => ['GDB', 'P85', 'FDB', '0986'],
            'диск' => ['DF', 'BD', '09'],
            
            // Свечи
            'свеча' => ['BKR', 'FR', 'NGK', 'DENSO'],
            'зажигания' => ['BKR', 'FR', 'NGK'],
            
            // Масла и жидкости
            'масло' => ['5W30', '5W40', '0W20', '10W40'],
            'моторное' => ['5W30', '5W40', '0W20'],
            'антифриз' => ['G11', 'G12', 'G13'],
            
            // Запчасти двигателя
            'поршень' => ['STD', '+0.25', '+0.50'],
            'кольца' => ['STD', '+0.25', '+0.50'],
            'прокладка' => ['ELRING', 'AJUSA'],
            'ремень' => ['6PK', '5PK', '4PK'],
            
            // Подвеска
            'амортизатор' => ['B4', 'B6', 'B8', 'KYB'],
            'стойка' => ['B4', 'B6', 'G', 'KYB'],
            'сайлентблок' => ['LEMFORDER', 'FEBI'],
            
            // Электрика
            'лампа' => ['H1', 'H4', 'H7', 'H11'],
            'лампочка' => ['H1', 'H4', 'H7', 'H11'],
            'предохранитель' => ['MAXI', 'MINI'],
        ];
        
        $foundKeywords = [];
        
        // Ищем прямые совпадения ключевых слов
        foreach ($keywords as $keyword => $articles) {
            if (mb_strpos($search, $keyword) !== false) {
                $foundKeywords = array_merge($foundKeywords, $articles);
            }
        }
        
        // Если не нашли ключевых слов, пробуем разбить поиск на слова
        if (empty($foundKeywords)) {
            $words = explode(' ', $search);
            foreach ($words as $word) {
                $word = trim($word);
                if (mb_strlen($word) >= 3) {
                    foreach ($keywords as $keyword => $articles) {
                        if ($this->calculateSimilarityPercent($word, $keyword) >= 70) {
                            $foundKeywords = array_merge($foundKeywords, $articles);
                        }
                    }
                }
            }
        }
        
        return array_unique($foundKeywords);
    }

    /**
     * Поиск товаров по ключевому слову (артикулу или части артикула)
     */
    private function searchByKeyword(string $keyword): array
    {
        try {
            // Поиск по прямому совпадению
            $directGoods = $this->getGoods(['art' => $keyword, 'cross' => 1]);
            if (is_array($directGoods) && count($directGoods) > 0) {
                return $directGoods;
            }
            
            // Поиск по частичному совпадению - добавляем популярные окончания
            $variations = [
                $keyword,
                $keyword . '1',
                $keyword . '2',
                $keyword . '0',
                $keyword . 'E',
                $keyword . 'Z',
            ];
            
            $allResults = [];
            foreach ($variations as $variation) {
                try {
                    $goods = $this->getGoods(['art' => $variation, 'cross' => 1]);
                    if (is_array($goods)) {
                        $allResults = array_merge($allResults, array_slice($goods, 0, 3)); // Берем первые 3 результата
                    }
                } catch (\Exception $e) {
                    continue;
                }
                
                // Ограничиваем общее количество результатов
                if (count($allResults) >= 15) {
                    break;
                }
            }
            
            return $allResults;
        } catch (\Exception $e) {
            Log::debug('Keyword search failed', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}