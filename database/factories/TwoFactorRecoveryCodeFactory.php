<?php

namespace Database\Factories;

use App\Models\TwoFactorRecoveryCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<TwoFactorRecoveryCode> */
class TwoFactorRecoveryCodeFactory extends Factory
{
    protected $model = TwoFactorRecoveryCode::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code_hash' => Hash::make(bin2hex(random_bytes(16))),
        ];
    }
}
