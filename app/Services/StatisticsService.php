<?php

namespace App\Services;

use App\Models\VisitorStatistics;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    private const CACHE_TTL = 300; // 5 минут кэша для статистики

    /**
     * Получить кэшированную общую статистику
     */
    public function getCachedGeneralStats($userId, $botId, $startDate, $endDate)
    {
        $cacheKey = "stats:general:{$userId}:{$botId}:" . $startDate->format('Y-m-d') . ":" . $endDate->format('Y-m-d');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $botId, $startDate, $endDate) {
            return $this->getGeneralStats($userId, $botId, $startDate, $endDate);
        });
    }

    /**
     * Получить общую статистику (без кэша)
     */
    private function getGeneralStats($userId, $botId, $startDate, $endDate)
    {
        // Базовые запросы с оптимизацией
        $visitorQuery = VisitorStatistics::where('user_id', $userId)
            ->whereBetween('visited_at', [$startDate, $endDate]);
        
        $orderQuery = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($botId) {
            $visitorQuery->where('telegram_bot_id', $botId);
            $orderQuery->where('telegram_bot_id', $botId);
        }

        // Используем один запрос для нескольких метрик
        $visitorStats = DB::table('visitor_statistics')
            ->where('user_id', $userId)
            ->when($botId, fn($q) => $q->where('telegram_bot_id', $botId))
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_visits,
                COUNT(DISTINCT session_id) as unique_visitors
            ')
            ->first();

        $orderStats = DB::table('orders')
            ->where('user_id', $userId)
            ->when($botId, fn($q) => $q->where('telegram_bot_id', $botId))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = ? THEN 1 END) as completed_orders,
                COALESCE(SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END), 0) as total_revenue
            ', [Order::STATUS_COMPLETED, Order::STATUS_COMPLETED])
            ->first();

        $totalVisits = $visitorStats->total_visits ?? 0;
        $uniqueVisitors = $visitorStats->unique_visitors ?? 0;
        $totalOrders = $orderStats->total_orders ?? 0;
        $completedOrders = $orderStats->completed_orders ?? 0;
        $totalRevenue = $orderStats->total_revenue ?? 0;

        $conversionRate = $uniqueVisitors > 0 ? ($completedOrders / $uniqueVisitors) * 100 : 0;
        $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

        return [
            'total_visits' => $totalVisits,
            'unique_visitors' => $uniqueVisitors,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'total_revenue' => $totalRevenue,
            'conversion_rate' => round($conversionRate, 2),
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }

    /**
     * Очистить кэш статистики пользователя
     */
    public function clearUserStatisticsCache($userId)
    {
        $pattern = "stats:*:{$userId}:*";
        
        // В production используйте Redis для более эффективной очистки по паттерну
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
    }

    /**
     * Получить метрики производительности
     */
    public function getPerformanceMetrics($userId, $botId = null)
    {
        $startTime = microtime(true);
        
        // Проверяем скорость основных запросов
        $visitorCount = VisitorStatistics::where('user_id', $userId)
            ->when($botId, fn($q) => $q->where('telegram_bot_id', $botId))
            ->count();
        
        $queryTime = microtime(true) - $startTime;
        
        return [
            'visitor_count' => $visitorCount,
            'query_time_ms' => round($queryTime * 1000, 2),
            'cache_status' => config('cache.default'),
            'database_connection' => config('database.default')
        ];
    }
}