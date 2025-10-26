<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_path',
        'thumbnail_path',
        'is_main',
        'order',
        'original_name',
        'file_size',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'order' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Связь с товаром
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Получить полный URL изображения
     */
    public function getUrlAttribute(): string
    {
        // Удаляем ведущий слэш если он есть, чтобы избежать дублирования
        $path = ltrim($this->file_path, '/');
        return asset('storage/' . $path);
    }

    /**
     * Получить полный URL миниатюры
     */
    public function getThumbnailUrlAttribute(): string
    {
        // Удаляем ведущий слэш если он есть, чтобы избежать дублирования
        $path = ltrim($this->thumbnail_path, '/');
        return asset('storage/' . $path);
    }

    /**
     * Установить текущее изображение главным
     */
    public function setAsMain(): bool
    {
        // Сбрасываем флаг is_main у всех изображений товара
        $this->product->images()->update(['is_main' => false]);
        
        // Устанавливаем текущее изображение главным
        return $this->update(['is_main' => true]);
    }

    /**
     * Удалить изображение и файлы
     */
    public function deleteWithFiles(): bool
    {
        // Удаляем файлы из хранилища
        if (Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
        
        if (Storage::disk('public')->exists($this->thumbnail_path)) {
            Storage::disk('public')->delete($this->thumbnail_path);
        }
        
        // Удаляем запись из БД
        return $this->delete();
    }

    /**
     * Scope для получения отсортированных изображений
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * Scope для получения главного изображения
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }
}
