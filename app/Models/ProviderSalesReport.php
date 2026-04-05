<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSalesReport extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'provider_id',
        'month',
        'total_sales',
        'commission_due',
        'tax_applied',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'total_sales' => 'decimal:2',
            'commission_due' => 'decimal:2',
            'tax_applied' => 'decimal:2',
            'verified' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
