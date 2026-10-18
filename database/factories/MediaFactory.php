<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'disk' => 'public',
            'path' => 'images/'.fake()->uuid().'.jpg',
            'original_name' => 'illustration.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'visibility' => 'public',
        ];
    }
}
