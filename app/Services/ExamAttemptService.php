<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamAttemptService
{
    public function __construct(
        private GradingService $gradingService,
        private AuditService $auditService
    ) {}

    public function startAttempt(Exam $exam, Student $student): ExamAttempt
    {
        $existing = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            // Heal an attempt that was started before the exam had questions
            // (otherwise the student is stuck on an empty paper forever).
            if (empty($existing->question_order)) {
                $order = $this->buildQuestionOrder($exam);
                $existing->update(['question_order' => $order]);

                foreach ($order as $questionId) {
                    AttemptAnswer::firstOrCreate(
                        ['attempt_id' => $existing->id, 'question_id' => $questionId],
                        ['is_answered' => false]
                    );
                }
            }

            return $existing;
        }

        $order = $this->buildQuestionOrder($exam);

        $attempt = ExamAttempt::create([
            'exam_id'        => $exam->id,
            'student_id'     => $student->id,
            'started_at'     => now(),
            'status'         => 'in_progress',
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'question_order' => $order,
        ]);

        foreach ($order as $questionId) {
            AttemptAnswer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $questionId,
                'is_answered' => false,
            ]);
        }

        $this->auditService->log('exam_start', $attempt);

        return $attempt;
    }

    private function buildQuestionOrder(Exam $exam): array
    {
        $questions = $exam->questions;

        return $exam->shuffle_questions
            ? $questions->shuffle()->pluck('id')->toArray()
            : $questions->pluck('id')->toArray();
    }

    public function saveAnswer(ExamAttempt $attempt, int $questionId, array $data): AttemptAnswer
    {
        return AttemptAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
            [
                'selected_option_ids'  => $data['selected_option_ids'] ?? null,
                'text_answer'          => $data['text_answer'] ?? null,
                'is_marked_for_review' => $data['is_marked_for_review'] ?? false,
                'is_answered'          => $data['is_answered'] ?? false,
                'saved_at'             => now(),
            ]
        );
    }

    public function submitAttempt(ExamAttempt $attempt, bool $isAuto = false): void
    {
        DB::transaction(function () use ($attempt, $isAuto) {
            $elapsed = now()->diffInSeconds(Carbon::parse($attempt->started_at));

            $attempt->update([
                'submitted_at'      => now(),
                'status'            => $isAuto ? 'auto_submitted' : 'submitted',
                'time_spent_seconds' => $elapsed,
            ]);

            $result = $this->gradingService->gradeAttempt($attempt);

            $exam = $attempt->exam;
            if ($exam->show_result_immediately) {
                $result->update(['published_at' => now()]);
            }

            $this->auditService->log($isAuto ? 'exam_auto_submitted' : 'exam_submit', $attempt);
        });
    }

    public function logSuspiciousActivity(ExamAttempt $attempt, string $type): void
    {
        $attempt->increment('suspicious_activity_count');

        if ($type === 'tab_switch') {
            $attempt->increment('tab_switch_count');
            $threshold = config('examination.tab_switch_submit_threshold');
            if ($attempt->tab_switch_count >= $threshold) {
                $this->submitAttempt($attempt, true);
            }
        }

        $this->auditService->log('suspicious_activity', $attempt, ['new' => ['type' => $type]]);
    }
}
