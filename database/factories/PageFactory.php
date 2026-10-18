<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'key' => 'test-'.fake()->unique()->slug(2),
            'slug' => fake()->unique()->slug(3),
            'title' => fake()->sentence(4),
            'template' => 'home',
            'status' => 'draft',
        ];
    }
}
