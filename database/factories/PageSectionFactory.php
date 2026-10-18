<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PageSection> */
class PageSectionFactory extends Factory
{
    protected $model = PageSection::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'key' => fake()->unique()->slug(2),
            'type' => 'rich_text',
            'content' => ['text' => fake()->paragraph()],
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
