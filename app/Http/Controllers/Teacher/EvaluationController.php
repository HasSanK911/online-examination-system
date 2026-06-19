<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
use App\Models\ResultDetail;
use App\Services\RankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EvaluationController extends Controller
{
    public function __construct(private RankingService $rankingService) {}

    public function index(Exam $exam): Response
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $attempts = $exam->attempts()
            ->with([
                'student.user',
                'answers.question',
                'result',
            ])
            ->whereIn('status', ['submitted', 'auto_submitted'])
            ->get()
            ->map(function ($attempt) use ($exam) {
                $pendingCount = $attempt->answers
                    ->filter(fn($a) => in_array($a->question->type ?? '', ['short', 'descriptive']))
                    ->count();
                return [
                    'id'            => $attempt->id,
                    'student'       => [
                        'name'       => $attempt->student->user->name,
                        'student_id' => $attempt->student->student_id,
                    ],
                    'submitted_at'  => $attempt->submitted_at,
                    'pending_count' => $pendingCount,
                    'result'        => $attempt->result ? [
                        'id'             => $attempt->result->id,
                        'obtained_marks' => $attempt->result->obtained_marks,
                        'total_marks'    => $attempt->result->total_marks,
                        'grade'          => $attempt->result->grade,
                        'is_evaluated'   => ! ($attempt->result->needs_evaluation ?? true),
                        'published_at'   => $attempt->result->published_at,
                    ] : null,
                ];
            });

        return Inertia::render('Teacher/Exams/Evaluate', [
            'exam'     => [
                'id'    => $exam->id,
                'title' => $exam->title,
                'course' => $exam->course ? ['title' => $exam->course->title, 'code' => $exam->course->code] : null,
                'total_marks'   => $exam->total_marks,
                'passing_marks' => $exam->passing_marks,
            ],
            'attempts' => $attempts,
        ]);
    }

    public function showAttempt(Exam $exam, $attemptId): Response
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $attempt = $exam->attempts()
            ->with(['student.user', 'answers.question.options', 'result.details'])
            ->findOrFail($attemptId);

        $manualQuestions = $attempt->answers
            ->filter(fn($a) => in_array($a->question->type ?? '', ['short', 'descriptive']))
            ->map(function ($answer) use ($attempt) {
                $detail = $attempt->result?->details
                    ->where('question_id', $answer->question_id)->first();
                return [
                    'question_id'    => $answer->question_id,
                    'question_text'  => $answer->question->question_text,
                    'type'           => $answer->question->type,
                    'max_marks'      => $answer->question->pivot->marks ?? $answer->question->marks,
                    'text_answer'    => $answer->text_answer,
                    'obtained_marks' => $detail?->obtained_marks,
                    'feedback'       => $detail?->teacher_feedback,
                ];
            })
            ->values();

        return Inertia::render('Teacher/Exams/EvaluateAttempt', [
            'exam'    => ['id' => $exam->id, 'title' => $exam->title],
            'attempt' => [
                'id'      => $attempt->id,
                'student' => ['name' => $attempt->student->user->name, 'student_id' => $attempt->student->student_id],
                'result'  => $attempt->result ? [
                    'id'             => $attempt->result->id,
                    'obtained_marks' => $attempt->result->obtained_marks,
                ] : null,
            ],
            'manual_questions' => $manualQuestions,
        ]);
    }

    public function submitEvaluation(Request $request, Exam $exam, $attemptId): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $request->validate([
            'evaluations'                       => 'required|array',
            'evaluations.*.question_id'         => 'required|integer',
            'evaluations.*.obtained_marks'      => 'required|numeric|min:0',
            'evaluations.*.feedback'            => 'nullable|string',
        ]);

        $attempt = $exam->attempts()->with('result.details')->findOrFail($attemptId);
        $result  = $attempt->result;

        if (! $result) {
            return back()->with('error', 'No result record found. Run auto-grading first.');
        }

        $bonusMarks = 0;

        foreach ($request->evaluations as $eval) {
            $detail = $result->details()->where('question_id', $eval['question_id'])->first();
            $max    = $detail?->max_marks ?? 0;
            $marks  = min((float) $eval['obtained_marks'], $max);

            if ($detail) {
                $detail->update([
                    'obtained_marks'  => $marks,
                    'teacher_feedback' => $eval['feedback'] ?? null,
                    'evaluated_by'    => Auth::id(),
                ]);
            }

            $bonusMarks += $marks;
        }

        $autoMarks = $result->details()
            ->whereHas('question', fn($q) => $q->whereNotIn('type', ['short', 'descriptive']))
            ->sum('obtained_marks');

        $totalObtained = $autoMarks + $bonusMarks;
        $percentage    = $result->total_marks > 0
            ? round($totalObtained / $result->total_marks * 100, 2)
            : 0;

        $gradeScale = config('examination.grade_scale');
        $grade = 'F'; $gpa = 0.0;
        foreach ($gradeScale as $entry) {
            if ($percentage >= $entry['min']) {
                $grade = $entry['grade'];
                $gpa   = $entry['gpa'];
                break;
            }
        }

        $result->update([
            'obtained_marks'   => $totalObtained,
            'percentage'       => $percentage,
            'grade'            => $grade,
            'gpa'              => $gpa,
            'is_pass'          => $percentage >= config('examination.passing_percentage', 50),
            'needs_evaluation' => false,
            'evaluated_at'     => now(),
        ]);

        $this->rankingService->computeExamRankings($exam->id);

        return redirect()->route('teacher.exams.evaluate', $exam->id)
            ->with('success', 'Evaluation saved.');
    }
}
