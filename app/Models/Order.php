<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'session_id',
        'telegram_chat_id',
        'telegram_bot_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'notes',
        'total_amount',
        'status',
        'order_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить уникальные статусы для использования в формах
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает обработки',
            self::STATUS_PROCESSING => 'В обработке',
            self::STATUS_COMPLETED => 'Выполнен',
            self::STATUS_CANCELLED => 'Отменен',
        ];
    }

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
     * Связь с позициями заказа
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Получить отформатированную общую стоимость
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total_amount, 0, ',', ' ') . ' ₽';
    }

    /**
     * Получить читабельный статус
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Неизвестный';
    }

    /**
     * Получить CSS класс для статуса
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge bg-warning text-dark',
            self::STATUS_PROCESSING => 'badge bg-info',
            self::STATUS_COMPLETED => 'badge bg-success',
            self::STATUS_CANCELLED => 'badge bg-danger',
            default => 'badge bg-secondary'
        };
    }

    /**
     * Scope для заказов конкретного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для заказов по session_id
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope для заказов конкретного бота
     */
    public function scopeForBot($query, $botId)
    {
        return $query->where('telegram_bot_id', $botId);
    }

    /**
     * Сгенерировать уникальный номер заказа
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
                        ->orderBy('id', 'desc')
                        ->first();
        
        $sequence = $lastOrder ? (intval(substr($lastOrder->order_number, -3)) + 1) : 1;
        
        return $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Получить количество товаров в заказе
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Проверить, можно ли отменить заказ
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Boot модели
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }
}