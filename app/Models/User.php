<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    protected $attributes = ['status' => 'invited', 'role' => 'editor', 'auth_version' => 0];

    public function setEmailAttribute(string $email): void
    {
        $this->attributes['email'] = mb_strtolower(trim($email));
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function managesContent(): bool
    {
        return $this->isActive() && in_array($this->role, [UserRole::SuperAdmin, UserRole::Admin], true);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'invited_by');
    }

    public function invitation(): HasOne
    {
        return $this->hasOne(UserInvitation::class);
    }

    public function twoFactorAuthentication(): HasOne
    {
        return $this->hasOne(UserTwoFactorAuthentication::class);
    }

    public function recoveryCodes(): HasMany
    {
        return $this->hasMany(TwoFactorRecoveryCode::class);
    }

    public function twoFactorChallenges(): HasMany
    {
        return $this->hasMany(TwoFactorChallenge::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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
            'password' => 'hashed',
            'status' => UserStatus::class,
            'role' => UserRole::class,
            'failed_login_attempts' => 'integer',
            'last_failed_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'last_login_ip' => 'string',
            'password_changed_at' => 'datetime',
            'activated_at' => 'datetime',
            'auth_version' => 'integer',
            'preferences' => 'array',
        ];
    }
}
