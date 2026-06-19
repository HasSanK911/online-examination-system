<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id'                   => Exam::factory(),
            'student_id'                => Student::factory(),
            'started_at'                => now()->subHour(),
            'submitted_at'              => null,
            'status'                    => 'in_progress',
            'ip_address'                => $this->faker->ipv4(),
            'user_agent'                => $this->faker->userAgent(),
            'tab_switch_count'          => 0,
            'suspicious_activity_count' => 0,
            'time_spent_seconds'        => null,
            'question_order'            => [],
        ];
    }

    public function submitted(): static
    {
        return $this->state([
            'status'       => 'submitted',
            'submitted_at' => now(),
            'time_spent_seconds' => 3600,
        ]);
    }
}
