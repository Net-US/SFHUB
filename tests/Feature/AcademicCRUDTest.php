<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subject;
use App\Models\Task;
use App\Models\ThesisMilestone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AcademicCRUDTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => Hash::make('password'),
            'is_active' => true,
        ]);
    }

    // ── COURSE (Subject) Tests ──────────────────────────────────────────

    public function test_academic_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/academic');
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.academic');
    }

    public function test_academic_page_redirects_guest(): void
    {
        $response = $this->get('/dashboard/academic');
        $response->assertRedirect('/login');
    }

    public function test_can_create_course(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('academic.courses.store'), [
            'code'        => 'IF401',
            'name'        => 'Metodologi Penelitian',
            'sks'         => 3,
            'day_of_week' => 'Senin',
            'start_time'  => '08:00',
            'end_time'    => '10:00',
            'start_date'  => now()->format('Y-m-d'),
            'room'        => 'R.202',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('subjects', [
            'user_id' => $this->user->id,
            'code'    => 'IF401',
            'name'    => 'Metodologi Penelitian',
        ]);
    }

    public function test_can_update_course(): void
    {
        $subject = Subject::create([
            'user_id'     => $this->user->id,
            'code'        => 'IF401',
            'name'        => 'Metodologi Penelitian',
            'sks'         => 3,
            'day_of_week' => 'Senin',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('academic.courses.update', $subject->id), [
            'code'        => 'IF401',
            'name'        => 'Metodologi Penelitian (Update)',
            'sks'         => 3,
            'day_of_week' => 'Selasa',
            'start_time'  => '09:00',
            'end_time'    => '11:00',
            'progress'    => 75,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('subjects', [
            'id'       => $subject->id,
            'name'     => 'Metodologi Penelitian (Update)',
            'progress' => 75,
        ]);
    }

    public function test_can_delete_course(): void
    {
        $subject = Subject::create([
            'user_id'     => $this->user->id,
            'code'        => 'IF401',
            'name'        => 'Metodologi Penelitian',
            'sks'         => 3,
            'day_of_week' => 'Senin',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('academic.courses.destroy', $subject->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_cannot_delete_other_users_course(): void
    {
        $otherUser = User::factory()->create();
        $subject   = Subject::create([
            'user_id'     => $otherUser->id,
            'code'        => 'IF999',
            'name'        => 'Other User Course',
            'sks'         => 2,
            'day_of_week' => 'Rabu',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('academic.courses.destroy', $subject->id));
        $response->assertStatus(404);
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    // ── Academic Task Tests ─────────────────────────────────────────────

    public function test_can_create_academic_task(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('academic.tasks.store'), [
            'title'    => 'Laporan Praktikum',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('tasks', [
            'user_id'  => $this->user->id,
            'title'    => 'Laporan Praktikum',
            'category' => 'academic',
        ]);
    }

    public function test_task_status_toggle(): void
    {
        $task = Task::create([
            'user_id'  => $this->user->id,
            'title'    => 'Tugas Ujian',
            'category' => 'academic',
            'priority' => 'urgent-important',
            'status'   => 'todo',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('academic.tasks.status', $task->id));
        $response->assertStatus(200);
        $response->assertJson(['status' => 'done']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_can_delete_academic_task(): void
    {
        $task = Task::create([
            'user_id'  => $this->user->id,
            'title'    => 'Tugas Hapus',
            'category' => 'academic',
            'priority' => 'not-urgent-not-important',
            'status'   => 'todo',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('academic.tasks.destroy', $task->id));
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    // ── Thesis Milestone Tests ──────────────────────────────────────────

    public function test_can_create_thesis_milestone(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('academic.milestones.store'), [
            'label'       => 'Pengajuan Judul',
            'target_date' => now()->addMonths(2)->format('Y-m-d'),
            'sort_order'  => 1,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('thesis_milestones', [
            'user_id' => $this->user->id,
            'label'   => 'Pengajuan Judul',
        ]);
    }

    public function test_can_update_milestone_as_done(): void
    {
        $milestone = ThesisMilestone::create([
            'user_id'     => $this->user->id,
            'label'       => 'Seminar Proposal',
            'target_date' => now()->addMonths(3)->format('Y-m-d'),
            'done'        => false,
            'sort_order'  => 3,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('academic.milestones.update', $milestone->id), [
            'label' => 'Seminar Proposal',
            'target_date' => now()->addMonths(3)->format('Y-m-d'),
            'done'  => true,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('thesis_milestones', ['id' => $milestone->id, 'done' => 1]);
    }
}
