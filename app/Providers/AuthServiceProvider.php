<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Result;
use App\Models\Student;
use App\Policies\ExamPolicy;
use App\Policies\QuestionBankPolicy;
use App\Policies\ResultPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Exam::class         => ExamPolicy::class,
        QuestionBank::class => QuestionBankPolicy::class,
        Result::class       => ResultPolicy::class,
        Student::class      => StudentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
