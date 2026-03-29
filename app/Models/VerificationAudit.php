<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationAudit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'listing_id',
        'inspector_id',
        'status',
        'notes',
        'inspected_at',
        'photo_urls',
    ];

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
            'photo_urls' => 'array',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
