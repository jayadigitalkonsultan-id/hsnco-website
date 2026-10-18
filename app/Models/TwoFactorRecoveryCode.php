<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorRecoveryCode extends DomainModel
{
    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public const UPDATED_AT = null;
}
