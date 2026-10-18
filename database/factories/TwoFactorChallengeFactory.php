<?php

namespace Database\Factories;

use App\Models\TwoFactorChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TwoFactorChallenge> */
class TwoFactorChallengeFactory extends Factory
{
    protected $model = TwoFactorChallenge::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'purpose' => 'login',
            'method' => 'totp',
            'session_binding_hash' => hash('sha256', random_bytes(32)),
            'expires_at' => now()->addMinutes(5),
        ];
    }
}
