<?php

namespace Tests\Feature;

use App\Models\AttemptAnswer;
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
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingTest extends TestCase
{
    use RefreshDatabase;

    private GradingService $grader;
    private Exam $exam;
    private ExamAttempt $attempt;
    private Question $mcqQ;
    private QuestionOption $correctOption;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->grader = app(GradingService::class);

        $faculty  = Faculty::factory()->create();
        $dept     = Department::factory()->create(['faculty_id' => $faculty->id]);
        $course   = Course::factory()->create(['department_id' => $dept->id]);
        $teacher  = User::factory()->create()->assignRole('teacher');
        $student  = User::factory()->create()->assignRole('student');
        $studentM = Student::factory()->create(['user_id' => $student->id, 'department_id' => $dept->id]);

        $bank = QuestionBank::factory()->create(['course_id' => $course->id, 'user_id' => $teacher->id]);

        $this->mcqQ = Question::factory()->create([
            'question_bank_id' => $bank->id,
            'type'  => 'mcq_single',
            'marks' => 10,
        ]);
        $this->correctOption = QuestionOption::factory()->create(['question_id' => $this->mcqQ->id, 'is_correct' => true]);
        QuestionOption::factory()->create(['question_id' => $this->mcqQ->id, 'is_correct' => false]);

        $this->exam = Exam::factory()->create([
            'course_id'    => $course->id,
            'created_by'   => $teacher->id,
            'total_marks'  => 10,
            'passing_marks' => 5,
            'status'       => 'completed',
        ]);
        $this->exam->questions()->attach($this->mcqQ->id, ['order' => 1, 'marks' => 10]);

        $this->attempt = ExamAttempt::factory()->create([
            'exam_id'    => $this->exam->id,
            'student_id' => $studentM->id,
            'status'     => 'submitted',
            'started_at' => now()->subHour(),
            'submitted_at' => now(),
        ]);
    }

    public function test_correct_mcq_answer_gets_full_marks(): void
    {
        AttemptAnswer::factory()->create([
            'attempt_id'         => $this->attempt->id,
            'question_id'        => $this->mcqQ->id,
            'selected_option_ids' => [$this->correctOption->id],
            'is_answered'        => true,
        ]);

        $result = $this->grader->gradeAttempt($this->attempt);

        $this->assertEquals(10, $result->obtained_marks);
        $this->assertEquals(100, $result->percentage);
        $this->assertTrue($result->is_pass);
    }

    public function test_wrong_mcq_answer_gets_zero(): void
    {
        $wrongOption = $this->mcqQ->options()->where('is_correct', false)->first();
        AttemptAnswer::factory()->create([
            'attempt_id'         => $this->attempt->id,
            'question_id'        => $this->mcqQ->id,
            'selected_option_ids' => [$wrongOption->id],
            'is_answered'        => true,
        ]);

        $result = $this->grader->gradeAttempt($this->attempt);

        $this->assertEquals(0, $result->obtained_marks);
        $this->assertFalse($result->is_pass);
    }

    public function test_unanswered_question_gets_zero(): void
    {
        AttemptAnswer::factory()->create([
            'attempt_id'  => $this->attempt->id,
            'question_id' => $this->mcqQ->id,
            'is_answered' => false,
        ]);

        $result = $this->grader->gradeAttempt($this->attempt);

        $this->assertEquals(0, $result->obtained_marks);
    }

    public function test_grade_calculation_returns_correct_grade(): void
    {
        $this->assertEquals(['grade' => 'A+', 'gpa' => 4.0], $this->grader->calculateGrade(95));
        $this->assertEquals(['grade' => 'A',  'gpa' => 4.0], $this->grader->calculateGrade(87));
        $this->assertEquals(['grade' => 'B+', 'gpa' => 3.5], $this->grader->calculateGrade(82));
        $this->assertEquals(['grade' => 'F',  'gpa' => 0.0], $this->grader->calculateGrade(40));
    }

    public function test_result_details_are_created(): void
    {
        AttemptAnswer::factory()->create([
            'attempt_id'         => $this->attempt->id,
            'question_id'        => $this->mcqQ->id,
            'selected_option_ids' => [$this->correctOption->id],
            'is_answered'        => true,
        ]);

        $result = $this->grader->gradeAttempt($this->attempt);

        $this->assertDatabaseHas('result_details', [
            'result_id'   => $result->id,
            'question_id' => $this->mcqQ->id,
        ]);
    }
}
