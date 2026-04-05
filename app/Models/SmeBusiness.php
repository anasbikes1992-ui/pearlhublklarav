<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmeBusiness extends Model
{
    use HasUuids;

    protected $table = 'sme_businesses';

    protected $fillable = [
        'user_id',
        'business_name',
        'description',
        'business_type',
        'location',
        'lat',
        'lng',
        'phone',
        'email',
        'website',
        'images',
        'verified',
        'moderation_status',
        'active',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'verified' => 'boolean',
            'active' => 'boolean',
            'images' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
