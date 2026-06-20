<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = Course::with(['department.faculty', 'teachers:id,name,email'])
            ->withCount('students', 'teachers', 'exams')
            ->when($request->search, fn($q, $s) =>
                $q->where('title', 'like', "%$s%")->orWhere('code', 'like', "%$s%")
            )
            ->when($request->department_id, fn($q, $d) => $q->where('department_id', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);

        $teachers = User::role('teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Courses/Index', [
            'courses'     => $courses,
            'departments' => $departments,
            'teachers'    => $teachers,
            // Resolved only when a course is being managed (e.g. partial reload) — null otherwise.
            'manageStudents' => fn () => $request->manage_course
                ? $this->manageStudentsData((int) $request->manage_course)
                : null,
            'filters'     => $request->only('search', 'department_id'),
        ]);
    }

    /**
     * Build the enrollment picker payload for a course: every student in the
     * course's department, flagged with their current enrollment state.
     */
    private function manageStudentsData(int $courseId): ?array
    {
        $course = Course::find($courseId);
        if (! $course) {
            return null;
        }

        $enrolledIds = $course->students()->pluck('students.id')->map(fn ($id) => (int) $id)->all();

        $students = Student::with('user:id,name')
            ->where('department_id', $course->department_id)
            ->orderBy('semester')
            ->orderBy('student_id')
            ->get(['id', 'user_id', 'student_id', 'roll_number', 'semester']);

        return [
            'course_id'    => $course->id,
            'enrolled_ids' => $enrolledIds,
            'students'     => $students->map(fn ($s) => [
                'id'          => $s->id,
                'student_id'  => $s->student_id,
                'roll_number' => $s->roll_number,
                'semester'    => $s->semester,
                'name'        => $s->user?->name,
            ]),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title'         => 'required|string|max:255',
            'code'          => 'required|string|max:30|unique:courses,code',
            'credit_hours'  => 'required|integer|min:1|max:6',
            'semester'      => 'required|integer|min:1|max:8',
            'status'        => 'in:active,inactive',
        ]);

        Course::create(array_merge($data, ['status' => $data['status'] ?? 'active']));

        return back()->with('success', 'Course created.');
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title'         => 'required|string|max:255',
            'code'          => 'required|string|max:30|unique:courses,code,' . $course->id,
            'credit_hours'  => 'required|integer|min:1|max:6',
            'semester'      => 'required|integer|min:1|max:8',
            'status'        => 'in:active,inactive',
        ]);

        $course->update($data);

        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();
        return back()->with('success', 'Course deleted.');
    }

    public function assignTeacher(Request $request, Course $course): RedirectResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $course->teachers()->syncWithoutDetaching([$request->user_id]);
        return back()->with('success', 'Teacher assigned.');
    }

    public function removeTeacher(Course $course, User $user): RedirectResponse
    {
        $course->teachers()->detach($user->id);
        return back()->with('success', 'Teacher removed.');
    }

    public function syncStudents(Request $request, Course $course): RedirectResponse
    {
        $request->validate([
            'student_ids'   => 'array',
            'student_ids.*' => 'exists:students,id',
        ]);

        // Only touch students belonging to this course's department, so we never
        // disturb any enrollment that lives outside the picker the admin saw.
        $deptStudentIds = Student::where('department_id', $course->department_id)
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        $selected = array_values(array_intersect(
            array_map('intval', $request->input('student_ids', [])),
            $deptStudentIds
        ));

        $course->students()->syncWithoutDetaching($selected);
        $course->students()->detach(array_diff($deptStudentIds, $selected));

        return back()->with('success', 'Course enrollment updated.');
    }
}
