<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Exam;
use App\Models\Faculty;
use App\Models\Result;
use App\Models\Student;
use App\Models\Course;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    private RankingService $ranker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->ranker = app(RankingService::class);
    }

    public function test_compute_exam_rankings_assigns_class_ranks(): void
    {
        $faculty  = Faculty::factory()->create();
        $dept     = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course   = Course::factory()->create(['department_id' => $dept->id]);
        $teacher  = User::factory()->create()->assignRole('teacher');

        $exam = Exam::factory()->completed()->create(['course_id' => $course->id, 'created_by' => $teacher->id]);

        $students = User::factory()->count(3)->create()->map(function ($u) use ($dept) {
            $u->assignRole('student');
            return Student::factory()->create(['user_id' => $u->id, 'department_id' => $dept->id]);
        });

        $marks = [90, 75, 60];
        $results = [];
        foreach ($students as $i => $student) {
            $attempt = \App\Models\ExamAttempt::factory()->submitted()->create([
                'exam_id'    => $exam->id,
                'student_id' => $student->id,
            ]);
            $results[] = Result::factory()->create([
                'attempt_id'     => $attempt->id,
                'student_id'     => $student->id,
                'exam_id'        => $exam->id,
                'obtained_marks' => $marks[$i],
                'total_marks'    => 100,
                'percentage'     => $marks[$i],
                'published_at'   => now(),
            ]);
        }

        $this->ranker->computeExamRankings($exam->id);

        $topResult = Result::where('exam_id', $exam->id)->where('obtained_marks', 90)->first();
        $this->assertEquals(1, $topResult->class_rank);

        $secondResult = Result::where('exam_id', $exam->id)->where('obtained_marks', 75)->first();
        $this->assertEquals(2, $secondResult->class_rank);
    }

    public function test_get_class_rankings_returns_ordered_results(): void
    {
        $faculty  = Faculty::factory()->create();
        $dept     = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course   = Course::factory()->create(['department_id' => $dept->id]);
        $teacher  = User::factory()->create()->assignRole('teacher');
        $exam     = Exam::factory()->completed()->create(['course_id' => $course->id, 'created_by' => $teacher->id]);

        $students = User::factory()->count(2)->create()->map(function ($u) use ($dept) {
            $u->assignRole('student');
            return Student::factory()->create(['user_id' => $u->id, 'department_id' => $dept->id]);
        });

        foreach ($students->zip([85, 70]) as [$student, $mark]) {
            $attempt = \App\Models\ExamAttempt::factory()->submitted()->create([
                'exam_id'    => $exam->id,
                'student_id' => $student->id,
            ]);
            Result::factory()->create([
                'attempt_id'     => $attempt->id,
                'student_id'     => $student->id,
                'exam_id'        => $exam->id,
                'obtained_marks' => $mark,
                'total_marks'    => 100,
                'percentage'     => $mark,
                'published_at'   => now(),
            ]);
        }

        $rankings = $this->ranker->getClassRankings($exam->id);

        $this->assertCount(2, $rankings);
        $this->assertEquals(85, $rankings->first()->obtained_marks);
        $this->assertEquals(1, $rankings->first()->class_rank);
    }
}
