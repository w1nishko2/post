<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Order extends BaseModel
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
        'notes',
        'total_amount',
        'expires_at',
        'payment_confirmed_at',
        'status',
        'order_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
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
        $date = Carbon::now('Europe/Moscow')->format('Ymd');
        $lastOrder = self::whereDate('created_at', Carbon::today('Europe/Moscow'))
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
            
            // Устанавливаем время истечения заказа (5 часов с момента создания) в московском времени
            if (!$order->expires_at && $order->status === self::STATUS_PENDING) {
                $order->expires_at = Carbon::now('Europe/Moscow')->addHours(5);
            }
        });
    }

    /**
     * Проверить, истек ли заказ
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_PENDING;
    }

    /**
     * Scope для поиска истекших заказов
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('expires_at', '<', Carbon::now('Europe/Moscow'));
    }

    /**
     * Scope для поиска заказов, ожидающих оплаты
     */
    public function scopePendingPayment($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('expires_at', '>', Carbon::now('Europe/Moscow'));
    }

    /**
     * Отменить заказ и снять резерв товаров
     */
    public function cancelAndUnreserve(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        DB::transaction(function () {
            // Снимаем резерв с товаров
            foreach ($this->items as $item) {
                if ($item->product) {
                    $item->product->unreserve($item->quantity);
                }
            }

            // Обновляем статус заказа
            $this->update(['status' => self::STATUS_CANCELLED]);
        });

        return true;
    }

    /**
     * Подтвердить оплату заказа и списать товары
     */
    public function confirmPayment(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        try {
            DB::transaction(function () {
                // Списываем зарезервированные товары
                foreach ($this->items as $item) {
                    if ($item->product) {
                        // Подтверждаем покупку (уменьшаем quantity и reserved_quantity)
                        $success = $item->product->confirmPurchase($item->quantity);
                        if (!$success) {
                            throw new \Exception("Не удалось подтвердить покупку товара {$item->product_name}");
                        }
                    }
                }

                // Обновляем статус заказа и время подтверждения
                $this->update([
                    'status' => self::STATUS_PROCESSING,
                    'payment_confirmed_at' => Carbon::now('Europe/Moscow'),
                ]);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка подтверждения оплаты заказа', [
                'order_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Проверить, подтверждена ли оплата
     */
    public function isPaymentConfirmed(): bool
    {
        return !is_null($this->payment_confirmed_at);
    }

    /**
     * Получить время до истечения заказа в читаемом формате
     */
    public function getTimeUntilExpirationAttribute(): ?string
    {
        if (!$this->expires_at || $this->status !== self::STATUS_PENDING) {
            return null;
        }

        $now = Carbon::now('Europe/Moscow');
        $expiresAt = $this->expires_at->setTimezone('Europe/Moscow');
        
        if ($expiresAt->isPast()) {
            return 'Истек';
        }

        $diff = $now->diffInMinutes($expiresAt);
        
        if ($diff >= 60) {
            $hours = intval($diff / 60);
            $minutes = $diff % 60;
            return "{$hours} ч. {$minutes} мин.";
        }
        
        return "{$diff} мин.";
    }

    /**
     * Получить форматированное время истечения заказа в московском времени
     */
    public function getFormattedExpiresAtAttribute(): string
    {
        if ($this->expires_at) {
            return $this->expires_at->setTimezone('Europe/Moscow')->format('d.m.Y H:i:s');
        }
        return '-';
    }
}