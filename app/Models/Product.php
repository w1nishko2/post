<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telegram_bot_id',
        'category_id',
        'name',
        'description',
        'article',
        'photo_url',
        'specifications',
        'quantity',
        'price',
        'is_active',
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'decimal:2',
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
     * Связь с Telegram ботом
     */
    public function telegramBot(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class);
    }

    /**
     * Связь с категорией
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope для активных товаров
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для товаров конкретного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для товаров конкретного бота
     */
    public function scopeForBot($query, $botId)
    {
        return $query->where('telegram_bot_id', $botId);
    }

    /**
     * Получить статус наличия
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if ($this->quantity === 0) {
            return 'Нет в наличии';
        } elseif ($this->quantity <= 3) {
            return 'Заканчивается';
        }
        return 'В наличии';
    }

    /**
     * Проверить наличие товара
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->quantity > 0;
    }

    /**
     * Получить форматированную цену
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 0, ',', ' ') . ' ₽';
    }
}
