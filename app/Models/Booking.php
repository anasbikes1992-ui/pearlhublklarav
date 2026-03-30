<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'listing_id',
        'customer_id',
        'start_at',
        'end_at',
        'status',
        'total_amount',
        'currency',
        'payment_status',
        'notes',
    ];

    /**
     * Fields that cannot be mass-assigned by user input.
     * Only the service layer should set these via explicit assignment.
     */
    protected $guarded_from_user = [
        'customer_id',
        'total_amount',
        'currency',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
