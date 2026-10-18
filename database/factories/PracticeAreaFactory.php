<?php

namespace Database\Factories;

use App\Models\PracticeArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PracticeArea> */
class PracticeAreaFactory extends Factory
{
    protected $model = PracticeArea::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(3),
            'summary' => fake()->paragraph(),
            'status' => 'draft',
            'sort_order' => 0,
        ];
    }
}
