<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultFactory extends Factory
{
    public function definition(): array
    {
        $obtained = $this->faker->numberBetween(40, 100);
        $total    = 100;
        $pct      = round($obtained / $total * 100, 2);

        return [
            'attempt_id'       => ExamAttempt::factory(),
            'student_id'       => Student::factory(),
            'exam_id'          => Exam::factory(),
            'total_marks'      => $total,
            'obtained_marks'   => $obtained,
            'percentage'       => $pct,
            'grade'            => $pct >= 90 ? 'A+' : ($pct >= 80 ? 'B+' : ($pct >= 70 ? 'C+' : 'D')),
            'gpa'              => $pct >= 90 ? 4.0 : ($pct >= 80 ? 3.5 : ($pct >= 70 ? 2.5 : 1.0)),
            'is_pass'          => $pct >= 50,
            'needs_evaluation' => false,
            'evaluated_at'     => now(),
            'published_at'     => now(),
        ];
    }
}
