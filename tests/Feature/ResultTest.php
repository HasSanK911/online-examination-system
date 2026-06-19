<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Faculty;
use App\Models\Result;
use App\Models\Student;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;
    private Student $student;
    private Result $result;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $faculty  = Faculty::factory()->create();
        $dept     = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course   = Course::factory()->create(['department_id' => $dept->id]);
        $teacher  = User::factory()->create()->assignRole('teacher');

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('student');
        $this->student = Student::factory()->create([
            'user_id'       => $this->studentUser->id,
            'department_id' => $dept->id,
        ]);

        $exam = Exam::factory()->create([
            'course_id'    => $course->id,
            'created_by'   => $teacher->id,
            'status'       => 'completed',
            'total_marks'  => 100,
            'passing_marks' => 50,
        ]);

        $attempt = ExamAttempt::factory()->create([
            'exam_id'      => $exam->id,
            'student_id'   => $this->student->id,
            'status'       => 'submitted',
            'started_at'   => now()->subHour(),
            'submitted_at' => now(),
        ]);

        $this->result = Result::factory()->create([
            'attempt_id'     => $attempt->id,
            'student_id'     => $this->student->id,
            'exam_id'        => $exam->id,
            'total_marks'    => 100,
            'obtained_marks' => 75,
            'percentage'     => 75,
            'grade'          => 'B',
            'gpa'            => 3.0,
            'is_pass'        => true,
            'published_at'   => now(),
        ]);
    }

    public function test_student_can_view_results_list(): void
    {
        $this->actingAs($this->studentUser)
            ->get('/student/results')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Student/Results/Index'));
    }

    public function test_student_can_view_own_result(): void
    {
        $this->actingAs($this->studentUser)
            ->get("/student/results/{$this->result->id}")
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Student/Results/Show'));
    }

    public function test_student_cannot_view_others_result(): void
    {
        $other = User::factory()->create()->assignRole('student');
        Student::factory()->create(['user_id' => $other->id, 'department_id' => $this->student->department_id]);

        $this->actingAs($other)
            ->get("/student/results/{$this->result->id}")
            ->assertStatus(403);
    }

    public function test_unpublished_result_hidden_from_student(): void
    {
        $this->result->update(['published_at' => null]);

        $this->actingAs($this->studentUser)
            ->get("/student/results/{$this->result->id}")
            ->assertStatus(403);
    }

    public function test_result_pdf_download(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get("/student/results/{$this->result->id}/download");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
