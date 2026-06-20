<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'view users', 'create users', 'edit users', 'delete users',
            // Teachers
            'view teachers', 'create teachers', 'edit teachers', 'delete teachers',
            // Faculties
            'view faculties', 'create faculties', 'edit faculties', 'delete faculties',
            // Departments
            'view departments', 'create departments', 'edit departments', 'delete departments',
            // Students
            'view students', 'create students', 'edit students', 'delete students', 'import students', 'export students',
            // Courses
            'view courses', 'create courses', 'edit courses', 'delete courses', 'assign teachers', 'enroll students',
            // Question Banks
            'view question_banks', 'create question_banks', 'edit question_banks', 'delete question_banks',
            // Questions
            'view questions', 'create questions', 'edit questions', 'delete questions',
            // Exams
            'view exams', 'create exams', 'edit exams', 'delete exams', 'schedule exams', 'publish exams', 'monitor exams',
            // Results
            'view results', 'publish results', 'evaluate results', 'export results',
            // Analytics
            'view analytics', 'view olap',
            // Reports
            'view reports', 'generate reports',
            // Audit
            'view audit_logs',
            // Student actions
            'attempt exams', 'view own_results', 'download result_card',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'super_admin' => $permissions,
            'exam_controller' => [
                'view faculties', 'view departments', 'view students', 'view courses',
                'view question_banks', 'view questions',
                'view exams', 'schedule exams', 'publish exams', 'monitor exams',
                'view results', 'publish results', 'export results',
                'view analytics', 'view olap', 'view reports', 'generate reports',
            ],
            'teacher' => [
                'view courses', 'view students',
                'view question_banks', 'create question_banks', 'edit question_banks',
                'view questions', 'create questions', 'edit questions', 'delete questions',
                'view exams', 'create exams', 'edit exams', 'delete exams',
                'view results', 'evaluate results', 'export results',
                'view analytics',
            ],
            'student' => [
                'view exams', 'attempt exams',
                'view own_results', 'download result_card',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
