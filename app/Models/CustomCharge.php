<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An admin-defined named charge (e.g. "Rush Order Fee", "Service Charge").
 * While active, an is_automatic charge is added to the total of every
 * order placed (see OrderService::placeOrder()); a non-automatic one is
 * never applied on its own — an admin attaches it to a specific order
 * from that order's detail page (see Admin\OrderController::attachCharge()
 * and OrderService::attachCharge()).
 */
class CustomCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'is_automatic',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_automatic' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
