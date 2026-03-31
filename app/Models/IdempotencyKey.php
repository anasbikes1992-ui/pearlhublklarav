<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'key',
        'gateway',
        'payload_hash',
        'status',
        'processed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
