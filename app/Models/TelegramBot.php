<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bot_name',
        'bot_username',
        'bot_token',
        'api_id',
        'api_hash',
        'webhook_url',
        'mini_app_url',
        'mini_app_short_name',
        'menu_button',
        'commands',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'menu_button' => 'array',
        'commands' => 'array',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    protected $hidden = [
        'bot_token',
        'api_hash',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить замаскированный токен бота для отображения
     */
    public function getMaskedTokenAttribute(): string
    {
        if (!$this->bot_token) {
            return '';
        }
        
        $token = $this->bot_token;
        $parts = explode(':', $token);
        
        if (count($parts) >= 2) {
            return $parts[0] . ':' . str_repeat('*', strlen($parts[1]) - 4) . substr($parts[1], -4);
        }
        
        return substr($token, 0, 8) . str_repeat('*', max(0, strlen($token) - 12)) . substr($token, -4);
    }

    /**
     * Проверить, настроен ли Mini App
     */
    public function hasMiniApp(): bool
    {
        return !empty($this->mini_app_url) && !empty($this->mini_app_short_name);
    }

    /**
     * Получить полный URL для Mini App
     */
    public function getMiniAppUrl(): ?string
    {
        if (!$this->hasMiniApp()) {
            return null;
        }

        // Если URL уже полный, возвращаем как есть
        if (filter_var($this->mini_app_url, FILTER_VALIDATE_URL)) {
            return $this->mini_app_url;
        }

        // Иначе формируем URL на основе короткого имени
        if (!empty($this->mini_app_short_name)) {
            return config('app.url') . '/' . $this->mini_app_short_name;
        }

        return $this->mini_app_url;
    }

    /**
     * Получить URL для настройки webhook
     */
    public function getWebhookUrl(): ?string
    {
        if (empty($this->webhook_url)) {
            return route('telegram.webhook', ['bot' => $this->id]);
        }

        return $this->webhook_url;
    }

    /**
     * Получить отображаемый URL для Mini App
     */
    public function getDisplayMiniAppUrl(): ?string
    {
        if (!$this->mini_app_short_name) {
            return null;
        }

        return config('app.url') . '/' . $this->mini_app_short_name;
    }

    /**
     * Scope для активных ботов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для ботов конкретного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}