<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Faculty;
use App\Models\Result;
use App\Models\Student;
use App\Models\Course;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingServiceTest extends TestCase
{
    use RefreshDatabase;

    private RankingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->service = app(RankingService::class);
    }

    private function makeExamWithResults(array $marks): Exam
    {
        $faculty = Faculty::factory()->create();
        $dept    = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course  = Course::factory()->create(['department_id' => $dept->id]);
        $teacher = User::factory()->create()->assignRole('teacher');
        $exam    = Exam::factory()->completed()->create(['course_id' => $course->id, 'created_by' => $teacher->id]);

        foreach ($marks as $mark) {
            $user    = User::factory()->create()->assignRole('student');
            $student = Student::factory()->create(['user_id' => $user->id, 'department_id' => $dept->id]);
            $attempt = ExamAttempt::factory()->submitted()->create(['exam_id' => $exam->id, 'student_id' => $student->id]);
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

        return $exam;
    }

    public function test_compute_exam_rankings_assigns_correct_rank_order(): void
    {
        $exam = $this->makeExamWithResults([70, 90, 80]);

        $this->service->computeExamRankings($exam->id);

        $results = Result::where('exam_id', $exam->id)
            ->orderBy('obtained_marks', 'desc')
            ->get();

        $this->assertEquals(1, $results[0]->class_rank);
        $this->assertEquals(2, $results[1]->class_rank);
        $this->assertEquals(3, $results[2]->class_rank);
        $this->assertEquals(90, $results[0]->obtained_marks);
    }

    public function test_tied_scores_get_same_rank(): void
    {
        $exam = $this->makeExamWithResults([80, 80, 60]);

        $this->service->computeExamRankings($exam->id);

        $tied = Result::where('exam_id', $exam->id)
            ->where('obtained_marks', 80)
            ->get();

        $this->assertEquals(1, $tied[0]->class_rank);
        $this->assertEquals(1, $tied[1]->class_rank);

        $last = Result::where('exam_id', $exam->id)
            ->where('obtained_marks', 60)
            ->first();
        $this->assertEquals(3, $last->class_rank);
    }

    public function test_get_class_rankings_returns_all_published(): void
    {
        $exam = $this->makeExamWithResults([70, 85, 60]);

        $rankings = $this->service->getClassRankings($exam->id);

        $this->assertCount(3, $rankings);
        $this->assertEquals(85, $rankings->first()->obtained_marks);
    }

    public function test_unpublished_results_excluded_from_rankings(): void
    {
        $faculty = Faculty::factory()->create();
        $dept    = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course  = Course::factory()->create(['department_id' => $dept->id]);
        $teacher = User::factory()->create()->assignRole('teacher');
        $exam    = Exam::factory()->completed()->create(['course_id' => $course->id, 'created_by' => $teacher->id]);

        $user    = User::factory()->create()->assignRole('student');
        $student = Student::factory()->create(['user_id' => $user->id, 'department_id' => $dept->id]);
        $attempt = ExamAttempt::factory()->submitted()->create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        Result::factory()->create([
            'attempt_id'   => $attempt->id,
            'student_id'   => $student->id,
            'exam_id'      => $exam->id,
            'obtained_marks' => 90,
            'total_marks'  => 100,
            'percentage'   => 90,
            'published_at' => null,
        ]);

        $rankings = $this->service->getClassRankings($exam->id);

        $this->assertCount(0, $rankings);
    }
}
