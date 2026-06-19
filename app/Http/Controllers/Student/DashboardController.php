<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $student = $user->student;

        if (!$student) {
            return Inertia::render('Dashboard/StudentDashboard', [
                'upcomingExams' => [],
                'recentResults' => [],
                'stats'         => ['total_exams' => 0, 'passed' => 0, 'avg_score' => 0, 'rank' => '—'],
            ]);
        }

        // Upcoming exams for courses the student is enrolled in
        $upcomingExams = Exam::whereHas('course.students', fn($q) => $q->where('students.id', $student->id))
            ->whereIn('status', ['scheduled', 'active'])
            ->with('course')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Recent published results
        $recentResults = Result::where('student_id', $student->id)
            ->whereNotNull('published_at')
            ->with('exam.course')
            ->latest('published_at')
            ->take(5)
            ->get();

        $allResults = Result::where('student_id', $student->id)->whereNotNull('published_at')->get();

        $stats = [
            'total_exams' => $allResults->count(),
            'passed'      => $allResults->where('is_pass', true)->count(),
            'avg_score'   => $allResults->count() ? round($allResults->avg('percentage'), 1) : 0,
            'rank'        => $student->class_rank ?? '—',
        ];

        return Inertia::render('Dashboard/StudentDashboard', [
            'student'       => $student->load('department'),
            'upcomingExams' => $upcomingExams,
            'recentResults' => $recentResults,
            'stats'         => $stats,
        ]);
    }
}
