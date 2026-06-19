<?php

namespace App\Policies;

use App\Models\QuestionBank;
use App\Models\User;

class QuestionBankPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'teacher']);
    }

    public function view(User $user, QuestionBank $bank): bool
    {
        if ($user->hasRole('super_admin')) return true;
        return $user->hasRole('teacher') && $bank->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'teacher']);
    }

    public function update(User $user, QuestionBank $bank): bool
    {
        if ($user->hasRole('super_admin')) return true;
        return $user->hasRole('teacher') && $bank->user_id === $user->id;
    }

    public function delete(User $user, QuestionBank $bank): bool
    {
        if ($user->hasRole('super_admin')) return true;
        return $user->hasRole('teacher') && $bank->user_id === $user->id;
    }
}
