<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QuestionBankController extends Controller
{
    public function index(): Response
    {
        $banks = QuestionBank::where('user_id', Auth::id())
            ->orWhereHas('course.teachers', fn ($q) => $q->where('users.id', Auth::id()))
            ->with(['course.department', 'creator'])
            ->withCount('questions')
            ->latest()
            ->get();

        $courses = Auth::user()->taughtCourses()->with('department')->get();

        return Inertia::render('Teacher/QuestionBanks/Index', [
            'banks'   => $banks,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'course_id'   => 'required|exists:courses,id',
            'description' => 'nullable|string',
        ]);

        $bank = QuestionBank::create(array_merge($data, [
            'user_id' => Auth::id(),
        ]));

        return redirect()->route('teacher.question-banks.show', $bank)
            ->with('success', 'Question bank created successfully.');
    }

    public function show(QuestionBank $bank): Response
    {
        $bank->load([
            'course.department',
            'creator',
            'questions' => fn ($q) => $q->with('options')->latest(),
        ]);

        return Inertia::render('Teacher/QuestionBanks/Show', [
            'bank' => $bank,
        ]);
    }

    public function storeQuestion(Request $request, QuestionBank $bank): RedirectResponse
    {
        $mcqTypes = ['mcq_single', 'mcq_multiple', 'true_false'];

        $data = $request->validate([
            'type'          => 'required|in:mcq_single,mcq_multiple,true_false,fill_blank,short,descriptive',
            'question_text' => 'required|string',
            'marks'         => 'required|numeric|min:0',
            'difficulty'    => 'required|in:easy,medium,hard',
            'tags'          => 'nullable',
            'explanation'   => 'nullable|string',
            'image_path'    => 'nullable|string|max:500',
            'correct_answer' => [
                'nullable',
                'string',
                // required for fill_blank
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'fill_blank' && empty($value)) {
                        $fail('A correct answer is required for fill in the blank questions.');
                    }
                },
            ],
            'options'       => [
                'nullable',
                'array',
                // required for MCQ / true_false types
                function ($attribute, $value, $fail) use ($request, $mcqTypes) {
                    if (in_array($request->type, $mcqTypes) && empty($value)) {
                        $fail('Options are required for MCQ and True/False questions.');
                    }
                },
            ],
            'options.*.option_text' => 'required_with:options|string',
            'options.*.is_correct'  => 'required_with:options|boolean',
        ]);

        $question = $bank->questions()->create([
            'type'           => $data['type'],
            'question_text'  => $data['question_text'],
            'marks'          => $data['marks'],
            'difficulty'     => $data['difficulty'],
            'tags'           => $this->normalizeTags($data['tags'] ?? null),
            'explanation'    => $data['explanation'] ?? null,
            'image_path'     => $data['image_path'] ?? null,
            'correct_answer' => $data['correct_answer'] ?? null,
            'is_active'      => true,
        ]);

        if (in_array($data['type'], $mcqTypes) && ! empty($data['options'])) {
            foreach ($data['options'] as $index => $optionData) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct'  => $optionData['is_correct'],
                    'order'       => $index + 1,
                ]);
            }
        }

        return back()->with('success', 'Question added successfully.');
    }

    private function normalizeTags(mixed $tags): array
    {
        if (!$tags) return [];
        if (is_array($tags)) return array_values(array_filter(array_map('trim', $tags)));
        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }

    public function destroyQuestion(QuestionBank $bank, Question $question): RedirectResponse
    {
        // Ensure the question belongs to this bank
        abort_unless($question->question_bank_id === $bank->id, 403);

        $question->delete();

        return back()->with('success', 'Question deleted successfully.');
    }
}
