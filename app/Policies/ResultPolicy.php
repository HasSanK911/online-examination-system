<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\User;

class ResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'exam_controller', 'teacher', 'student']);
    }

    public function view(User $user, Result $result): bool
    {
        if ($user->hasAnyRole(['super_admin', 'exam_controller'])) return true;
        if ($user->hasRole('teacher')) {
            return $result->exam?->created_by === $user->id;
        }
        if ($user->hasRole('student')) {
            return $result->student_id === $user->student?->id
                && $result->published_at !== null;
        }
        return false;
    }

    public function publish(User $user, Result $result): bool
    {
        return $user->hasAnyRole(['super_admin', 'exam_controller']);
    }

    public function download(User $user, Result $result): bool
    {
        return $user->hasRole('student')
            && $result->student_id === $user->student?->id
            && $result->published_at !== null;
    }
}
