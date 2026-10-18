<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MediaUsage> */
class MediaUsageFactory extends Factory
{
    protected $model = MediaUsage::class;

    public function definition(): array
    {
        return [
            'media_id' => Media::factory(),
            'usable_type' => 'page',
            'usable_id' => Page::factory(),
            'field' => 'hero.image',
        ];
    }
}
