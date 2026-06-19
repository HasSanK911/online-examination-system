<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@uniexam.local'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
                'role_type'         => 'super_admin',
            ]
        );
        $admin->assignRole('super_admin');

        $examController = User::firstOrCreate(
            ['email' => 'controller@uniexam.local'],
            [
                'name'              => 'Exam Controller',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
                'role_type'         => 'exam_controller',
            ]
        );
        $examController->assignRole('exam_controller');

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@uniexam.local'],
            [
                'name'              => 'Dr. Sarah Ahmed',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
                'role_type'         => 'teacher',
            ]
        );
        $teacher->assignRole('teacher');
    }
}
