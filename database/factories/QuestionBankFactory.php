<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionBankFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id'   => Course::factory(),
            'user_id'     => User::factory(),
            'title'       => $this->faker->words(3, true) . ' Bank',
            'description' => $this->faker->sentence(),
        ];
    }
}
