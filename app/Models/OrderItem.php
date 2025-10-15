<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_article',
        'product_photo_url',
        'quantity',
        'price',
        'total_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Связь с заказом
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Связь с товаром
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Получить отформатированную цену
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 0, ',', ' ') . ' ₽';
    }

    /**
     * Получить отформатированную общую стоимость
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format((float) $this->total_price, 0, ',', ' ') . ' ₽';
    }

    /**
     * Boot модели для автоматического подсчета общей стоимости
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderItem) {
            $orderItem->total_price = $orderItem->price * $orderItem->quantity;
        });

        static::updating(function ($orderItem) {
            $orderItem->total_price = $orderItem->price * $orderItem->quantity;
        });
    }
}