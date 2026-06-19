<?php

namespace Database\Factories;

use App\Models\QuestionBank;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_bank_id' => QuestionBank::factory(),
            'type'             => $this->faker->randomElement(['mcq_single', 'true_false', 'fill_blank', 'short']),
            'question_text'    => '<p>' . $this->faker->sentence() . '?</p>',
            'marks'            => $this->faker->randomElement([5, 10, 15]),
            'difficulty'       => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'tags'             => [],
            'is_active'        => true,
            'correct_answer'   => null,
        ];
    }

    public function mcqSingle(): static
    {
        return $this->state(['type' => 'mcq_single']);
    }

    public function trueFalse(): static
    {
        return $this->state(['type' => 'true_false']);
    }

    public function short(): static
    {
        return $this->state(['type' => 'short']);
    }
}
