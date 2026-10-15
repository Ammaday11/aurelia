<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A wrapping paper option a customer can pick (at most one) for a custom
 * bouquet on the /custom-bouquet builder — see CartService::addCustom().
 * Same shape/role as Flower and Ribbon.
 */
class WrappingPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
