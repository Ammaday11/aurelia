<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'thumb_path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        // asset() (not Storage::disk('public')->url(), which hardcodes
        // APP_URL) resolves against the current request's actual host/port,
        // so images still load when the app is served from somewhere other
        // than APP_URL — e.g. `php artisan serve` on a non-default port.
        return asset('storage/'.$this->path);
    }

    public function thumbUrl(): string
    {
        return $this->thumb_path
            ? asset('storage/'.$this->thumb_path)
            : $this->url();
    }
}
