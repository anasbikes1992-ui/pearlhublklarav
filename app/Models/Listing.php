<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Listing extends Model
{
    use HasFactory, HasUuid, Searchable;

    protected $fillable = [
        'provider_id',
        'vertical',
        'title',
        'slug',
        'seo_slug',
        'description',
        'price',
        'currency',
        'status',
        'metadata',
        'availability_calendar',
        'latitude',
        'longitude',
        'verified_at',
        'inspector_id',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'metadata' => 'array',
            'availability_calendar' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'verified_at' => 'datetime',
            'is_hidden' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function listingType(): HasOne
    {
        return $this->hasOne(ListingType::class);
    }

    public function verificationAudits(): HasMany
    {
        return $this->hasMany(VerificationAudit::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'seo_slug' => $this->seo_slug,
            'title' => $this->title,
            'description' => $this->description,
            'vertical' => $this->vertical,
            'price' => $this->price,
            'status' => $this->status,
            'is_hidden' => $this->is_hidden,
            'availability_calendar' => $this->availability_calendar,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
