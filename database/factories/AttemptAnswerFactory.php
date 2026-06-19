<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttemptAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attempt_id'           => ExamAttempt::factory(),
            'question_id'          => Question::factory(),
            'selected_option_ids'  => null,
            'text_answer'          => null,
            'is_marked_for_review' => false,
            'is_answered'          => false,
            'saved_at'             => null,
        ];
    }
}
