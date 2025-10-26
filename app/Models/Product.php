<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
        'photos_gallery',
        'main_photo_index',
        'specifications',
        'quantity',
        'reserved_quantity',
        'price',
        'markup_percentage',
        'is_active',
        'images_download_status',
        'images_download_error',
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
     * Получить обработанные данные фотографий
     */
    public function getProcessedPhotoData(): array
    {
        // Сначала проверяем, есть ли сохраненная галерея
        if ($this->photos_gallery && is_array($this->photos_gallery) && count($this->photos_gallery) > 0) {
            $mainPhotoIndex = $this->main_photo_index ?? 0;
            $mainPhoto = $this->photos_gallery[$mainPhotoIndex] ?? $this->photos_gallery[0] ?? null;
            
            return [
                'type' => 'gallery',
                'photos' => $this->photos_gallery,
                'main_photo' => $mainPhoto,
                'main_photo_index' => $mainPhotoIndex
            ];
        }
        
        // Если нет сохраненной галереи, но есть photo_url
        if (!$this->photo_url) {
            return [
                'type' => 'none',
                'photos' => [],
                'main_photo' => null
            ];
        }

        // Обычная фотография
        return [
            'type' => 'single',
            'photos' => [$this->photo_url],
            'main_photo' => $this->photo_url
        ];
    }

    /**
     * Получить главную фотографию для быстрого отображения
     */
    public function getMainPhotoForDisplay(): ?string
    {
        // Приоритет 1: Главное изображение из новой системы
        $mainImage = $this->mainImage()->first();
        if ($mainImage) {
            return $mainImage->url;
        }
        
        // Приоритет 2: Если есть сохраненная галерея - берем главную из неё и преобразуем в полный URL
        if ($this->photos_gallery && is_array($this->photos_gallery) && count($this->photos_gallery) > 0) {
            $mainPhotoIndex = $this->main_photo_index ?? 0;
            $path = $this->photos_gallery[$mainPhotoIndex] ?? $this->photos_gallery[0] ?? null;
            
            if ($path) {
                // Удаляем ведущий слэш если есть и добавляем /storage/
                return asset('storage/' . ltrim($path, '/'));
            }
        }
        
        // Последний фоллбэк: возвращаем photo_url как есть
        return $this->photo_url;
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
     * Связь с категорией
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Связь с изображениями товара
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Получить главное изображение
     */
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    /**
     * Получить URL главного изображения
     */
    public function getMainImageUrlAttribute(): ?string
    {
        // Сначала проверяем новую систему изображений
        $mainImage = $this->mainImage()->first();
        if ($mainImage) {
            return $mainImage->url;
        }

        // Фоллбэк на старую систему
        return $this->getMainPhotoForDisplay();
    }

    /**
     * Получить URL миниатюры главного изображения
     */
    public function getMainImageThumbnailAttribute(): ?string
    {
        $mainImage = $this->mainImage()->first();
        if ($mainImage) {
            return $mainImage->thumbnail_url;
        }

        // Фоллбэк на старую систему
        return $this->getMainPhotoForDisplay();
    }

    /**
     * Получить все URL изображений
     */
    public function getAllImageUrlsAttribute(): array
    {
        $images = $this->images()->ordered()->get();
        
        if ($images->isNotEmpty()) {
            return $images->pluck('url')->toArray();
        }

        // Фоллбэк на старую систему
        return $this->getAllPhotosAttribute();
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
     * Получить главную фотографию товара (с прокси для Яндекс.Диска)
     */
    public function getMainPhotoUrlAttribute(): ?string
    {
        // Используем новый быстрый метод без HTTP запросов
        return $this->getMainPhotoForDisplay();
    }

    /**
     * Получить все URL фотографий для отображения
     */
    public function getAllPhotosAttribute(): array
    {
        // Приоритет: новая система изображений из таблицы product_images
        $images = $this->images()->ordered()->get();
        
        if ($images->isNotEmpty()) {
            return $images->pluck('url')->toArray();
        }
        
        // Фоллбэк: Если есть сохраненная галерея, преобразуем пути в полные URL
        if ($this->photos_gallery && is_array($this->photos_gallery) && count($this->photos_gallery) > 0) {
            return array_map(function($path) {
                // Удаляем ведущий слэш если есть и добавляем /storage/
                return asset('storage/' . ltrim($path, '/'));
            }, $this->photos_gallery);
        }
        
        // Последний фоллбэк: используем обработанные данные
        $photoData = $this->getProcessedPhotoData();
        return $photoData['photos'] ?? ($this->photo_url ? [$this->photo_url] : []);
    }

    /**
     * Проверить, есть ли несколько фотографий
     */
    public function getHasMultiplePhotosAttribute(): bool
    {
        $photos = $this->getAllPhotosAttribute();
        return count($photos) > 1;
    }

    /**
     * Получить URL для отображения конкретной фотографии
     */
    public function getPhotoUrl($photo = null): string
    {
        $url = null;
        
        if ($photo && is_array($photo) && isset($photo['display_url'])) {
            $url = $photo['display_url'];
        } elseif ($photo && is_string($photo)) {
            $url = $photo;
        } else {
            $url = $this->getMainPhotoUrlAttribute();
        }
        
        return $url ?? '';
    }


}
