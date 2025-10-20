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
        'yandex_disk_folder_url',
        'photos_gallery',
        'main_photo_index',
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
        'photos_gallery' => 'array',
        'price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'main_photo_index' => 'integer',
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

    /**
     * Получить главную фотографию товара
     */
    public function getMainPhotoUrlAttribute(): ?string
    {
        // Если есть галерея фотографий
        if ($this->photos_gallery && is_array($this->photos_gallery) && count($this->photos_gallery) > 0) {
            $index = min($this->main_photo_index ?? 0, count($this->photos_gallery) - 1);
            $originalUrl = $this->photos_gallery[$index] ?? null;
            
            // Если это URL из Яндекс.Диска, проксируем его
            if ($originalUrl && (str_contains($originalUrl, 'downloader.disk.yandex.ru') || str_contains($originalUrl, 'disk.yandex'))) {
                return url('/api/yandex-image-proxy?url=' . urlencode($originalUrl));
            }
            
            return $originalUrl;
        }
        
        // Иначе возвращаем стандартное поле photo_url
        return $this->photo_url;
    }

    /**
     * Получить все фотографии товара (галерея + основная фотография)
     */
    public function getAllPhotosAttribute(): array
    {
        $photos = [];
        
        // Если есть галерея фотографий из Яндекс.Диска
        if ($this->photos_gallery && is_array($this->photos_gallery)) {
            $photos = array_map(function($url) {
                // Если это URL из Яндекс.Диска, проксируем его
                if ($url && (str_contains($url, 'downloader.disk.yandex.ru') || str_contains($url, 'disk.yandex'))) {
                    return url('/api/yandex-image-proxy?url=' . urlencode($url));
                }
                return $url;
            }, $this->photos_gallery);
        }
        
        // Если галерея пустая, но есть основная фотография
        if (empty($photos) && $this->photo_url) {
            $photos = [$this->photo_url];
        }
        
        return $photos;
    }

    /**
     * Проверить, есть ли несколько фотографий
     */
    public function getHasMultiplePhotosAttribute(): bool
    {
        return count($this->all_photos) > 1;
    }

    /**
     * Получить URL для работы с Яндекс.Диском
     */
    public function getYandexDiskPublicKeyAttribute(): ?string
    {
        if (!$this->yandex_disk_folder_url) {
            return null;
        }
        
        // Извлекаем публичный ключ из URL типа https://disk.yandex.ru/d/hV4dQv-tEeXN_A
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $this->yandex_disk_folder_url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Установить порядок фотографий в галерее
     */
    public function setPhotosOrder(array $photosUrls, int $mainPhotoIndex = 0): bool
    {
        return $this->update([
            'photos_gallery' => $photosUrls,
            'main_photo_index' => max(0, min($mainPhotoIndex, count($photosUrls) - 1))
        ]);
    }

    /**
     * Установить главную фотографию по индексу
     */
    public function setMainPhoto(int $index): bool
    {
        $gallery = $this->photos_gallery ?? [];
        if (empty($gallery) || $index < 0 || $index >= count($gallery)) {
            return false;
        }
        
        return $this->update(['main_photo_index' => $index]);
    }
}
