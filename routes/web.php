<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ExamManagementController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Teacher\EvaluationController;
use App\Http\Controllers\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Teacher\QuestionBankController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ExamAttemptController;
use App\Http\Controllers\Student\ResultController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Named dashboard route — Breeze auth controllers redirect here after login
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('super_admin') || $user->hasRole('exam_controller')) return redirect()->route('admin.dashboard');
    if ($user->hasRole('teacher'))  return redirect()->route('teacher.dashboard');
    if ($user->hasRole('student'))  return redirect()->route('student.dashboard');
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('super_admin'))    return redirect()->route('admin.dashboard');
        if ($user->hasRole('exam_controller')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('teacher'))        return redirect()->route('teacher.dashboard');
        if ($user->hasRole('student'))        return redirect()->route('student.dashboard');
    }
    return redirect()->route('login');
});

// ─── Super Admin & Exam Controller (shared exam operations) ───────────────────
Route::middleware(['auth', 'verified', 'role:super_admin|exam_controller'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Exams — schedule, monitor, publish results
        Route::get('/exams', [ExamManagementController::class, 'index'])->name('exams.index');
        Route::get('/exams/{exam}', [ExamManagementController::class, 'show'])->name('exams.show');
        Route::post('/exams/{exam}/publish-results', [ExamManagementController::class, 'publishResults'])->name('exams.publish-results');

        // Analytics & OLAP
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/student/{student}/pdf', [ReportController::class, 'studentPdf'])->name('reports.student');
        Route::get('/reports/exam/{exam}/pdf', [ReportController::class, 'examPdf'])->name('reports.exam');
        Route::get('/reports/department/{department}/pdf', [ReportController::class, 'departmentPdf'])->name('reports.department');
        Route::get('/reports/course/{course}/pdf', [ReportController::class, 'coursePdf'])->name('reports.course');
    });

// ─── Super Admin only (academic structure, users, audit) ──────────────────────
Route::middleware(['auth', 'verified', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Faculties
        Route::resource('faculties', FacultyController::class)->except(['create', 'edit', 'show']);

        // Departments
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Courses
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/teachers', [CourseController::class, 'assignTeacher'])->name('courses.teachers.assign');
        Route::delete('/courses/{course}/teachers/{user}', [CourseController::class, 'removeTeacher'])->name('courses.teachers.remove');
        Route::post('/courses/{course}/students', [CourseController::class, 'syncStudents'])->name('courses.students.sync');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        // Students
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
    });

// ─── Teacher ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');

        // Exam CRUD & lifecycle
        Route::get('/exams', [TeacherExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [TeacherExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [TeacherExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}', [TeacherExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{exam}/edit', [TeacherExamController::class, 'edit'])->name('exams.edit');
        Route::put('/exams/{exam}', [TeacherExamController::class, 'update'])->name('exams.update');
        Route::delete('/exams/{exam}', [TeacherExamController::class, 'destroy'])->name('exams.destroy');

        // Exam question management
        Route::get('/exams/{exam}/questions', [TeacherExamController::class, 'manageQuestions'])->name('exams.questions');
        Route::post('/exams/{exam}/questions', [TeacherExamController::class, 'addQuestion'])->name('exams.questions.add');
        Route::delete('/exams/{exam}/questions/{question}', [TeacherExamController::class, 'removeQuestion'])->name('exams.questions.remove');
        Route::patch('/exams/{exam}/questions/{question}', [TeacherExamController::class, 'updateQuestionMark'])->name('exams.questions.update-mark');

        // Exam status & publishing
        Route::patch('/exams/{exam}/status', [TeacherExamController::class, 'updateStatus'])->name('exams.status');
        Route::post('/exams/{exam}/publish', [TeacherExamController::class, 'publish'])->name('exams.publish');

        // Evaluation
        Route::get('/exams/{exam}/evaluate', [EvaluationController::class, 'index'])->name('exams.evaluate');
        Route::get('/exams/{exam}/evaluate/{attempt}', [EvaluationController::class, 'showAttempt'])->name('exams.evaluate.attempt');
        Route::post('/exams/{exam}/evaluate/{attempt}', [EvaluationController::class, 'submitEvaluation'])->name('exams.evaluate.submit');

        // Question Banks
        Route::get('/question-banks', [QuestionBankController::class, 'index'])->name('question-banks.index');
        Route::post('/question-banks', [QuestionBankController::class, 'store'])->name('question-banks.store');
        Route::get('/question-banks/{bank}', [QuestionBankController::class, 'show'])->name('question-banks.show');
        Route::post('/question-banks/{bank}/questions', [QuestionBankController::class, 'storeQuestion'])->name('question-banks.questions.store');
        Route::delete('/question-banks/{bank}/questions/{question}', [QuestionBankController::class, 'destroyQuestion'])->name('question-banks.questions.destroy');
    });

// ─── Student ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
        Route::get('/exams', [ExamAttemptController::class, 'index'])->name('exams.index');
        Route::get('/exams/{exam}/attempt', [ExamAttemptController::class, 'start'])->name('exams.attempt');
        Route::get('/results', [ResultController::class, 'index'])->name('results.index');
        Route::get('/results/{result}', [ResultController::class, 'show'])->name('results.show');
        Route::get('/results/{result}/download', [ResultController::class, 'downloadCard'])->name('results.download');
    });

// ─── Internal API (exam attempt AJAX) ─────────────────────────────────────────
Route::middleware(['auth', 'role:student'])
    ->prefix('api/exam')
    ->name('api.exam.')
    ->group(function () {
        Route::post('/{attempt}/save-answer', [ExamAttemptController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/{attempt}/submit', [ExamAttemptController::class, 'submit'])->name('submit');
        Route::post('/{attempt}/log-activity', [ExamAttemptController::class, 'logActivity'])->name('log-activity');
    });

// ─── Profile (all auth users) ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
