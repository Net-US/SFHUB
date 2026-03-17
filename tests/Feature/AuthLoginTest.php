<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email'     => 'user@test.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login'    => 'user@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_username(): void
    {
        $user = User::factory()->create([
            'username'  => 'testuser123',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login'    => 'testuser123',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'user@test.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->post('/login', [
            'login'    => 'user@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email'     => 'inactive@test.com',
            'password'  => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'login'    => 'inactive@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_register_creates_user_with_auto_username(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Budi Mahasiswa',
            'email'                 => 'budi@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
            'plan'                  => 'free',
            'terms'                 => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email'    => 'budi@test.com',
            'username' => 'budimahasiswa',
        ]);
    }
}
