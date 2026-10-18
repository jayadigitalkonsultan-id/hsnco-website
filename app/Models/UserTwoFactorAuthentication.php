<?php

namespace App\Models;

use App\Enums\TwoFactorMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTwoFactorAuthentication extends DomainModel
{
    protected $hidden = ['secret', 'last_used_step'];

    protected function casts(): array
    {
        return [
            'method' => TwoFactorMethod::class,
            'secret' => 'encrypted',
            'confirmed_at' => 'datetime',
            'last_used_step' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
