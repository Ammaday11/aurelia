<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single flower type (Rose, Lily, Tulip, ...) a customer can pick for a
 * custom bouquet on the /custom-bouquet builder — see CustomBouquetController
 * and CartService::addCustom(). Priced per stem; unlike Product, flowers
 * have no stock_quantity (the shop's artificial-flower stems are treated
 * as always available while a flower is active).
 */
class Flower extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_per_stem',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price_per_stem' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function imageUrl(): ?string
    {
        // asset(), not Storage::disk('public')->url() (which hardcodes
        // APP_URL) — see App\Models\ProductImage::url() for why.
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
