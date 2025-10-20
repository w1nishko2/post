<?php

namespace App\Http\Controllers;

use App\Models\VisitorStatistics;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'statistics.access']);
    }

    /**
     * Отобразить страницу статистики
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $botId = $request->get('bot_id');
        $period = $request->get('period', 'last_30_days');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Валидация дат для произвольного периода
        if ($period === 'custom') {
            $request->validate([
                'start_date' => 'required|date|before_or_equal:end_date',
                'end_date' => 'required|date|after_or_equal:start_date|before_or_equal:today',
            ], [
                'start_date.required' => 'Укажите дату начала периода',
                'start_date.date' => 'Неверный формат даты начала',
                'start_date.before_or_equal' => 'Дата начала должна быть раньше или равна дате окончания',
                'end_date.required' => 'Укажите дату окончания периода',
                'end_date.date' => 'Неверный формат даты окончания',
                'end_date.after_or_equal' => 'Дата окончания должна быть позже или равна дате начала',
                'end_date.before_or_equal' => 'Дата окончания не может быть позже сегодняшнего дня',
            ]);
        }

        // Получаем боты пользователя
        $userBots = $user->telegramBots()->get();

        // Определяем период для анализа
        $dates = $this->getPeriodDates($period, $startDate, $endDate);

        // Получаем общую статистику
        $generalStats = $this->getGeneralStats($user->id, $botId, $dates['start'], $dates['end']);
        
        // Получаем статистику посещений
        $visitStats = $this->getVisitStats($user->id, $botId, $dates['start'], $dates['end']);
        
        // Получаем статистику товаров
        $productStats = $this->getProductStats($user->id, $botId, $dates['start'], $dates['end']);
        
        // Получаем статистику заказов
        $orderStats = $this->getOrderStats($user->id, $botId, $dates['start'], $dates['end']);
        
        // Получаем время последнего обновления данных
        $lastDataUpdate = $this->getLastDataUpdate($user->id, $botId);

        return view('statistics.index', compact(
            'userBots',
            'botId',
            'period',
            'startDate',
            'endDate',
            'generalStats',
            'visitStats',
            'productStats',
            'orderStats',
            'lastDataUpdate'
        ));
    }

    /**
     * API для получения данных графиков
     */
    public function chartData(Request $request)
    {
        $user = Auth::user();
        $botId = $request->get('bot_id');
        $period = $request->get('period', 'last_30_days');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $chartType = $request->get('chart_type', 'visits');

        // Валидация входных данных
        $request->validate([
            'bot_id' => 'nullable|exists:telegram_bots,id',
            'period' => 'string|in:today,yesterday,last_7_days,last_30_days,this_month,last_month,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'chart_type' => 'string|in:visits,orders,revenue,products'
        ]);

        // Проверяем права доступа к боту
        if ($botId && !$user->telegramBots()->where('id', $botId)->exists()) {
            return response()->json(['error' => 'Unauthorized access to bot data'], 403);
        }

        $dates = $this->getPeriodDates($period, $startDate, $endDate);

        try {
            switch ($chartType) {
                case 'visits':
                    return response()->json($this->getVisitsChartData($user->id, $botId, $dates['start'], $dates['end']));
                
                case 'orders':
                    return response()->json($this->getOrdersChartData($user->id, $botId, $dates['start'], $dates['end']));
                
                case 'revenue':
                    return response()->json($this->getRevenueChartData($user->id, $botId, $dates['start'], $dates['end']));
                
                case 'products':
                    return response()->json($this->getPopularProductsData($user->id, $botId, $dates['start'], $dates['end']));
                
                default:
                    return response()->json(['error' => 'Invalid chart type'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Statistics chart data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'bot_id' => $botId,
                'chart_type' => $chartType
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Получить даты периода
     */
    private function getPeriodDates($period, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay()
                ];
            
            case 'last_7_days':
                return [
                    'start' => $now->copy()->subDays(7)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            
            case 'last_30_days':
                return [
                    'start' => $now->copy()->subDays(30)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            
            case 'this_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            
            case 'custom':
                // Для произвольного периода проверяем, что даты переданы
                if (!$startDate || !$endDate) {
                    // Если даты не указаны, возвращаем последние 30 дней как дефолт
                    return [
                        'start' => $now->copy()->subDays(30)->startOfDay(),
                        'end' => $now->copy()->endOfDay()
                    ];
                }
                
                try {
                    $start = Carbon::parse($startDate)->startOfDay();
                    $end = Carbon::parse($endDate)->endOfDay();
                    
                    // Проверяем, что дата начала не позже даты окончания
                    if ($start->gt($end)) {
                        throw new \InvalidArgumentException('Дата начала не может быть позже даты окончания');
                    }
                    
                    // Проверяем, что даты не в будущем
                    if ($start->gt($now) || $end->gt($now)) {
                        throw new \InvalidArgumentException('Даты не могут быть в будущем');
                    }
                    
                    return [
                        'start' => $start,
                        'end' => $end
                    ];
                } catch (\Exception $e) {
                    // В случае ошибки парсинга возвращаем дефолтный период
                    return [
                        'start' => $now->copy()->subDays(30)->startOfDay(),
                        'end' => $now->copy()->endOfDay()
                    ];
                }
            
            default:
                return [
                    'start' => $now->copy()->subDays(30)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
        }
    }

    /**
     * Получить общую статистику
     */
    private function getGeneralStats($userId, $botId, $startDate, $endDate)
    {
        // Строим запросы для статистики посещений
        $visitorQuery = VisitorStatistics::where('user_id', $userId)
            ->whereBetween('visited_at', [$startDate, $endDate]);
        
        // Строим запросы для заказов
        $orderQuery = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Применяем фильтр по боту, если указан
        if ($botId) {
            $visitorQuery->where('telegram_bot_id', $botId);
            $orderQuery->where('telegram_bot_id', $botId);
        }

        $totalVisits = $visitorQuery->count();
        $uniqueVisitors = $visitorQuery->distinct('session_id')->count();
        $totalOrders = $orderQuery->count();
        $completedOrders = $orderQuery->where('status', Order::STATUS_COMPLETED)->count();
        $totalRevenue = $orderQuery->where('status', Order::STATUS_COMPLETED)->sum('total_amount') ?: 0;

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
     * Получить статистику посещений
     */
    private function getVisitStats($userId, $botId, $startDate, $endDate)
    {
        $dailyVisits = VisitorStatistics::getDailyVisitsStats($userId, $botId, $startDate, $endDate);
        $hourlyVisits = VisitorStatistics::getHourlyVisitsStats($userId, $botId, $startDate->format('Y-m-d'));
        $topPages = VisitorStatistics::getTopPages($userId, $botId, $startDate, $endDate, 10);
        $trafficSources = VisitorStatistics::getTrafficSources($userId, $botId, $startDate, $endDate);
        
        return [
            'daily_visits' => $dailyVisits,
            'hourly_visits' => $hourlyVisits,
            'top_pages' => $topPages,
            'traffic_sources' => $trafficSources
        ];
    }

    /**
     * Получить статистику товаров
     */
    private function getProductStats($userId, $botId, $startDate, $endDate)
    {
        // Базовая статистика товаров пользователя
        $productQuery = Product::where('user_id', $userId);
        if ($botId) {
            $productQuery->where('telegram_bot_id', $botId);
        }

        // Самые популярные товары (по количеству реальных просмотров)
        $popularProducts = collect();
        $productViewsQuery = VisitorStatistics::where('user_id', $userId)
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->where(function($query) {
                $query->where('page_url', 'LIKE', '%/products/%')
                      ->orWhere('page_url', 'LIKE', '%/api/products/%');
            });

        $productViews = $productViewsQuery->get()
            ->groupBy(function($item) {
                // Извлекаем ID товара из URL
                if (preg_match('/\/products\/(\d+)/', $item->page_url, $matches)) {
                    return $matches[1];
                }
                return null;
            })
            ->filter(function($items, $productId) {
                return $productId !== null;
            })
            ->map(function($items, $productId) {
                return [
                    'product_id' => $productId,
                    'views' => $items->count(),
                    'unique_views' => $items->unique('session_id')->count()
                ];
            })
            ->sortByDesc('views')
            ->take(10);

        foreach ($productViews as $viewData) {
            $product = Product::where('id', $viewData['product_id'])
                             ->where('user_id', $userId)
                             ->first();
            if ($product) {
                $popularProducts->push([
                    'product' => $product,
                    'views' => $viewData['views'],
                    'unique_views' => $viewData['unique_views']
                ]);
            }
        }

        // Самые покупаемые товары (реальные данные из завершенных заказов)
        $bestSellingProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.user_id', $userId)
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('orders.telegram_bot_id', $botId);
            })
            ->selectRaw('
                products.id,
                products.name,
                products.price,
                products.photo_url,
                SUM(order_items.quantity) as total_sold,
                COUNT(DISTINCT orders.id) as orders_count,
                SUM(order_items.quantity * order_items.price) as total_revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.price', 'products.photo_url')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return [
            'popular_products' => $popularProducts,
            'best_selling_products' => $bestSellingProducts,
            'total_products' => $productQuery->count(),
            'active_products' => $productQuery->where('is_active', true)->count(),
            'out_of_stock' => $productQuery->where('quantity', '<=', 0)->count()
        ];
    }

    /**
     * Получить статистику заказов
     */
    private function getOrderStats($userId, $botId, $startDate, $endDate)
    {
        $ordersByStatus = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $dailyOrders = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'orders_by_status' => $ordersByStatus,
            'daily_orders' => $dailyOrders,
            'average_processing_time' => $this->getAverageProcessingTime($userId, $botId, $startDate, $endDate)
        ];
    }

    /**
     * Получить данные для графика посещений
     */
    private function getVisitsChartData($userId, $botId, $startDate, $endDate)
    {
        $query = VisitorStatistics::where('user_id', $userId)
            ->whereBetween('visited_at', [$startDate, $endDate]);
            
        if ($botId) {
            $query->where('telegram_bot_id', $botId);
        }
        
        $dailyVisits = $query->selectRaw('DATE(visited_at) as date, COUNT(*) as total_visits, COUNT(DISTINCT session_id) as unique_visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'labels' => $dailyVisits->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d.m');
            })->toArray(),
            'visits' => $dailyVisits->pluck('total_visits')->toArray(),
            'unique_visitors' => $dailyVisits->pluck('unique_visitors')->toArray()
        ];
    }

    /**
     * Получить данные для графика заказов
     */
    private function getOrdersChartData($userId, $botId, $startDate, $endDate)
    {
        $orderQuery = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($botId) {
            $orderQuery->where('telegram_bot_id', $botId);
        }

        $dailyOrders = $orderQuery->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $dailyOrders->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d.m');
            })->toArray(),
            'orders' => $dailyOrders->pluck('count')->toArray()
        ];
    }

    /**
     * Получить данные для графика выручки
     */
    private function getRevenueChartData($userId, $botId, $startDate, $endDate)
    {
        $orderQuery = Order::where('user_id', $userId)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($botId) {
            $orderQuery->where('telegram_bot_id', $botId);
        }

        $dailyRevenue = $orderQuery->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $dailyRevenue->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d.m');
            })->toArray(),
            'revenue' => $dailyRevenue->pluck('revenue')->toArray()
        ];
    }

    /**
     * Получить данные популярных товаров
     */
    private function getPopularProductsData($userId, $botId, $startDate, $endDate)
    {
        // Получаем популярные товары из реальных данных просмотров
        $productViews = VisitorStatistics::where('user_id', $userId)
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->where(function($query) {
                $query->where('page_url', 'LIKE', '%/products/%')
                      ->orWhere('page_url', 'LIKE', '%/api/products/%');
            })
            ->get()
            ->groupBy(function($item) {
                // Извлекаем ID товара из URL
                if (preg_match('/\/products\/(\d+)/', $item->page_url, $matches)) {
                    return $matches[1];
                }
                return null;
            })
            ->filter(function($items, $productId) {
                return $productId !== null;
            })
            ->map(function($items) {
                return $items->count();
            })
            ->sortDesc()
            ->take(10);

        $productNames = [];
        $views = [];

        foreach ($productViews as $productId => $viewCount) {
            $product = Product::where('id', $productId)
                             ->where('user_id', $userId)
                             ->first();
            if ($product) {
                $productNames[] = mb_substr($product->name, 0, 20) . (mb_strlen($product->name) > 20 ? '...' : '');
                $views[] = $viewCount;
            }
        }

        return [
            'labels' => $productNames,
            'views' => $views
        ];
    }

    /**
     * Получить среднее время обработки заказов
     */
    private function getAverageProcessingTime($userId, $botId, $startDate, $endDate)
    {
        $completedOrders = Order::where('user_id', $userId)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->selectRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) as processing_hours')
            ->get()
            ->filter(function($order) {
                return $order->processing_hours > 0;
            });

        if ($completedOrders->isEmpty()) {
            return 0;
        }

        return round($completedOrders->avg('processing_hours'), 1);
    }

    /**
     * Получить время последнего обновления данных
     */
    private function getLastDataUpdate($userId, $botId = null)
    {
        $latestVisit = VisitorStatistics::where('user_id', $userId)
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->latest('visited_at')
            ->first();

        $latestOrder = Order::where('user_id', $userId)
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            })
            ->latest('created_at')
            ->first();

        $latestVisitTime = $latestVisit ? $latestVisit->visited_at : null;
        $latestOrderTime = $latestOrder ? $latestOrder->created_at : null;

        if ($latestVisitTime && $latestOrderTime) {
            return $latestVisitTime->gt($latestOrderTime) ? $latestVisitTime : $latestOrderTime;
        }

        return $latestVisitTime ?: $latestOrderTime;
    }

    /**
     * Генерация полного отчета
     */
    public function generateFullReport(Request $request)
    {
        $user = Auth::user();
        $botId = $request->get('bot_id');
        $period = $request->get('period', 'last_30_days');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Валидация входных данных
        $request->validate([
            'bot_id' => 'nullable|exists:telegram_bots,id',
            'period' => 'string|in:today,yesterday,last_7_days,last_30_days,this_month,last_month,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Проверяем права доступа к боту
        if ($botId && !$user->telegramBots()->where('id', $botId)->exists()) {
            return redirect()->route('statistics.index')->with('error', 'У вас нет доступа к статистике этого бота.');
        }

        // Получаем данные о боте
        $bot = $botId ? $user->telegramBots()->find($botId) : null;
        
        // Определяем период для анализа
        $dates = $this->getPeriodDates($period, $startDate, $endDate);

        // Собираем все данные для отчета
        $generalStats = $this->getGeneralStats($user->id, $botId, $dates['start'], $dates['end']);
        $visitStats = $this->getVisitStats($user->id, $botId, $dates['start'], $dates['end']);
        $productStats = $this->getProductStats($user->id, $botId, $dates['start'], $dates['end']);
        $orderStats = $this->getOrderStats($user->id, $botId, $dates['start'], $dates['end']);
        
        // Получаем детальные данные для отчета
        $detailedVisits = $this->getDetailedVisitsData($user->id, $botId, $dates['start'], $dates['end']);
        $detailedOrders = $this->getDetailedOrdersData($user->id, $botId, $dates['start'], $dates['end']);
        
        // Формируем название файла
        $fileName = 'statistics_report_' . $dates['start']->format('Y-m-d') . '_to_' . $dates['end']->format('Y-m-d');
        if ($bot) {
            $fileName .= '_bot_' . $bot->name;
        }
        $fileName .= '.pdf';

        // Генерируем PDF отчет
        $pdf = Pdf::loadView('statistics.report', compact(
            'user',
            'bot',
            'period',
            'dates',
            'generalStats',
            'visitStats',
            'productStats',
            'orderStats',
            'detailedVisits',
            'detailedOrders'
        ));

        return $pdf->download($fileName);
    }

    /**
     * Получить детальные данные посещений для отчета
     */
    private function getDetailedVisitsData($userId, $botId, $startDate, $endDate)
    {
        $query = VisitorStatistics::where('user_id', $userId)
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            });

        return [
            'daily_breakdown' => $query->clone()
                ->selectRaw('DATE(visited_at) as date, COUNT(*) as total_visits, COUNT(DISTINCT session_id) as unique_visitors')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'hourly_distribution' => $query->clone()
                ->selectRaw('HOUR(visited_at) as hour, COUNT(*) as visits')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
            
            'top_referrers' => $query->clone()
                ->selectRaw('referer, COUNT(*) as visits')
                ->whereNotNull('referer')
                ->groupBy('referer')
                ->orderBy('visits', 'desc')
                ->limit(10)
                ->get(),
            
            'device_stats' => $query->clone()
                ->selectRaw('
                    CASE 
                        WHEN user_agent LIKE "%Mobile%" THEN "Mobile"
                        WHEN user_agent LIKE "%Tablet%" THEN "Tablet"
                        ELSE "Desktop"
                    END as device_type,
                    COUNT(*) as visits
                ')
                ->groupBy('device_type')
                ->get()
        ];
    }

    /**
     * Получить детальные данные заказов для отчета
     */
    private function getDetailedOrdersData($userId, $botId, $startDate, $endDate)
    {
        $query = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($botId, function($query) use ($botId) {
                return $query->where('telegram_bot_id', $botId);
            });

        return [
            'status_breakdown' => $query->clone()
                ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total_amount')
                ->groupBy('status')
                ->get(),
            
            'daily_revenue' => $query->clone()
                ->where('status', Order::STATUS_COMPLETED)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'top_customers' => $query->clone()
                ->where('status', Order::STATUS_COMPLETED)
                ->selectRaw('customer_name, COUNT(*) as orders_count, SUM(total_amount) as total_spent')
                ->whereNotNull('customer_name')
                ->groupBy('customer_name')
                ->orderBy('total_spent', 'desc')
                ->limit(10)
                ->get(),
            
            'recent_orders' => $query->clone()
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
        ];
    }
}