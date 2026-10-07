<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'sku',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = $product->generateUniqueSlug($product->title);
            }
        });
    }

    public function generateUniqueSlug(string $source): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * True when a sale_price is set and lower than the regular price.
     */
    public function getIsOnSaleAttribute(): bool
    {
        return ! is_null($this->sale_price) && (float) $this->sale_price < (float) $this->price;
    }

    /**
     * The price a customer actually pays: sale price when on sale, else the
     * regular price. Use this everywhere totals are calculated.
     */
    public function getCurrentPriceAttribute(): float
    {
        return $this->is_on_sale ? (float) $this->sale_price : (float) $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->is_on_sale || (float) $this->price <= 0) {
            return null;
        }

        return (int) round((1 - ((float) $this->sale_price / (float) $this->price)) * 100);
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

    public function mainImage(): ?ProductImage
    {
        return $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();
    }

    public function mainImageUrl(): ?string
    {
        return $this->mainImage()?->url();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
