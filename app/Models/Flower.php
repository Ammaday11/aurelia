<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single flower type (Rose, Lily, Tulip, ...) a customer can pick for a
 * custom bouquet on the /custom-bouquet builder — see CustomBouquetController
 * and CartService::addCustom(). Priced per stem; stock_quantity works the
 * same way as Product's (see that model), guarding against a custom order
 * being built from stems the shop doesn't actually have on hand.
 */
class Flower extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_per_stem',
        'stock_quantity',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price_per_stem' => 'decimal:2',
        'stock_quantity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= config('shop.low_stock_threshold');
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match (true) {
            $this->stock_quantity <= 0 => 'Out of stock',
            $this->is_low_stock => "Only {$this->stock_quantity} left",
            default => 'In stock',
        };
    }

    public function imageUrl(): ?string
    {
        // asset(), not Storage::disk('public')->url() (which hardcodes
        // APP_URL) — see App\Models\ProductImage::url() for why.
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
