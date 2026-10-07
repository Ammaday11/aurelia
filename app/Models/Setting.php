<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Simple key/value store backing the admin "Settings" screen: shop name,
 * logo/favicon paths, contact info, delivery fee & time slots, bank
 * account details, etc. Values are stored as strings; JSON-ish values
 * (e.g. delivery time slots) are json_encode()'d by the caller and decoded
 * on read via get() with a sensible default.
 */
class Setting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'shop_settings';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * All settings as a flat [key => value] array, cached for an hour.
     */
    public static function all_cached(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all_cached();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Persist several key/value pairs at once (e.g. from a Settings form).
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public static function getJson(string $key, mixed $default = null): mixed
    {
        $raw = static::get($key);

        if (blank($raw)) {
            return $default;
        }

        return json_decode($raw, true) ?? $default;
    }
}
