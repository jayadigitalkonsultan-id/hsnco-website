<?php

namespace Database\Factories;

use App\Models\Insight;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Insight> */
class InsightFactory extends Factory
{
    protected $model = Insight::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'slug' => fake()->unique()->slug(4),
            'excerpt' => fake()->paragraph(),
            'status' => 'draft',
        ];
    }
}
