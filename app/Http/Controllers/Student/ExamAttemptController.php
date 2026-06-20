<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExamAttemptController extends Controller
{
    public function __construct(private ExamAttemptService $attemptService) {}

    public function index()
    {
        $student      = Auth::user()->student;
        $courseIds    = $student->courses()->pluck('courses.id');
        $now          = now();

        $upcomingExams = Exam::whereIn('course_id', $courseIds)
            ->where('status', 'scheduled')
            ->where('start_time', '>', $now)
            ->with('course')
            ->orderBy('start_time')
            ->get();

        $activeExams = Exam::whereIn('course_id', $courseIds)
            ->live()
            ->with('course')
            ->get();

        $attemptedIds = ExamAttempt::where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'auto_submitted'])
            ->pluck('exam_id');

        return Inertia::render('Student/Exams/Index', [
            'upcoming_exams' => $upcomingExams,
            'active_exams'   => $activeExams,
            'attempted_ids'  => $attemptedIds,
        ]);
    }

    public function start(Exam $exam)
    {
        $student = Auth::user()->student;

        abort_if(! $exam->isLive(), 403, 'This exam is not currently active.');

        if ($exam->questions()->count() === 0) {
            return back()->with('error', 'This exam has no questions yet. Please contact your teacher.');
        }

        // Reflect that the exam is now running (no scheduler flips this for us).
        if ($exam->status === 'scheduled') {
            $exam->update(['status' => 'active']);
        }

        $attempt = $this->attemptService->startAttempt($exam, $student);
        $exam->load(['questions.options' => fn($q) => $q->orderBy('order')]);

        $orderedQuestions = collect($attempt->question_order)
            ->map(fn($id) => $exam->questions->firstWhere('id', $id))
            ->filter()
            ->values();

        $answers = $attempt->answers()->get()->keyBy('question_id');

        return Inertia::render('Student/Exams/Attempt', [
            'attempt'    => $attempt,
            'exam'       => $exam->only(['id', 'title', 'total_marks', 'duration_minutes', 'allow_backtrack', 'instructions']),
            'questions'  => $orderedQuestions->map(fn($q) => [
                'id'           => $q->id,
                'type'         => $q->type,
                'question_text' => $q->question_text,
                'marks'        => $q->pivot->marks ?? $q->marks,
                'options'      => $q->type !== 'descriptive' && $q->type !== 'short' ? $q->options->map(fn($o) => ['id' => $o->id, 'text' => $o->option_text]) : [],
            ]),
            'saved_answers' => $answers,
            'started_at'   => $attempt->started_at->toISOString(),
            'time_limit'   => $exam->duration_minutes * 60,
        ]);
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt)
    {
        abort_if($attempt->student_id !== Auth::user()->student?->id, 403);
        abort_if($attempt->status !== 'in_progress', 422, 'Attempt already submitted.');

        $data = $request->validate([
            'question_id'          => 'required|integer',
            'selected_option_ids'  => 'nullable|array',
            'text_answer'          => 'nullable|string|max:10000',
            'is_marked_for_review' => 'boolean',
            'is_answered'          => 'boolean',
        ]);

        $answer = $this->attemptService->saveAnswer($attempt, $data['question_id'], $data);

        return response()->json(['saved' => true, 'saved_at' => $answer->saved_at]);
    }

    public function submit(Request $request, ExamAttempt $attempt)
    {
        abort_if($attempt->student_id !== Auth::user()->student?->id, 403);
        abort_if($attempt->status !== 'in_progress', 422, 'Attempt already submitted.');

        $this->attemptService->submitAttempt($attempt);

        return response()->json(['submitted' => true]);
    }

    public function logActivity(Request $request, ExamAttempt $attempt)
    {
        abort_if($attempt->student_id !== Auth::user()->student?->id, 403);

        $data = $request->validate(['type' => 'required|string|max:50']);
        $this->attemptService->logSuspiciousActivity($attempt, $data['type']);

        return response()->json(['logged' => true]);
    }
}
