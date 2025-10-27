<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ImportQueue extends Model
{
    use HasFactory;

    protected $table = 'import_queue';

    protected $fillable = [
        'session_id',
        'user_id',
        'telegram_bot_id',
        'row_data',
        'update_existing',
        'download_images',
        'status',
        'error_message',
        'attempts',
        'max_attempts',
        'product_id',
        'processed_at',
    ];

    protected $casts = [
        'row_data' => 'array',
        'update_existing' => 'boolean',
        'download_images' => 'boolean',
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
     * Связь с товаром (после обработки)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: только pending записи
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->where('attempts', '<', 3);
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
    public function markAsCompleted(int $productId): void
    {
        $this->update([
            'status' => 'completed',
            'product_id' => $productId,
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
}

