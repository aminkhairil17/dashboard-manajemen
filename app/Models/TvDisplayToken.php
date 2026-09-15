<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TvDisplayToken extends Model
{
    const TYPE_PUBLIK = 'publik';

    const TYPE_DIREKTUR = 'direktur';

    protected $fillable = ['token', 'label', 'type', 'created_by', 'last_used_at', 'revoked_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public static function generate(string $label, string $type = self::TYPE_PUBLIK, ?int $createdBy = null): self
    {
        return static::create([
            'token' => Str::random(40),
            'label' => $label,
            'type' => $type,
            'created_by' => $createdBy,
        ]);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function isDirektur(): bool
    {
        return $this->type === self::TYPE_DIREKTUR;
    }
}
