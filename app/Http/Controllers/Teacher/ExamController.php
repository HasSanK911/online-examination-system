<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Notifications\ExamScheduledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function index(): Response
    {
        $exams = Exam::with(['course.department', 'creator'])
            ->where('created_by', Auth::id())
            ->withCount(['questions', 'attempts', 'results'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Teacher/Exams/Index', [
            'exams' => $exams,
        ]);
    }

    public function create(): Response
    {
        $courses = Auth::user()->taughtCourses()->with('department')->get();

        return Inertia::render('Teacher/Exams/Create', [
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id'               => 'required|exists:courses,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'instructions'            => 'nullable|string',
            'total_marks'             => 'required|numeric|min:1',
            'passing_marks'           => 'required|numeric|min:0',
            'duration_minutes'        => 'required|integer|min:5|max:300',
            'start_time'              => 'required|date|after:now',
            'end_time'                => 'required|date|after:start_time',
            'shuffle_questions'       => 'boolean',
            'shuffle_options'         => 'boolean',
            'allow_backtrack'         => 'boolean',
            'show_result_immediately' => 'boolean',
        ]);

        $exam = Exam::create(array_merge($data, [
            'created_by' => Auth::id(),
            'status'     => 'draft',
        ]));

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Exam created successfully. Add questions to get started.');
    }

    public function show(Exam $exam): Response
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $exam->load([
            'course.department',
            'questions' => fn ($q) => $q->withPivot(['order', 'marks'])->orderByPivot('order'),
            'questions.options',
            'attempts.student.user',
        ]);

        return Inertia::render('Teacher/Exams/Show', [
            'exam' => $exam,
        ]);
    }

    public function edit(Exam $exam): Response
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $courses = Auth::user()->taughtCourses()->with('department')->get();

        return Inertia::render('Teacher/Exams/Create', [
            'exam'    => $exam,
            'courses' => $courses,
        ]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $data = $request->validate([
            'course_id'               => 'required|exists:courses,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'instructions'            => 'nullable|string',
            'total_marks'             => 'required|numeric|min:1',
            'passing_marks'           => 'required|numeric|min:0',
            'duration_minutes'        => 'required|integer|min:5|max:300',
            'start_time'              => 'required|date',
            'end_time'                => 'required|date|after:start_time',
            'shuffle_questions'       => 'boolean',
            'shuffle_options'         => 'boolean',
            'allow_backtrack'         => 'boolean',
            'show_result_immediately' => 'boolean',
        ]);

        $exam->update($data);

        return back()->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $exam->delete();

        return redirect()->route('teacher.exams.index')
            ->with('success', 'Exam deleted successfully.');
    }

    public function manageQuestions(Exam $exam): Response
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $exam->load([
            'questions' => fn ($q) => $q->withPivot(['order', 'marks'])->orderByPivot('order'),
            'questions.options',
            'course',
        ]);

        $questionBanks = QuestionBank::with([
            'questions' => fn ($q) => $q->with('options')->where('is_active', true),
        ])
            ->where('course_id', $exam->course_id)
            ->get();

        return Inertia::render('Teacher/Exams/ManageQuestions', [
            'exam'          => $exam,
            'questionBanks' => $questionBanks,
        ]);
    }

    public function addQuestion(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $request->validate([
            'question_ids'   => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $maxOrder = $exam->questions()->max('exam_questions.order') ?? 0;

        $attach = [];
        foreach ($request->question_ids as $index => $questionId) {
            $question = Question::findOrFail($questionId);
            $attach[$questionId] = [
                'order' => $maxOrder + $index + 1,
                'marks' => $question->marks,
            ];
        }

        $exam->questions()->syncWithoutDetaching($attach);

        return back()->with('success', 'Questions added to exam.');
    }

    public function removeQuestion(Exam $exam, Question $question): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $exam->questions()->detach($question->id);

        return back()->with('success', 'Question removed from exam.');
    }

    public function updateQuestionMark(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $request->validate([
            'marks' => 'required|numeric|min:0',
        ]);

        $exam->questions()->updateExistingPivot($question->id, [
            'marks' => $request->marks,
        ]);

        return back()->with('success', 'Question marks updated.');
    }

    public function updateStatus(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $request->validate([
            'status' => 'required|in:draft,scheduled,active,completed,cancelled',
        ]);

        $exam->update(['status' => $request->status]);

        if ($request->status === 'scheduled') {
            $students = $exam->course->students()->with('user')->get();
            foreach ($students as $student) {
                $student->user->notify(new ExamScheduledNotification($exam));
            }
        }

        return back()->with('success', 'Exam status updated to ' . $request->status . '.');
    }

    public function publish(Exam $exam): RedirectResponse
    {
        abort_unless($exam->created_by === Auth::id(), 403);

        $exam->update([
            'status'       => 'scheduled',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Exam has been scheduled and published.');
    }
}
