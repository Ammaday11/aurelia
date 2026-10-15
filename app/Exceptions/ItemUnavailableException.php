<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by OrderService::placeOrder() when a custom-bouquet cart line
 * references a flower that's been deactivated or deleted from the
 * catalogue since it was added to the cart. Flowers have no stock
 * concept (see Flower), so this is the custom-order analogue of
 * InsufficientStockException.
 */
class ItemUnavailableException extends RuntimeException
{
    public function __construct(public readonly string $itemName)
    {
        parent::__construct("Sorry, \"{$itemName}\" is no longer available. Please update your bouquet.");
    }
}
