<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'nic',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'country_code',
        'is_kyc_verified',
    ];

    protected function casts(): array
    {
        return [
            'is_kyc_verified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
