<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A charge line attached to a specific Order — either added automatically
 * at checkout (is_automatic true, from every active automatic CustomCharge
 * at that time) or manually by an admin afterwards (see
 * OrderService::attachCharge()). name/amount are snapshotted so the order
 * keeps its own history regardless of later changes to the CustomCharge.
 */
class OrderCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'custom_charge_id',
        'name',
        'amount',
        'is_automatic',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_automatic' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customCharge(): BelongsTo
    {
        return $this->belongsTo(CustomCharge::class);
    }
}
