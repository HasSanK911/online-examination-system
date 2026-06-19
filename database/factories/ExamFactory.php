<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDay();
        return [
            'course_id'               => Course::factory(),
            'created_by'              => User::factory(),
            'title'                   => $this->faker->words(4, true) . ' Exam',
            'description'             => $this->faker->sentence(),
            'total_marks'             => 100,
            'passing_marks'           => 50,
            'duration_minutes'        => 60,
            'start_time'              => $start,
            'end_time'                => $start->copy()->addHours(2),
            'status'                  => 'scheduled',
            'allow_backtrack'         => true,
            'shuffle_questions'       => false,
            'shuffle_options'         => false,
            'show_result_immediately' => false,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status'     => 'active',
            'start_time' => now()->subMinutes(5),
            'end_time'   => now()->addHours(2),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status'     => 'completed',
            'start_time' => now()->subHours(3),
            'end_time'   => now()->subHour(),
        ]);
    }
}
