<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LandingContent;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
            'username'  => 'admin',
        ]);
        $this->regularUser = User::factory()->create([
            'role'      => 'student',
            'is_active' => true,
        ]);

        // Create 'free' plan for testing
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_accessible_by_admin(): void
    {
        $this->actingAs($this->admin)->get('/admin')->assertStatus(200);
    }

    public function test_admin_dashboard_blocked_for_regular_user(): void
    {
        $this->actingAs($this->regularUser)->get('/admin')->assertStatus(403);
    }

    public function test_admin_user_list_accessible(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')->assertStatus(200);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'                  => 'New Student',
            'email'                 => 'newstudent@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
            'plan'                  => 'free',
        ]);

        $response->assertRedirect(route('admin.users'));
        $this->assertDatabaseHas('users', ['email' => 'newstudent@test.com']);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $user = User::factory()->create(['is_active' => true, 'role' => 'student']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle', $user))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => 0]);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $user = User::factory()->create(['is_active' => false, 'role' => 'student']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle', $user))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => 1]);
    }

    public function test_admin_cannot_deactivate_another_admin(): void
    {
        $admin2 = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle', $admin2))
            ->assertRedirect();

        // Admin should remain active
        $this->assertDatabaseHas('users', ['id' => $admin2->id, 'is_active' => 1]);
    }

    public function test_admin_can_delete_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_landing_page_accessible(): void
    {
        $this->actingAs($this->admin)->get('/admin/landing')->assertStatus(200);
    }

    public function test_admin_can_add_landing_content(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.landing.store'), [
            'key'     => 'test_feature',
            'section' => 'features',
            'title'   => 'Fitur Test',
            'content' => 'Deskripsi fitur test',
            'icon'    => 'fa-star',
            'color'   => 'text-blue-600',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('landing_contents', [
            'key'     => 'test_feature',
            'title'   => 'Fitur Test',
            'section' => 'features',
        ]);
    }

    public function test_admin_can_delete_landing_content(): void
    {
        $content = LandingContent::create([
            'key'     => 'delete_me',
            'section' => 'features',
            'title'   => 'Delete Test',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.landing.destroy', $content))
            ->assertRedirect();

        $this->assertDatabaseMissing('landing_contents', ['id' => $content->id]);
    }

    public function test_user_search_works(): void
    {
        User::factory()->create(['name' => 'Budi Santoso', 'role' => 'student']);
        User::factory()->create(['name' => 'Sari Dewi',    'role' => 'student']);

        $response = $this->actingAs($this->admin)->get('/admin/users?search=Budi');
        $response->assertStatus(200);
        $response->assertSeeText('Budi Santoso');
        $response->assertDontSeeText('Sari Dewi');
    }
}
