<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'exam_controller', 'teacher', 'student']);
    }

    public function view(User $user, Exam $exam): bool
    {
        if ($user->hasAnyRole(['super_admin', 'exam_controller'])) {
            return true;
        }
        if ($user->hasRole('teacher')) {
            return $exam->created_by === $user->id;
        }
        if ($user->hasRole('student')) {
            return $user->student?->courses()->where('courses.id', $exam->course_id)->exists();
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'teacher']);
    }

    public function update(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super_admin')) return true;
        return $user->hasRole('teacher') && $exam->created_by === $user->id && $exam->status === 'draft';
    }

    public function delete(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super_admin')) return true;
        return $user->hasRole('teacher') && $exam->created_by === $user->id && in_array($exam->status, ['draft', 'cancelled']);
    }

    public function schedule(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['super_admin', 'exam_controller']) ||
            ($user->hasRole('teacher') && $exam->created_by === $user->id);
    }

    public function publishResults(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['super_admin', 'exam_controller']);
    }
}
