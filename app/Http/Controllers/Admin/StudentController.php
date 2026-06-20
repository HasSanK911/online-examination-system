<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // Department cards with semester breakdown (always loaded)
        $departmentGroups = Department::with('faculty')
            ->get()
            ->map(function ($dept) {
                $semesterBreakdown = Student::where('department_id', $dept->id)
                    ->select('semester', DB::raw('count(*) as count'))
                    ->groupBy('semester')
                    ->orderBy('semester')
                    ->get()
                    ->map(fn($r) => ['semester' => $r->semester, 'count' => $r->count]);

                $total  = Student::where('department_id', $dept->id)->count();
                $active = Student::where('department_id', $dept->id)->where('status', 'active')->count();

                return [
                    'id'        => $dept->id,
                    'name'      => $dept->name,
                    'code'      => $dept->code,
                    'faculty'   => $dept->faculty?->name,
                    'total'     => $total,
                    'active'    => $active,
                    'semesters' => $semesterBreakdown,
                ];
            });

        // Load paginated students only when dept + semester are both selected
        $students = null;
        $selectedDepartment = null;

        if ($request->department_id) {
            $selectedDepartment = Department::with('faculty')->find($request->department_id);
        }

        if ($request->department_id && $request->semester) {
            $students = Student::with(['user', 'department.faculty'])
                ->where('department_id', $request->department_id)
                ->where('semester', $request->semester)
                ->when($request->search, function ($q, $s) {
                    $q->where('student_id', 'like', "%$s%")
                      ->orWhere('roll_number', 'like', "%$s%")
                      ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
                })
                ->when($request->status, fn($q, $s) => $q->where('status', $s))
                ->paginate(20)
                ->withQueryString();
        }

        return Inertia::render('Admin/Students/Index', [
            'departmentGroups'   => $departmentGroups,
            'selectedDepartment' => $selectedDepartment,
            'students'           => $students,
            'departments'        => Department::all(['id', 'name', 'code']),
            'filters'            => $request->only(['search', 'department_id', 'semester', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'student_id'    => 'required|string|max:50|unique:students',
            'roll_number'   => 'required|string|max:50|unique:students',
            'department_id' => 'required|exists:departments,id',
            'semester'      => 'required|integer|min:1|max:8',
            'batch'         => 'nullable|string|max:20',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]);

            // Admin-created students skip email verification — they can log in immediately.
            // (email_verified_at is not in $fillable, so it must be set explicitly.)
            $user->markEmailAsVerified();

            $user->assignRole('student');

            Student::create([
                'user_id'       => $user->id,
                'department_id' => $data['department_id'],
                'student_id'    => $data['student_id'],
                'roll_number'   => $data['roll_number'],
                'semester'      => $data['semester'],
                'batch'         => $data['batch'] ?? null,
                'phone'         => $data['phone'] ?? null,
                'address'       => $data['address'] ?? null,
                'status'        => 'active',
            ]);
        });

        return back()->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'department.faculty', 'courses', 'results.exam.course']);
        return Inertia::render('Admin/Students/Show', ['student' => $student]);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return back()->with('success', 'Student deleted.');
    }
}
