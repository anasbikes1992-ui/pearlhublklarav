<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmeProduct extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'listing_id',
        'name',
        'category',
        'description',
        'price',
        'variants',
        'is_active',
        'stock_status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'variants' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
