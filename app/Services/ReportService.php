<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function studentReport(Student $student): array
    {
        $results = DB::table('student_results_view')
            ->where('student_id', $student->id)
            ->orderByDesc('published_at')
            ->get();

        $cgpa = $results->avg('gpa') ?? 0;

        return [
            'student' => [
                'name'       => $student->user->name,
                'student_id' => $student->student_id,
                'roll_number' => $student->roll_number,
                'semester'   => $student->semester,
                'department' => $student->department->name,
                'batch'      => $student->batch,
            ],
            'results' => $results->map(fn($r) => (array) $r)->toArray(),
            'summary' => [
                'total_exams'  => $results->count(),
                'passed'       => $results->where('is_pass', 1)->count(),
                'failed'       => $results->where('is_pass', 0)->count(),
                'cgpa'         => round($cgpa, 2),
                'avg_percentage' => round($results->avg('percentage'), 2),
            ],
        ];
    }

    public function examReport(Exam $exam): array
    {
        $results = $exam->results()
            ->with('student.user')
            ->whereNotNull('published_at')
            ->get();

        return [
            'exam' => [
                'title'       => $exam->title,
                'course'      => $exam->course?->title,
                'total_marks' => $exam->total_marks,
                'date'        => $exam->start_time,
            ],
            'results' => $results->map(fn($r) => [
                'student_name' => $r->student->user->name,
                'student_id'   => $r->student->student_id,
                'obtained'     => $r->obtained_marks,
                'total'        => $r->total_marks,
                'percentage'   => $r->percentage,
                'grade'        => $r->grade,
                'gpa'          => $r->gpa,
                'is_pass'      => $r->is_pass,
                'rank'         => $r->class_rank,
            ])->toArray(),
            'summary' => [
                'total'    => $results->count(),
                'passed'   => $results->where('is_pass', true)->count(),
                'failed'   => $results->where('is_pass', false)->count(),
                'avg'      => round($results->avg('percentage'), 2),
                'highest'  => $results->max('percentage'),
                'lowest'   => $results->min('percentage'),
            ],
        ];
    }

    public function departmentReport(Department $department): array
    {
        $rows = DB::table('student_results_view')
            ->where('department', $department->name)
            ->whereNotNull('published_at')
            ->get();

        return [
            'department' => $department->name,
            'faculty'    => $department->faculty?->name,
            'results'    => $rows->map(fn($r) => (array) $r)->toArray(),
            'summary'    => [
                'total_students' => $rows->pluck('student_id')->unique()->count(),
                'total_exams'    => $rows->count(),
                'passed'         => $rows->where('is_pass', 1)->count(),
                'failed'         => $rows->where('is_pass', 0)->count(),
                'avg_percentage' => round($rows->avg('percentage'), 2),
            ],
        ];
    }

    public function courseReport(Course $course): array
    {
        $rows = DB::table('student_results_view')
            ->where('course_code', $course->code)
            ->whereNotNull('published_at')
            ->get();

        return [
            'course'  => $course->title,
            'code'    => $course->code,
            'results' => $rows->map(fn($r) => (array) $r)->toArray(),
            'summary' => [
                'total'   => $rows->count(),
                'passed'  => $rows->where('is_pass', 1)->count(),
                'failed'  => $rows->where('is_pass', 0)->count(),
                'avg'     => round($rows->avg('percentage'), 2),
                'highest' => $rows->max('percentage'),
                'lowest'  => $rows->min('percentage'),
            ],
        ];
    }
}
