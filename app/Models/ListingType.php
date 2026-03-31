<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingType extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'listing_id',
        'type',
        'sqft',
        'beds',
        'baths',
        'deed_status',
        'room_type',
        'amenities_json',
        'check_in_out_times',
        'transmission',
        'fuel_type',
        'daily_rate',
        'with_driver',
        'venue_map_json',
        'ticket_tiers_json',
        'qr_secret',
        'extra_json',
    ];

    protected function casts(): array
    {
        return [
            'amenities_json' => 'array',
            'check_in_out_times' => 'array',
            'daily_rate' => 'decimal:2',
            'with_driver' => 'boolean',
            'venue_map_json' => 'array',
            'ticket_tiers_json' => 'array',
            'extra_json' => 'array',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
