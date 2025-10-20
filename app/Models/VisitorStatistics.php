<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class VisitorStatistics extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telegram_bot_id',
        'session_id',
        'telegram_chat_id',
        'ip_address',
        'user_agent',
        'referer',
        'page_url',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с Telegram ботом
     */
    public function telegramBot(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class);
    }

    /**
     * Scope для статистики конкретного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для статистики конкретного бота
     */
    public function scopeForBot($query, $botId)
    {
        return $query->where('telegram_bot_id', $botId);
    }

    /**
     * Scope для фильтрации по периоду
     */
    public function scopePeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('visited_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    /**
     * Scope для сегодняшних посещений
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', Carbon::today());
    }

    /**
     * Scope для вчерашних посещений  
     */
    public function scopeYesterday($query)
    {
        return $query->whereDate('visited_at', Carbon::yesterday());
    }

    /**
     * Scope для посещений за последние 7 дней
     */
    public function scopeLast7Days($query)
    {
        return $query->where('visited_at', '>=', Carbon::now()->subDays(7));
    }

    /**
     * Scope для посещений за последние 30 дней
     */
    public function scopeLast30Days($query)
    {
        return $query->where('visited_at', '>=', Carbon::now()->subDays(30));
    }

    /**
     * Scope для текущего месяца
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visited_at', Carbon::now()->month)
                    ->whereYear('visited_at', Carbon::now()->year);
    }

    /**
     * Scope для уникальных посетителей (по session_id)
     */
    public function scopeUniqueVisitors($query)
    {
        return $query->distinct('session_id');
    }

    /**
     * Scope для посетителей из Telegram
     */
    public function scopeTelegramVisitors($query)
    {
        return $query->whereNotNull('telegram_chat_id');
    }

    /**
     * Получить статистику посещений по дням
     */
    public static function getDailyVisitsStats($userId, $botId = null, $startDate = null, $endDate = null)
    {
        $query = static::forUser($userId);
        
        if ($botId) {
            $query->forBot($botId);
        }
        
        if ($startDate && $endDate) {
            $query->period($startDate, $endDate);
        } else {
            $query->last30Days();
        }
        
        return $query->selectRaw('DATE(visited_at) as date, COUNT(*) as total_visits, COUNT(DISTINCT session_id) as unique_visitors')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
    }

    /**
     * Получить статистику посещений по часам
     */
    public static function getHourlyVisitsStats($userId, $botId = null, $date = null)
    {
        $query = static::forUser($userId);
        
        if ($botId) {
            $query->forBot($botId);
        }
        
        if ($date) {
            $query->whereDate('visited_at', $date);
        } else {
            $query->today();
        }
        
        return $query->selectRaw('HOUR(visited_at) as hour, COUNT(*) as visits')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get();
    }

    /**
     * Получить топ популярных страниц
     */
    public static function getTopPages($userId, $botId = null, $startDate = null, $endDate = null, $limit = 10)
    {
        $query = static::forUser($userId);
        
        if ($botId) {
            $query->forBot($botId);
        }
        
        if ($startDate && $endDate) {
            $query->period($startDate, $endDate);
        } else {
            $query->last30Days();
        }
        
        return $query->selectRaw('page_url, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visitors')
                    ->groupBy('page_url')
                    ->orderBy('visits', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Получить статистику источников трафика
     */
    public static function getTrafficSources($userId, $botId = null, $startDate = null, $endDate = null)
    {
        $query = static::forUser($userId);
        
        if ($botId) {
            $query->forBot($botId);
        }
        
        if ($startDate && $endDate) {
            $query->period($startDate, $endDate);
        } else {
            $query->last30Days();
        }
        
        return $query->selectRaw('
                CASE 
                    WHEN telegram_chat_id IS NOT NULL THEN "Telegram"
                    WHEN referer IS NULL OR referer = "" THEN "Прямой переход"
                    WHEN referer LIKE "%google%" THEN "Google"
                    WHEN referer LIKE "%yandex%" THEN "Yandex"
                    WHEN referer LIKE "%vk.com%" THEN "VKontakte"
                    WHEN referer LIKE "%facebook%" THEN "Facebook"
                    WHEN referer LIKE "%instagram%" THEN "Instagram"
                    ELSE "Другие"
                END as source,
                COUNT(*) as visits,
                COUNT(DISTINCT session_id) as unique_visitors
            ')
                    ->groupBy('source')
                    ->orderBy('visits', 'desc')
                    ->get();
    }
}