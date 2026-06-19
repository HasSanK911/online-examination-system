<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FacultyController extends Controller
{
    public function index(Request $request)
    {
        $faculties = Faculty::withCount('departments')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Faculties/Index', [
            'faculties' => $faculties,
            'filters'   => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:faculties',
            'dean_name' => 'nullable|string|max:255',
            'email'     => 'nullable|email',
            'phone'     => 'nullable|string|max:30',
            'status'    => 'required|in:active,inactive',
        ]);

        Faculty::create($data);

        return back()->with('success', 'Faculty created successfully.');
    }

    public function update(Request $request, Faculty $faculty)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => "required|string|max:20|unique:faculties,code,{$faculty->id}",
            'dean_name' => 'nullable|string|max:255',
            'email'     => 'nullable|email',
            'phone'     => 'nullable|string|max:30',
            'status'    => 'required|in:active,inactive',
        ]);

        $faculty->update($data);

        return back()->with('success', 'Faculty updated successfully.');
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();
        return back()->with('success', 'Faculty deleted.');
    }
}
