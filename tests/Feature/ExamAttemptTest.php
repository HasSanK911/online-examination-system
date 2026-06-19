<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Faculty;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAttemptTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;
    private Student $student;
    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $faculty = Faculty::factory()->create();
        $dept    = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course  = Course::factory()->create(['department_id' => $dept->id]);

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('student');
        $this->student = Student::factory()->create([
            'user_id'       => $this->studentUser->id,
            'department_id' => $dept->id,
        ]);
        $course->students()->attach($this->student->id);

        $bank = QuestionBank::factory()->create([
            'course_id' => $course->id,
            'user_id'   => $teacher->id,
        ]);

        $question = Question::factory()->create([
            'question_bank_id' => $bank->id,
            'type'             => 'mcq_single',
            'marks'            => 10,
        ]);
        QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        $this->exam = Exam::factory()->create([
            'course_id'      => $course->id,
            'created_by'     => $teacher->id,
            'status'         => 'active',
            'start_time'     => now()->subMinute(),
            'end_time'       => now()->addHour(),
            'duration_minutes' => 60,
            'total_marks'    => 10,
            'passing_marks'  => 5,
        ]);
        $this->exam->questions()->attach($question->id, ['order' => 1, 'marks' => 10]);
    }

    public function test_student_can_view_exam_list(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get('/student/exams');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Student/Exams/Index'));
    }

    public function test_student_can_start_exam_attempt(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get("/student/exams/{$this->exam->id}/attempt");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Student/Exams/Attempt'));

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id'    => $this->exam->id,
            'student_id' => $this->student->id,
            'status'     => 'in_progress',
        ]);
    }

    public function test_student_cannot_start_exam_twice(): void
    {
        $this->actingAs($this->studentUser)->get("/student/exams/{$this->exam->id}/attempt");
        $this->actingAs($this->studentUser)->get("/student/exams/{$this->exam->id}/attempt");

        $this->assertCount(1, ExamAttempt::where('exam_id', $this->exam->id)->get());
    }

    public function test_save_answer_updates_attempt_answer(): void
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id'    => $this->exam->id,
            'student_id' => $this->student->id,
            'status'     => 'in_progress',
        ]);

        $question = $this->exam->questions()->first();
        $option   = $question->options()->where('is_correct', true)->first();

        $response = $this->actingAs($this->studentUser)
            ->postJson("/api/exam/{$attempt->id}/save-answer", [
                'question_id'       => $question->id,
                'selected_option_ids' => [$option->id],
                'is_answered'       => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
            'is_answered' => true,
        ]);
    }

    public function test_submit_attempt_creates_result(): void
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id'    => $this->exam->id,
            'student_id' => $this->student->id,
            'status'     => 'in_progress',
            'started_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($this->studentUser)
            ->postJson("/api/exam/{$attempt->id}/submit");

        $response->assertStatus(200);

        $attempt->refresh();
        $this->assertEquals('submitted', $attempt->status);
        $this->assertNotNull($attempt->submitted_at);

        $this->assertDatabaseHas('results', [
            'attempt_id' => $attempt->id,
            'student_id' => $this->student->id,
            'exam_id'    => $this->exam->id,
        ]);
    }

    public function test_log_activity_increments_tab_switch_count(): void
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id'    => $this->exam->id,
            'student_id' => $this->student->id,
            'status'     => 'in_progress',
        ]);

        $this->actingAs($this->studentUser)
            ->postJson("/api/exam/{$attempt->id}/log-activity", ['type' => 'tab_switch'])
            ->assertStatus(200);

        $attempt->refresh();
        $this->assertEquals(1, $attempt->tab_switch_count);
    }

    public function test_guest_cannot_access_exam(): void
    {
        $this->get('/student/exams')->assertRedirect('/login');
    }

    public function test_student_cannot_attempt_inactive_exam(): void
    {
        $this->exam->update(['status' => 'draft']);

        $response = $this->actingAs($this->studentUser)
            ->get("/student/exams/{$this->exam->id}/attempt");

        $response->assertStatus(403);
    }
}
