<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OlapService;
use App\Services\RankingService;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private OlapService $olapService,
        private RankingService $rankingService
    ) {}

    public function index()
    {
        return Inertia::render('Analytics/OlapDashboard', [
            'stats'                 => $this->olapService->getOverviewStats(),
            'monthly_trend'         => $this->olapService->getMonthlyTrend(),
            'grade_distribution'    => $this->olapService->getGradeDistribution(),
            'department_performance' => $this->olapService->getDepartmentPerformance(),
            'course_performance'    => $this->olapService->getCoursePerformance(),
            'semester_matrix'       => $this->olapService->getSemesterMatrix(),
            'top_students'          => $this->rankingService->getTopStudentsPerCourse(5),
        ]);
    }
}
