<?php

namespace App\Models;

use App\Enums\TwoFactorMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorChallenge extends DomainModel
{
    protected $hidden = ['code_hash', 'session_binding_hash'];

    protected function casts(): array
    {
        return [
            'method' => TwoFactorMethod::class,
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public const UPDATED_AT = null;
}
