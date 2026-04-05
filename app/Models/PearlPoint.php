<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PearlPoint extends Model
{
    use HasUuids;

    protected $table = 'pearl_points';

    protected $fillable = [
        'user_id',
        'total_earned',
        'total_redeemed',
    ];

    protected function casts(): array
    {
        return [
            'total_earned' => 'integer',
            'total_redeemed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getBalanceAttribute(): int
    {
        return $this->total_earned - $this->total_redeemed;
    }
}
