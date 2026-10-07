<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'submitted_from_ip',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function getIsReadAttribute(): bool
    {
        return ! is_null($this->read_at);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
