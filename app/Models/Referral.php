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
        'revenue_bonus_amount',
        'bonus_paid_at',
        'bonus_currency',
        'referral_type',
        'qualified_action',
        'qualified_at',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'integer',
            'revenue_bonus_amount' => 'decimal:2',
            'bonus_paid_at' => 'datetime',
            'qualified_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    // Type constants
    public const TYPE_SIGNUP = 'signup';
    public const TYPE_BOOKING = 'booking';
    public const TYPE_LISTING = 'listing';
    public const TYPE_VERIFIED = 'verified';

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isQualified(): bool
    {
        return $this->status === self::STATUS_QUALIFIED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || ($this->expires_at && $this->expires_at->isPast());
    }

    public function markQualified(string $action, float $bonusAmount = 0): void
    {
        $this->status = self::STATUS_QUALIFIED;
        $this->qualified_action = $action;
        $this->qualified_at = now();
        $this->revenue_bonus_amount = $bonusAmount;
        $this->save();
    }

    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    public function markPaid(): void
    {
        $this->status = self::STATUS_PAID;
        $this->bonus_paid_at = now();
        $this->save();
    }

    public function markExpired(): void
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeQualified($query)
    {
        return $query->where('status', self::STATUS_QUALIFIED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_QUALIFIED, self::STATUS_COMPLETED]);
    }
}
