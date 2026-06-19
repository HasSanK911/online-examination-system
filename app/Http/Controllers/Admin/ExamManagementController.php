<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Result;
use App\Notifications\ResultPublishedNotification;
use App\Services\RankingService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamManagementController extends Controller
{
    public function __construct(
        private RankingService $rankingService,
        private AuditService $auditService
    ) {}

    public function index(Request $request)
    {
        $exams = Exam::with(['course.department', 'creator'])
            ->withCount('attempts', 'results')
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->course_id, fn($q, $id) => $q->where('course_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Exams/Index', [
            'exams'   => $exams,
            'courses' => Course::active()->with('department')->get(['id', 'title', 'code', 'department_id']),
            'filters' => $request->only(['search', 'status', 'course_id']),
        ]);
    }

    public function show(Exam $exam)
    {
        $exam->load(['course.department', 'creator', 'questions.options']);
        $rankings = $this->rankingService->getClassRankings($exam->id);

        return Inertia::render('Admin/Exams/Show', [
            'exam'     => $exam,
            'rankings' => $rankings,
        ]);
    }

    public function publishResults(Exam $exam)
    {
        $unpublished = $exam->results()->whereNull('published_at')->get();

        foreach ($unpublished as $result) {
            $result->update(['published_at' => now()]);
            $result->load('student.user');
            $result->student->user->notify(new ResultPublishedNotification($result));
        }

        $this->rankingService->computeExamRankings($exam->id);
        $this->rankingService->computeDepartmentRankings($exam->id);
        $this->rankingService->computeSemesterRankings($exam->id);

        $this->auditService->log('result_publish', $exam, ['description' => "Published {$unpublished->count()} results"]);

        return back()->with('success', 'Results published and rankings computed.');
    }
}
