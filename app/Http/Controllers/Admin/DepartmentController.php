<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response
    {
        $departments = Department::with(['faculty'])
            ->withCount('students', 'courses')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->when($request->faculty_id, fn($q, $f) => $q->where('faculty_id', $f))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $faculties = Faculty::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'faculties'   => $faculties,
            'filters'     => $request->only('search', 'faculty_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id'  => 'required|exists:faculties,id',
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string',
        ]);

        Department::create($data);

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id'  => 'required|exists:faculties,id',
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($data);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();
        return back()->with('success', 'Department deleted.');
    }
}
