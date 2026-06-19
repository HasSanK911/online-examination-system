<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = Course::with(['department.faculty'])
            ->withCount('students', 'teachers', 'exams')
            ->when($request->search, fn($q, $s) =>
                $q->where('title', 'like', "%$s%")->orWhere('code', 'like', "%$s%")
            )
            ->when($request->department_id, fn($q, $d) => $q->where('department_id', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Courses/Index', [
            'courses'     => $courses,
            'departments' => $departments,
            'filters'     => $request->only('search', 'department_id'),
        ]);
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
}
