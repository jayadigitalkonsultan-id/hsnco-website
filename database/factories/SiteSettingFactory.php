<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SiteSetting> */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'group' => 'identity',
            'key' => 'identity.'.fake()->unique()->slug(1),
            'value' => ['text' => fake()->word()],
        ];
    }
}
