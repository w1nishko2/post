<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

abstract class BaseModel extends Model
{
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Устанавливаем московский часовой пояс для всех новых моделей
        static::creating(function ($model) {
            $model->setCreatedAtToMoscowTime();
        });
        
        static::updating(function ($model) {
            $model->setUpdatedAtToMoscowTime();
        });
    }

    /**
     * Установить время создания в московском часовом поясе
     */
    private function setCreatedAtToMoscowTime()
    {
        if ($this->usesTimestamps() && !$this->exists) {
            $now = Carbon::now('Europe/Moscow');
            $this->{$this->getCreatedAtColumn()} = $now;
        }
    }

    /**
     * Установить время обновления в московском часовом поясе
     */
    private function setUpdatedAtToMoscowTime()
    {
        if ($this->usesTimestamps()) {
            $now = Carbon::now('Europe/Moscow');
            $this->{$this->getUpdatedAtColumn()} = $now;
        }
    }

    /**
     * Получить атрибут created_at в московском времени
     */
    public function getCreatedAtMoscowAttribute()
    {
        if ($this->created_at) {
            return $this->created_at->setTimezone('Europe/Moscow');
        }
        return null;
    }

    /**
     * Получить атрибут updated_at в московском времени
     */
    public function getUpdatedAtMoscowAttribute()
    {
        if ($this->updated_at) {
            return $this->updated_at->setTimezone('Europe/Moscow');
        }
        return null;
    }

    /**
     * Получить форматированное время создания в московском времени
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        if ($this->created_at) {
            return $this->created_at->setTimezone('Europe/Moscow')->format('d.m.Y H:i:s');
        }
        return '-';
    }

    /**
     * Получить форматированное время обновления в московском времени
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        if ($this->updated_at) {
            return $this->updated_at->setTimezone('Europe/Moscow')->format('d.m.Y H:i:s');
        }
        return '-';
    }

    /**
     * Получить локализованную дату создания в московском времени
     */
    public function getLocalizedCreatedAtAttribute(): string
    {
        if ($this->created_at) {
            $date = $this->created_at->setTimezone('Europe/Moscow');
            $months = [
                1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
                5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
                9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
            ];
            
            $day = $date->format('j');
            $month = $months[(int) $date->format('n')];
            $year = $date->format('Y');
            $time = $date->format('H:i');
            
            return "{$day} {$month} {$year} в {$time}";
        }
        return '-';
    }

    /**
     * Scope для фильтрации по дате создания в московском времени
     */
    public function scopeCreatedInMoscowBetween($query, $startDate, $endDate)
    {
        // Преобразуем даты в UTC для корректного поиска в базе
        $startUtc = Carbon::parse($startDate, 'Europe/Moscow')->utc();
        $endUtc = Carbon::parse($endDate, 'Europe/Moscow')->utc();
        
        return $query->whereBetween('created_at', [$startUtc, $endUtc]);
    }

    /**
     * Scope для фильтрации по дате создания (только дата, без времени) в московском времени
     */
    public function scopeCreatedOnMoscowDate($query, $date)
    {
        $moscowDate = Carbon::parse($date, 'Europe/Moscow');
        $startUtc = $moscowDate->copy()->startOfDay()->utc();
        $endUtc = $moscowDate->copy()->endOfDay()->utc();
        
        return $query->whereBetween('created_at', [$startUtc, $endUtc]);
    }

    /**
     * Scope для фильтрации записей, созданных сегодня по московскому времени
     */
    public function scopeCreatedTodayMoscow($query)
    {
        $today = Carbon::now('Europe/Moscow')->startOfDay();
        $startUtc = $today->copy()->utc();
        $endUtc = $today->copy()->endOfDay()->utc();
        
        return $query->whereBetween('created_at', [$startUtc, $endUtc]);
    }

    /**
     * Scope для фильтрации записей, созданных на этой неделе по московскому времени
     */
    public function scopeCreatedThisWeekMoscow($query)
    {
        $startOfWeek = Carbon::now('Europe/Moscow')->startOfWeek();
        $endOfWeek = Carbon::now('Europe/Moscow')->endOfWeek();
        
        return $query->createdInMoscowBetween($startOfWeek, $endOfWeek);
    }

    /**
     * Scope для фильтрации записей, созданных в этом месяце по московскому времени
     */
    public function scopeCreatedThisMonthMoscow($query)
    {
        $startOfMonth = Carbon::now('Europe/Moscow')->startOfMonth();
        $endOfMonth = Carbon::now('Europe/Moscow')->endOfMonth();
        
        return $query->createdInMoscowBetween($startOfMonth, $endOfMonth);
    }
}