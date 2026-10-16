<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A wrapping paper option a customer can pick (at most one) for a custom
 * bouquet on the /custom-bouquet builder — see CartService::addCustom().
 * Same shape/role as Flower and Ribbon, stock_quantity included.
 */
class WrappingPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock_quantity',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
