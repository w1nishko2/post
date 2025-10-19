<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    public const MOSCOW_TIMEZONE = 'Europe/Moscow';
    
    /**
     * Получить текущее время в московском часовом поясе
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::MOSCOW_TIMEZONE);
    }
    
    /**
     * Создать Carbon из строки в московском часовом поясе
     */
    public static function parse(string $time): Carbon
    {
        return Carbon::parse($time, self::MOSCOW_TIMEZONE);
    }
    
    /**
     * Преобразовать UTC время в московское
     */
    public static function fromUtc(Carbon $utcTime): Carbon
    {
        return $utcTime->setTimezone(self::MOSCOW_TIMEZONE);
    }
    
    /**
     * Получить начало дня в московском времени
     */
    public static function startOfDay(?Carbon $date = null): Carbon
    {
        $date = $date ?: self::now();
        return $date->copy()->setTimezone(self::MOSCOW_TIMEZONE)->startOfDay();
    }
    
    /**
     * Получить конец дня в московском времени
     */
    public static function endOfDay(?Carbon $date = null): Carbon
    {
        $date = $date ?: self::now();
        return $date->copy()->setTimezone(self::MOSCOW_TIMEZONE)->endOfDay();
    }
    
    /**
     * Получить начало недели в московском времени
     */
    public static function startOfWeek(?Carbon $date = null): Carbon
    {
        $date = $date ?: self::now();
        return $date->copy()->setTimezone(self::MOSCOW_TIMEZONE)->startOfWeek();
    }
    
    /**
     * Получить конец недели в московском времени
     */
    public static function endOfWeek(?Carbon $date = null): Carbon
    {
        $date = $date ?: self::now();
        return $date->copy()->setTimezone(self::MOSCOW_TIMEZONE)->endOfWeek();
    }
    
    /**
     * Получить начало месяца в московском времени
     */
    public static function startOfMonth(?Carbon $date = null): Carbon
    {
        $date = $date ?: self::now();
        return $date->copy()->setTimezone(self::MOSCOW_TIMEZONE)->startOfMonth();
    }
    
    /**
     * Получить конец месяца в московском времени
     */
    public static function endOfMonth(?Carbon $date = null): Carbon
    {
        $date = $date ?: self::now();
        return $date->copy()->setTimezone(self::MOSCOW_TIMEZONE)->endOfMonth();
    }
    
    /**
     * Форматировать дату в московском времени
     */
    public static function format(Carbon $date, string $format = 'd.m.Y H:i:s'): string
    {
        return $date->setTimezone(self::MOSCOW_TIMEZONE)->format($format);
    }
    
    /**
     * Получить относительное время (например, "2 часа назад")
     */
    public static function diffForHumans(Carbon $date): string
    {
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        $now = self::now();
        
        return $moscowDate->diffForHumans($now);
    }
    
    /**
     * Проверить, является ли дата сегодняшней по московскому времени
     */
    public static function isToday(Carbon $date): bool
    {
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        $today = self::now();
        
        return $moscowDate->isSameDay($today);
    }
    
    /**
     * Проверить, является ли дата в пределах этой недели по московскому времени
     */
    public static function isThisWeek(Carbon $date): bool
    {
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        $thisWeek = self::now();
        
        return $moscowDate->isSameWeek($thisWeek);
    }
    
    /**
     * Проверить, является ли дата в пределах этого месяца по московскому времени
     */
    public static function isThisMonth(Carbon $date): bool
    {
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        $thisMonth = self::now();
        
        return $moscowDate->isSameMonth($thisMonth);
    }
    
    /**
     * Добавить время к текущему московскому времени
     */
    public static function addTime(string $timeString): Carbon
    {
        return self::now()->add($timeString);
    }
    
    /**
     * Вычесть время из текущего московского времени
     */
    public static function subTime(string $timeString): Carbon
    {
        return self::now()->sub($timeString);
    }
    
    /**
     * Создать временную метку для заказа (истечение через 5 часов)
     */
    public static function createOrderExpiration(): Carbon
    {
        return self::now()->addHours(5);
    }
    
    /**
     * Получить локализованное название дня недели на русском
     */
    public static function getDayName(Carbon $date): string
    {
        $days = [
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среда',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота',
            7 => 'воскресенье'
        ];
        
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        return $days[$moscowDate->dayOfWeek];
    }
    
    /**
     * Получить локализованное название месяца на русском
     */
    public static function getMonthName(Carbon $date): string
    {
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        return $months[$moscowDate->month];
    }
    
    /**
     * Получить красивую локализованную дату
     */
    public static function getBeautifulDate(Carbon $date): string
    {
        $moscowDate = $date->setTimezone(self::MOSCOW_TIMEZONE);
        
        $day = $moscowDate->day;
        $month = self::getMonthName($moscowDate);
        $year = $moscowDate->year;
        $time = $moscowDate->format('H:i');
        
        return "{$day} {$month} {$year} в {$time}";
    }
}