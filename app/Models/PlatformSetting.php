<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function decimal(string $key, float $default): float
    {
        $setting = self::query()->where('key', $key)->first();
        $raw = $setting?->value['value'] ?? null;

        if (is_numeric($raw)) {
            return (float) $raw;
        }

        return $default;
    }
}
