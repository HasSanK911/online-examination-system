<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $courses = Course::whereHas('teachers', fn($q) => $q->where('users.id', $user->id))
            ->withCount('students')
            ->with('department')
            ->get();

        $recentExams = Exam::where('created_by', $user->id)
            ->with('course')
            ->withCount('attempts')
            ->latest()
            ->take(6)
            ->get();

        $pendingEvaluations = ExamAttempt::whereHas('exam', fn($q) => $q->where('created_by', $user->id))
            ->whereIn('status', ['submitted', 'auto_submitted'])
            ->whereDoesntHave('result')
            ->count();

        $publishedResults = Result::whereHas('exam', fn($q) => $q->where('created_by', $user->id))
            ->whereNotNull('published_at')
            ->count();

        return Inertia::render('Dashboard/TeacherDashboard', [
            'courses'             => $courses,
            'recentExams'         => $recentExams,
            'pendingEvaluations'  => $pendingEvaluations,
            'totalStudents'       => $courses->sum('students_count'),
            'publishedResults'    => $publishedResults,
            'totalExams'          => Exam::where('created_by', $user->id)->count(),
        ]);
    }
}
