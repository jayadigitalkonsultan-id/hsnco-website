<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserTwoFactorAuthentication> */
class UserTwoFactorAuthenticationFactory extends Factory
{
    protected $model = UserTwoFactorAuthentication::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'method' => 'totp',
            'secret' => 'test-only-secret',
        ];
    }
}
