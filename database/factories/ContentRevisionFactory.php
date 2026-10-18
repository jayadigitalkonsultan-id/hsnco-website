<?php

namespace Database\Factories;

use App\Models\ContentRevision;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContentRevision> */
class ContentRevisionFactory extends Factory
{
    protected $model = ContentRevision::class;

    public function definition(): array
    {
        return [
            'revisionable_type' => 'page',
            'revisionable_id' => Page::factory(),
            'version' => 1,
            'status' => 'draft',
            'payload' => ['title' => fake()->sentence()],
        ];
    }
}
