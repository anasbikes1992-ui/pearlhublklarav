<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbackRecord extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'booking_id',
        'customer_id',
        'provider_id',
        'sale_amount',
        'cashback_rate',
        'cashback_amount',
        'currency',
        'status',
        'confirmed_at',
        'credited_at',
    ];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'cashback_rate' => 'decimal:4',
        'cashback_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'credited_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
