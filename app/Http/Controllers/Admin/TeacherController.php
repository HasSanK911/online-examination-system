<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(Request $request): Response
    {
        $teachers = User::role('teacher')
            ->withCount('taughtCourses')
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")
            )
            ->when($request->filled('status'), fn($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters'  => $request->only('search', 'status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => ['required', 'confirmed', Password::defaults()],
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
            'role_type' => 'teacher',
        ]);

        // Admin-created teachers skip email verification — they can log in immediately.
        // (email_verified_at is not in $fillable, so it must be set explicitly.)
        $user->markEmailAsVerified();

        $user->assignRole('teacher');

        return back()->with('success', 'Teacher created successfully.');
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => "required|email|max:255|unique:users,email,{$teacher->id}",
            'password'  => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => 'boolean',
        ]);

        $teacher->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'is_active' => $data['is_active'] ?? $teacher->is_active,
        ]);

        if (! empty($data['password'])) {
            $teacher->update(['password' => Hash::make($data['password'])]);
        }

        return back()->with('success', 'Teacher updated successfully.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        $teacher->delete();
        return back()->with('success', 'Teacher deleted.');
    }
}
