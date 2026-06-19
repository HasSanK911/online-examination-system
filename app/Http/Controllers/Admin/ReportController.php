<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Student;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'students'    => Student::with('user:id,name')->get(['id', 'user_id', 'student_id'])->map(fn($s) => [
                'id' => $s->id, 'name' => $s->user?->name ?? 'Unknown', 'student_id' => $s->student_id ?? '',
            ]),
            'exams'       => Exam::where('status', 'completed')->latest()->get(['id', 'title'])->toArray(),
            'departments' => Department::orderBy('name')->get(['id', 'name'])->toArray(),
            'courses'     => Course::where('status', 'active')->orderBy('title')->get(['id', 'title', 'code'])->toArray(),
        ]);
    }

    public function studentPdf(Student $student)
    {
        $data = $this->reportService->studentReport($student);
        $pdf  = Pdf::loadView('pdf.reports.student', $data);
        return $pdf->download("student-{$student->student_id}-report.pdf");
    }

    public function examPdf(Exam $exam)
    {
        $data = $this->reportService->examReport($exam);
        $pdf  = Pdf::loadView('pdf.reports.exam', $data);
        return $pdf->download("exam-{$exam->id}-report.pdf");
    }

    public function departmentPdf(Department $department)
    {
        $data = $this->reportService->departmentReport($department);
        $pdf  = Pdf::loadView('pdf.reports.department', $data);
        return $pdf->download("dept-{$department->id}-report.pdf");
    }

    public function coursePdf(Course $course)
    {
        $data = $this->reportService->courseReport($course);
        $pdf  = Pdf::loadView('pdf.reports.course', $data);
        return $pdf->download("course-{$course->code}-report.pdf");
    }
}
