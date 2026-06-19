<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong_password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_rate_limiting_on_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email'    => $user->email,
                'password' => 'wrong',
            ]);
        }

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/teacher/dashboard')->assertRedirect('/login');
        $this->get('/student/dashboard')->assertRedirect('/login');
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }
}
