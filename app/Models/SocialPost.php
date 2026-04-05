<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPost extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'media_urls',
        'vertical_tag',
        'listing_id',
        'likes_count',
        'comments_count',
        'is_pinned',
        'is_flagged',
    ];

    protected $casts = [
        'media_urls'     => 'array',
        'is_pinned'      => 'boolean',
        'is_flagged'     => 'boolean',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialComment::class, 'post_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(SocialLike::class, 'post_id');
    }
}
