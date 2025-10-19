<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends BaseModel
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
        'reserved_quantity',
        'price',
        'markup_percentage',
        'is_active',
    ];

    // Защита от массового назначения критических полей
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
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
    public function scopeForUser($query, int $userId)
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID должен быть положительным числом');
        }
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для товаров конкретного бота
     */
    public function scopeForBot($query, int $botId)
    {
        if ($botId <= 0) {
            throw new \InvalidArgumentException('Bot ID должен быть положительным числом');
        }
        return $query->where('telegram_bot_id', $botId);
    }

    /**
     * Scope для сортировки списка товаров при выводе в каталоге/поиске.
     * Сначала товары с количеством > 0, затем по дате создания (новые первыми).
     * Также можно учитывать флаг is_active (активные выше неактивных) при необходимости.
     */
    public function scopeOrderedForListing($query)
    {
        // MySQL/SQLite: выражение (quantity > 0) вернёт 1 или 0, сортируем по убыванию,
        // чтобы товары с положительным количеством шли первыми.
        // Далее сортируем по is_active (true первее), затем по created_at (новые первыми).
        return $query->orderByRaw('(COALESCE(quantity, 0) > 0) DESC')
                     ->orderByRaw('(is_active = 1) DESC')
                     ->orderBy('created_at', 'desc');
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

    /**
     * Получить цену с наценкой
     */
    public function getPriceWithMarkupAttribute(): float
    {
        $markup = $this->markup_percentage ?? 0;
        return $this->price * (1 + $markup / 100);
    }

    /**
     * Получить форматированную цену с наценкой
     */
    public function getFormattedPriceWithMarkupAttribute(): string
    {
        return number_format($this->price_with_markup, 0, ',', ' ') . ' ₽';
    }

    /**
     * Получить размер наценки в деньгах
     */
    public function getMarkupAmountAttribute(): float
    {
        return $this->price_with_markup - $this->price;
    }

    /**
     * Получить форматированную наценку в деньгах
     */
    public function getFormattedMarkupAmountAttribute(): string
    {
        return number_format($this->markup_amount, 0, ',', ' ') . ' ₽';
    }

    /**
     * Получить доступное количество (общее количество минус зарезервированное)
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - ($this->reserved_quantity ?? 0));
    }

    /**
     * Проверить, можно ли зарезервировать указанное количество
     */
    public function canReserve(int $quantity): bool
    {
        return $this->is_active && $this->available_quantity >= $quantity;
    }

    /**
     * Зарезервировать товар (увеличить reserved_quantity)
     */
    public function reserve(int $quantity): bool
    {
        if (!$this->canReserve($quantity)) {
            return false;
        }

        return $this->increment('reserved_quantity', $quantity);
    }

    /**
     * Снять резерв товара (уменьшить reserved_quantity)
     */
    public function unreserve(int $quantity): bool
    {
        $currentReserved = $this->reserved_quantity ?? 0;
        $newReserved = max(0, $currentReserved - $quantity);
        
        return $this->update(['reserved_quantity' => $newReserved]);
    }

    /**
     * Подтвердить покупку (уменьшить quantity и reserved_quantity)
     */
    public function confirmPurchase(int $quantity): bool
    {
        $currentReserved = $this->reserved_quantity ?? 0;
        $currentQuantity = $this->quantity;

        if ($currentReserved < $quantity || $currentQuantity < $quantity) {
            return false;
        }

        return $this->update([
            'quantity' => $currentQuantity - $quantity,
            'reserved_quantity' => $currentReserved - $quantity,
        ]);
    }

    /**
     * Проверить наличие товара с учетом резерва
     */
    public function isAvailableForReservation(int $quantity = 1): bool
    {
        return $this->is_active && $this->available_quantity >= $quantity;
    }
}
