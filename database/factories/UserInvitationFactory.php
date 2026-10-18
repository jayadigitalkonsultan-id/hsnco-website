<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserInvitation> */
class UserInvitationFactory extends Factory
{
    protected $model = UserInvitation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fn (array $attributes) => User::findOrFail($attributes['user_id'])->email,
            'token_hash' => hash('sha256', random_bytes(32)),
            'issued_at' => now(),
            'expires_at' => now()->addHour(),
        ];
    }
}
