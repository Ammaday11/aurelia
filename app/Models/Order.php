<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * The order status workflow, in forward order. "cancelled" is reachable
     * from any non-terminal state but is not part of the forward chain.
     */
    public const STATUSES = [
        'pending',
        'confirmed',
        'preparing',
        'out_for_delivery',
        'delivered',
    ];

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number',
        'name',
        'phone',
        'email',
        'delivery_address',
        'delivery_date',
        'delivery_time_slot',
        'gift_message',
        'special_notes',
        'payment_method',
        'payment_receipt_path',
        'status',
        'subtotal',
        'delivery_fee',
        'total',
        'currency',
        'placed_from_ip',
        'cancelled_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * Generate a unique, human-friendly order number, e.g. AUR-20261007-7F3K.
     * Retries on the rare collision rather than trusting pure luck.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = sprintf(
                '%s-%s-%s',
                config('shop.order_number_prefix'),
                now()->format('Ymd'),
                Str::upper(Str::random(4))
            );
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function statusLabel(): string
    {
        return order_status_label($this->status);
    }

    public function paymentMethodLabel(): string
    {
        return payment_method_label($this->payment_method);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Whether the order can still move forward or be cancelled (i.e. it has
     * not reached a terminal state yet).
     */
    public function isActive(): bool
    {
        return ! in_array($this->status, ['delivered', 'cancelled'], true);
    }

    public function receiptUrl(): ?string
    {
        return $this->payment_receipt_path
            ? \Storage::disk('public')->url($this->payment_receipt_path)
            : null;
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopePaymentMethod($query, ?string $method)
    {
        return $method ? $query->where('payment_method', $method) : $query;
    }

    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }
}
