<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by OrderService::placeOrder() when, at the moment of checkout, a
 * cart line requests more units than are actually available — guarding
 * against overselling when two customers check out the same low-stock
 * item at nearly the same time.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly string $productTitle, public readonly int $available)
    {
        $message = $available > 0
            ? "Sorry, only {$available} \u{d7} \"{$productTitle}\" left in stock."
            : "Sorry, \"{$productTitle}\" just sold out.";

        parent::__construct($message);
    }
}
