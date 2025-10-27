<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutQueue extends Model
{
    use HasFactory;

    protected $table = 'checkout_queue';

    protected $fillable = [
        'session_id',
        'user_id',
        'session_cart_id',
        'telegram_user_id',
        'telegram_bot_id',
        'cart_data',
        'user_data',
        'notes',
        'status',
        'error_message',
        'attempts',
        'max_attempts',
        'order_id',
        'processed_at',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'user_data' => 'array',
        'telegram_user_id' => 'integer',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с ботом
     */
    public function telegramBot(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class);
    }

    /**
     * Связь с заказом (после обработки)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope: только pending записи
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->where('attempts', '<', 3)
                     ->orderBy('created_at', 'asc');
    }

    /**
     * Scope: записи готовые к повторной попытке
     */
    public function scopeRetryable($query)
    {
        return $query->where('status', 'failed')
                     ->where('attempts', '<', 3);
    }

    /**
     * Scope: по сессии
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope: по Telegram пользователю
     */
    public function scopeByTelegramUser($query, int $telegramUserId)
    {
        return $query->where('telegram_user_id', $telegramUserId);
    }

    /**
     * Отметить как processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Отметить как completed
     */
    public function markAsCompleted(int $orderId): void
    {
        $this->update([
            'status' => 'completed',
            'order_id' => $orderId,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Отметить как failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Проверить, можно ли повторить попытку
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts && $this->status === 'failed';
    }
}
