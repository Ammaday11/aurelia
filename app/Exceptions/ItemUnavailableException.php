<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by OrderService::placeOrder() when a custom-bouquet cart line
 * references a flower, wrapping paper, or ribbon that's been deactivated
 * or deleted from the catalogue since it was added to the cart — the
 * "no longer offered" counterpart to InsufficientStockException's
 * "offered, but not enough left".
 */
class ItemUnavailableException extends RuntimeException
{
    public function __construct(public readonly string $itemName)
    {
        parent::__construct("Sorry, \"{$itemName}\" is no longer available. Please update your bouquet.");
    }
}
