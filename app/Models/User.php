<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'color_scheme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Связь с Telegram ботами пользователя
     */
    public function telegramBots()
    {
        return $this->hasMany(\App\Models\TelegramBot::class);
    }

    /**
     * Получить активные Telegram боты пользователя
     */
    public function activeTelegramBots()
    {
        return $this->hasMany(\App\Models\TelegramBot::class)->where('is_active', true);
    }

    /**
     * Связь с категориями пользователя
     */
    public function categories()
    {
        return $this->hasMany(\App\Models\Category::class);
    }

    /**
     * Получить активные категории пользователя
     */
    public function activeCategories()
    {
        return $this->hasMany(\App\Models\Category::class)->where('is_active', true);
    }

    /**
     * Связь с товарами пользователя
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Получить активные товары пользователя
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    /**
     * Связь с заказами пользователя
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Получить недавние заказы пользователя
     */
    public function recentOrders()
    {
        return $this->hasMany(\App\Models\Order::class)->latest()->limit(10);
    }

    /**
     * Получить цветовую схему пользователя
     *
     * @return string
     */
    public function getColorScheme()
    {
        return $this->color_scheme ?? 'gray';
    }

    /**
     * Установить цветовую схему пользователя
     *
     * @param string $scheme
     * @return bool
     */
    public function setColorScheme(string $scheme)
    {
        $availableSchemes = config('color-schemes.available', ['gray']);
        
        if (!in_array($scheme, array_keys($availableSchemes))) {
            return false;
        }

        $this->color_scheme = $scheme;
        return $this->save();
    }

    /**
     * Получить CSS переменные для текущей цветовой схемы
     *
     * @return array
     */
    public function getColorSchemeCss()
    {
        $schemes = config('color-schemes.available', []);
        $currentScheme = $this->getColorScheme();
        
        if (!isset($schemes[$currentScheme])) {
            $currentScheme = 'gray';
        }

        return $schemes[$currentScheme]['colors'] ?? [];
    }

    /**
     * Получить информацию о текущей цветовой схеме
     *
     * @return array
     */
    public function getColorSchemeInfo()
    {
        $schemes = config('color-schemes.available', []);
        $currentScheme = $this->getColorScheme();
        
        if (!isset($schemes[$currentScheme])) {
            $currentScheme = 'gray';
        }

        return $schemes[$currentScheme] ?? [
            'name' => 'Серая',
            'description' => 'Стандартная серая схема',
            'colors' => []
        ];
    }
}
