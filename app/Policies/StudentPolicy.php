<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'exam_controller']);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['super_admin', 'exam_controller'])) return true;
        return $user->hasRole('student') && $student->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('super_admin');
    }
}
