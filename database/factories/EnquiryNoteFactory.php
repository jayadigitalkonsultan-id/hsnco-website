<?php

namespace Database\Factories;

use App\Models\Enquiry;
use App\Models\EnquiryNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EnquiryNote> */
class EnquiryNoteFactory extends Factory
{
    protected $model = EnquiryNote::class;

    public function definition(): array
    {
        return [
            'enquiry_id' => Enquiry::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
