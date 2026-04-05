<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasUuids;

    protected $table = 'referrals';

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'code',
        'status',
        'points_awarded',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'integer',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
