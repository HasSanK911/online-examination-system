<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\Result;
use App\Models\ResultDetail;
use App\Models\Question;
use App\Models\AttemptAnswer;
use Illuminate\Support\Facades\DB;

class GradingService
{
    public function gradeAttempt(ExamAttempt $attempt): Result
    {
        return DB::transaction(function () use ($attempt) {
            $exam      = $attempt->exam()->with('questions.options')->first();
            $answers   = $attempt->answers()->with('question.options')->get()->keyBy('question_id');
            $needsEval = false;
            $obtained  = 0;

            $details = [];
            foreach ($exam->questions as $question) {
                $answer = $answers->get($question->id);
                $maxMarks = $question->pivot->marks ?? $question->marks;

                if ($question->isAutoGraded()) {
                    $earned = $this->autoGrade($question, $answer);
                } else {
                    $earned    = 0;
                    $needsEval = true;
                }

                $obtained += $earned;
                $details[] = [
                    'question_id'   => $question->id,
                    'obtained_marks' => $earned,
                    'max_marks'      => $maxMarks,
                    'is_correct'     => $question->isAutoGraded() ? ($earned >= $maxMarks) : null,
                ];
            }

            $percentage = $exam->total_marks > 0
                ? round(($obtained / $exam->total_marks) * 100, 2)
                : 0;

            $gradeInfo = $this->calculateGrade($percentage);

            $result = Result::updateOrCreate(
                ['attempt_id' => $attempt->id],
                [
                    'student_id'       => $attempt->student_id,
                    'exam_id'          => $attempt->exam_id,
                    'total_marks'      => $exam->total_marks,
                    'obtained_marks'   => $obtained,
                    'percentage'       => $percentage,
                    'grade'            => $gradeInfo['grade'],
                    'gpa'              => $gradeInfo['gpa'],
                    'is_pass'          => $percentage >= config('examination.passing_percentage'),
                    'needs_evaluation' => $needsEval,
                    'evaluated_at'     => $needsEval ? null : now(),
                ]
            );

            $result->details()->delete();
            $result->details()->createMany($details);

            return $result;
        });
    }

    private function autoGrade(Question $question, ?AttemptAnswer $answer): float
    {
        if (! $answer || ! $answer->is_answered) {
            return 0;
        }

        $maxMarks = $question->marks;

        return match ($question->type) {
            'mcq_single'  => $this->gradeMcqSingle($question, $answer, $maxMarks),
            'mcq_multiple' => $this->gradeMcqMultiple($question, $answer, $maxMarks),
            'true_false'  => $this->gradeTrueFalse($question, $answer, $maxMarks),
            'fill_blank'  => $this->gradeFillBlank($question, $answer, $maxMarks),
            default       => 0,
        };
    }

    private function gradeMcqSingle(Question $question, AttemptAnswer $answer, float $maxMarks): float
    {
        $correctId = $question->options()->where('is_correct', true)->value('id');
        $selected  = $answer->selected_option_ids[0] ?? null;
        return ($selected && (int) $selected === (int) $correctId) ? $maxMarks : 0;
    }

    private function gradeMcqMultiple(Question $question, AttemptAnswer $answer, float $maxMarks): float
    {
        $correctIds  = $question->options()->where('is_correct', true)->pluck('id')->map(fn($id) => (int)$id)->sort()->values()->toArray();
        $selectedIds = collect($answer->selected_option_ids ?? [])->map(fn($id) => (int)$id)->sort()->values()->toArray();
        return ($correctIds === $selectedIds) ? $maxMarks : 0;
    }

    private function gradeTrueFalse(Question $question, AttemptAnswer $answer, float $maxMarks): float
    {
        $correctId = $question->options()->where('is_correct', true)->value('id');
        $selected  = $answer->selected_option_ids[0] ?? null;
        return ($selected && (int) $selected === (int) $correctId) ? $maxMarks : 0;
    }

    private function gradeFillBlank(Question $question, AttemptAnswer $answer, float $maxMarks): float
    {
        $correct  = strtolower(trim($question->correct_answer ?? ''));
        $provided = strtolower(trim($answer->text_answer ?? ''));
        return ($correct && $correct === $provided) ? $maxMarks : 0;
    }

    public function calculateGrade(float $percentage): array
    {
        foreach (config('examination.grade_scale') as $grade) {
            if ($percentage >= $grade['min']) {
                return ['grade' => $grade['grade'], 'gpa' => $grade['gpa']];
            }
        }
        return ['grade' => 'F', 'gpa' => 0.00];
    }
}
