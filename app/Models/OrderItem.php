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
        'product_title',
        'unit_price',
        'quantity',
        'line_total',
        'gift_message',
        'custom_flowers',
        'wrapping_paper_name',
        'wrapping_paper_price',
        'ribbon_name',
        'ribbon_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'line_total' => 'decimal:2',
        'custom_flowers' => 'array',
        'wrapping_paper_price' => 'decimal:2',
        'ribbon_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * True for a custom-bouquet line item (product_id is null, built from
     * a flower selection instead of a catalogue Product) — see
     * CartService::addCustom() / OrderService::placeOrder().
     */
    public function isCustom(): bool
    {
        return is_null($this->product_id) && ! empty($this->custom_flowers);
    }
}
