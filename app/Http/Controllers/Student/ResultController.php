<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $results = Result::where('student_id', $student->id)
            ->whereNotNull('published_at')
            ->with(['exam.course.department'])
            ->latest('published_at')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'exam_title'     => $r->exam->title,
                'course'         => $r->exam->course->title,
                'course_code'    => $r->exam->course->code,
                'obtained_marks' => $r->obtained_marks,
                'total_marks'    => $r->total_marks,
                'percentage'     => $r->percentage,
                'grade'          => $r->grade,
                'gpa'            => $r->gpa,
                'is_pass'        => $r->is_pass,
                'class_rank'     => $r->class_rank,
                'published_at'   => $r->published_at->format('M d, Y'),
            ]);

        return Inertia::render('Student/Results/Index', ['results' => $results]);
    }

    public function show(Result $result)
    {
        abort_if($result->student_id !== Auth::user()->student?->id, 403);
        $result->load(['exam.course.department', 'details.question', 'student.user', 'student.department.faculty']);

        return Inertia::render('Student/Results/Show', ['result' => $result]);
    }

    public function downloadCard(Result $result)
    {
        abort_if($result->student_id !== Auth::user()->student?->id, 403);
        $result->load(['exam.course.department.faculty', 'student.user', 'student.department.faculty']);

        $pdf = Pdf::loadView('pdf.result-card', ['result' => $result])
            ->setPaper('a4', 'portrait');

        return $pdf->download("result-card-{$result->student->student_id}-{$result->exam->id}.pdf");
    }
}
