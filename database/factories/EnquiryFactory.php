<?php

namespace Database\Factories;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Enquiry> */
class EnquiryFactory extends Factory
{
    protected $model = Enquiry::class;

    public function definition(): array
    {
        return [
            'submission_key' => strtolower((string) Str::ulid()),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'area_label' => 'Lainnya',
            'message' => fake()->paragraph(),
            'source' => 'contact',
            'status' => 'new',
        ];
    }
}
