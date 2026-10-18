<?php

namespace Database\Factories;

use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Professional> */
class ProfessionalFactory extends Factory
{
    protected $model = Professional::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fake()->unique()->slug(3),
            'position' => 'Mitra',
            'level' => 'partner',
            'status' => 'draft',
            'qualifications' => [],
            'languages' => ['Indonesia'],
        ];
    }
}
