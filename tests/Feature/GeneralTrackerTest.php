<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GeneralTrackerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    public function test_general_tracker_page_loads(): void
    {
        $this->actingAs($this->user)->get('/dashboard/tracker')->assertStatus(200);
    }

    public function test_general_tracker_redirects_guest(): void
    {
        $this->get('/dashboard/tracker')->assertRedirect('/login');
    }

    public function test_can_create_general_task(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('tasks.store'), [
            'title'    => 'Olahraga Pagi',
            'category' => 'Kesehatan',
            'due_date' => now()->format('Y-m-d'),
            'status'   => 'todo',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tasks', [
            'user_id'  => $this->user->id,
            'title'    => 'Olahraga Pagi',
            'category' => 'Kesehatan',
            'status'   => 'todo',
        ]);
    }

    public function test_can_toggle_task_status(): void
    {
        $task = Task::create([
            'user_id'  => $this->user->id,
            'title'    => 'Task Toggle',
            'category' => 'Personal',
            'priority' => 'not-urgent-not-important',
            'status'   => 'todo',
        ]);

        $response = $this->actingAs($this->user)->postJson(
            route('tasks.update-status', $task->id),
            ['status' => 'done']
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'new_status' => 'done']);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::create([
            'user_id'  => $this->user->id,
            'title'    => 'Task Hapus',
            'category' => 'Personal',
            'priority' => 'not-urgent-not-important',
            'status'   => 'todo',
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('tasks.destroy', $task->id))
            ->assertStatus(302)
            ->assertRedirect(route('dashboard.tracker'));

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_tracker_view_has_correct_variables(): void
    {
        Task::create([
            'user_id'  => $this->user->id,
            'title'    => 'Test Task View',
            'category' => 'Kesehatan',
            'priority' => 'not-urgent-not-important',
            'status'   => 'todo',
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard/tracker');
        $response->assertViewHas('allTasks');
        $response->assertViewHas('completedCount');
        $response->assertViewHas('totalCount');
        $response->assertViewHas('catCounts');
    }

    public function test_user_cannot_delete_other_users_task(): void
    {
        $other = User::factory()->create();
        $task  = Task::create([
            'user_id'  => $other->id,
            'title'    => 'Milik Orang Lain',
            'category' => 'Personal',
            'priority' => 'not-urgent-not-important',
            'status'   => 'todo',
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('tasks.destroy', $task->id))
            ->assertStatus(404);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
