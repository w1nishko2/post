<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telegram_bot_id',
        'name',
        'description',
        'photo_url',
        'is_active',
    ];

    // Защита от массового назначения критических полей
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с ботом Telegram
     */
    public function telegramBot(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class);
    }

    /**
     * Связь с товарами
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope для активных категорий
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для категорий конкретного пользователя
     */
    public function scopeForUser($query, int $userId)
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID должен быть положительным числом');
        }
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для категорий конкретного бота
     */
    public function scopeForBot($query, int $botId)
    {
        if ($botId <= 0) {
            throw new \InvalidArgumentException('Bot ID должен быть положительным числом');
        }
        return $query->where('telegram_bot_id', $botId);
    }

    /**
     * Получить активные товары категории
     */
    public function activeProducts()
    {
        return $this->products()->active();
    }

    /**
     * Получить количество активных товаров
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->activeProducts()->count();
    }
}