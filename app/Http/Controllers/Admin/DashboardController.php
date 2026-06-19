<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Department;
use App\Models\Result;
use App\Models\User;
use App\Models\AuditLog;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students'   => Student::count(),
            'active_students'  => Student::active()->count(),
            'total_exams'      => Exam::count(),
            'active_exams'     => Exam::active()->count(),
            'scheduled_exams'  => Exam::upcoming()->count(),
            'total_courses'    => Course::count(),
            'total_departments' => Department::count(),
            'total_users'      => User::count(),
            'pending_results'  => Result::where('needs_evaluation', true)->whereNull('published_at')->count(),
            'published_results' => Result::whereNotNull('published_at')->count(),
        ];

        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn($log) => [
                'id'          => $log->id,
                'event'       => $log->event,
                'user'        => $log->user?->name ?? 'System',
                'description' => $log->description,
                'ip_address'  => $log->ip_address,
                'created_at'  => $log->created_at->diffForHumans(),
            ]);

        $upcomingExams = Exam::upcoming()
            ->with('course')
            ->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'id'         => $e->id,
                'title'      => $e->title,
                'course'     => $e->course->title,
                'start_time' => $e->start_time->format('M d, Y H:i'),
                'duration'   => $e->duration_minutes . ' min',
            ]);

        return Inertia::render('Dashboard/AdminDashboard', compact('stats', 'recentActivity', 'upcomingExams'));
    }
}
