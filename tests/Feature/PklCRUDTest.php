<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PklInfo;
use App\Models\PklLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PklCRUDTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    public function test_pkl_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/pkl');
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.pkl');
    }

    public function test_pkl_page_redirects_guest(): void
    {
        $this->get('/dashboard/pkl')->assertRedirect('/login');
    }

    public function test_can_create_pkl_info(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('pkl.info.store'), [
            'company'        => 'PT. Digital Kreatif Indonesia',
            'department'     => 'IT',
            'supervisor'     => 'Bpk. Reza',
            'start_date'     => '2024-01-08',
            'end_date'       => '2024-06-28',
            'hours_required' => 720,
            'allowance'      => 2500000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pkl_infos', [
            'user_id' => $this->user->id,
            'company' => 'PT. Digital Kreatif Indonesia',
        ]);
    }

    public function test_can_update_pkl_info(): void
    {
        $info = PklInfo::create([
            'user_id'        => $this->user->id,
            'company'        => 'PT Lama',
            'hours_required' => 720,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('pkl.info.update', $info->id), [
            'company'    => 'PT Baru',
            'department' => 'Digital Marketing',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pkl_infos', ['id' => $info->id, 'company' => 'PT Baru']);
    }

    public function test_can_store_pkl_activity(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('pkl.activities.store'), [
            'task'     => 'Membuat desain banner',
            'log_date' => now()->format('Y-m-d'),
            'hours'    => 4,
            'category' => 'Design',
            'notes'    => 'Menggunakan Figma',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pkl_logs', [
            'user_id' => $this->user->id,
            'task'    => 'Membuat desain banner',
            'hours'   => 4,
            'status'  => 'done',
        ]);
    }

    public function test_can_update_pkl_activity(): void
    {
        $log = PklLog::create([
            'user_id'  => $this->user->id,
            'task'     => 'Aktivitas Lama',
            'log_date' => now()->format('Y-m-d'),
            'hours'    => 2,
            'category' => 'Design',
            'status'   => 'done',
        ]);

        $response = $this->actingAs($this->user)->putJson(route('pkl.activities.update', $log->id), [
            'task'     => 'Aktivitas Baru',
            'log_date' => now()->format('Y-m-d'),
            'hours'    => 3,
            'category' => 'Development',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pkl_logs', ['id' => $log->id, 'task' => 'Aktivitas Baru', 'hours' => 3]);
    }

    public function test_can_delete_pkl_activity(): void
    {
        $log = PklLog::create([
            'user_id'  => $this->user->id,
            'task'     => 'Aktivitas Hapus',
            'log_date' => now()->format('Y-m-d'),
            'hours'    => 1,
            'category' => 'Meeting',
            'status'   => 'done',
        ]);

        $this->actingAs($this->user)->deleteJson(route('pkl.activities.destroy', $log->id))->assertRedirect();
        $this->assertDatabaseMissing('pkl_logs', ['id' => $log->id]);
    }

    public function test_cannot_modify_other_users_pkl_activity(): void
    {
        $other = User::factory()->create();
        $log   = PklLog::create([
            'user_id'  => $other->id,
            'task'     => 'Orang Lain',
            'log_date' => now()->format('Y-m-d'),
            'hours'    => 1,
            'category' => 'Lainnya',
            'status'   => 'done',
        ]);

        $this->actingAs($this->user)->deleteJson(route('pkl.activities.destroy', $log->id))->assertStatus(404);
        $this->assertDatabaseHas('pkl_logs', ['id' => $log->id]);
    }

    public function test_pkl_schedule_can_be_updated(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('pkl.schedule.update'), [
            'schedules' => [
                ['day' => 'Senin',  'type' => 'full', 'start_time' => '08:00', 'end_time' => '17:00'],
                ['day' => 'Selasa', 'type' => 'full', 'start_time' => '08:00', 'end_time' => '17:00'],
                ['day' => 'Sabtu',  'type' => 'off',  'start_time' => null,    'end_time' => null],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pkl_schedules', [
            'user_id' => $this->user->id,
            'day'     => 'Senin',
            'type'    => 'full',
        ]);
    }
}
