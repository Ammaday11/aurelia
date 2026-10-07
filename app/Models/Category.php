<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (blank($category->slug)) {
                $category->slug = $category->generateUniqueSlug($category->name);
            }
        });
    }

    /**
     * Build a slug from $source, appending -2, -3, ... if it collides with
     * an existing category (excluding this record when updating).
     */
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(): ?string
    {
        return $this->image ? \Storage::disk('public')->url($this->image) : null;
    }
}
