<?php

namespace App\Facades;

use App\Services\CartService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string add(\App\Models\Product $product, int $quantity = 1, ?string $giftMessage = null)
 * @method static void update(string $rowId, int $quantity)
 * @method static void remove(string $rowId)
 * @method static void clear()
 * @method static bool isEmpty()
 * @method static array items()
 * @method static \Illuminate\Support\Collection content()
 * @method static int count()
 * @method static float subtotal()
 *
 * @see CartService
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
