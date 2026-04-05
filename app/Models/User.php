<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasUuid;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PROVIDER = 'provider';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_DRIVER = 'driver';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'is_active',
        'password',
    ];

    /**
     * Set the user's role safely. Only use in admin contexts.
     */
    public function setRole(string $role): void
    {
        $allowedRoles = [self::ROLE_ADMIN, self::ROLE_PROVIDER, self::ROLE_CUSTOMER, self::ROLE_DRIVER];
        
        if (!in_array($role, $allowedRoles, true)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }
        
        $this->role = $role;
        $this->save();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_user_id');
    }

    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }
}
