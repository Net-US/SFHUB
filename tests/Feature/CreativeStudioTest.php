<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreativeStudioTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    public function test_creative_studio_page_loads(): void
    {
        $this->actingAs($this->user)->get('/dashboard/creative')->assertStatus(200);
    }

    public function test_creative_studio_page_redirects_guest(): void
    {
        $this->get('/dashboard/creative')->assertRedirect('/login');
    }

    public function test_can_create_creative_project(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('dashboard.creative.store'), [
            'title'          => 'Video Explainer Klien A',
            'project_type'   => 'Freelance',
            'workflow_stage' => 'production',
            'priority'       => 'high',
            'due_date'       => now()->addDays(14)->format('Y-m-d'),
            'tags'           => ['After Effects', 'Motion Graphics'],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tasks', [
            'user_id'        => $this->user->id,
            'title'          => 'Video Explainer Klien A',
            'category'       => 'Creative',
            'workflow_stage' => 'production',
        ]);
    }

    public function test_can_update_creative_project_status(): void
    {
        $task = Task::create([
            'user_id'        => $this->user->id,
            'title'          => 'Proyek Status Test',
            'category'       => 'Creative',
            'priority'       => 'urgent-important',
            'status'         => 'todo',
            'workflow_stage' => 'script',
        ]);

        $response = $this->actingAs($this->user)->postJson(
            route('dashboard.creative.update-status', $task->id),
            ['stage' => 'production']
        );

        $response->assertStatus(200);
    }

    public function test_can_delete_creative_project(): void
    {
        $task = Task::create([
            'user_id'  => $this->user->id,
            'title'    => 'Proyek Hapus',
            'category' => 'Creative',
            'priority' => 'not-urgent-not-important',
            'status'   => 'todo',
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('dashboard.creative.destroy', $task->id))
            ->assertStatus(200);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_projects_are_grouped_by_stage_in_view(): void
    {
        Task::create([
            'user_id'        => $this->user->id,
            'title'          => 'Proyek Script',
            'category'       => 'Creative',
            'priority'       => 'not-urgent-not-important',
            'status'         => 'todo',
            'workflow_stage' => 'script',
        ]);
        Task::create([
            'user_id'        => $this->user->id,
            'title'          => 'Proyek Production',
            'category'       => 'Creative',
            'priority'       => 'urgent-important',
            'status'         => 'doing',
            'workflow_stage' => 'production',
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard/creative');
        $response->assertStatus(200);
        $response->assertViewHas('projects');

        $projects = $response->viewData('projects');
        $this->assertCount(2, $projects);
    }
}
