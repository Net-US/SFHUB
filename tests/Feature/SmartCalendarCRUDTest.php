<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartCalendarCRUDTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->user = User::factory()->create(['is_active' => true]);
        $this->otherUser = User::factory()->create(['is_active' => true]);
    }

    public function test_smart_calendar_page_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.smartcalendar'))
            ->assertStatus(200)
            ->assertViewIs('dashboard.smartcalendar');
    }

    public function test_smart_calendar_page_redirects_guest(): void
    {
        $this->get(route('dashboard.smartcalendar'))
            ->assertRedirect('/login');
    }

    public function test_can_create_regular_event(): void
    {
        $payload = [
            'title' => 'Meeting Client',
            'description' => 'Kickoff',
            'type' => 'creative',
            'date' => '2026-04-10',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'is_all_day' => false,
        ];

        $this->actingAs($this->user)
            ->postJson(route('calendar.events.store'), $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $this->user->id,
            'title' => 'Meeting Client',
            'type' => 'creative',
            'is_all_day' => 0,
        ]);
    }

    public function test_can_create_all_day_multi_day_event(): void
    {
        $payload = [
            'title' => 'Libur Lebaran',
            'description' => 'Tanggal merah',
            'type' => 'personal',
            'date' => '2026-04-01',
            'end_date' => '2026-04-10',
            'is_all_day' => true,
        ];

        $this->actingAs($this->user)
            ->postJson(route('calendar.events.store'), $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $event = CalendarEvent::query()->where('user_id', $this->user->id)->firstOrFail();

        $this->assertSame('2026-04-10', $event->end_date?->format('Y-m-d'));
        $this->assertTrue($event->is_all_day);
        $this->assertSame('00:00:00', $event->start_time->format('H:i:s'));
        $this->assertSame('23:59:59', $event->end_time->format('H:i:s'));
    }

    public function test_validation_fails_when_end_date_before_start_date(): void
    {
        $payload = [
            'title' => 'Event Invalid',
            'type' => 'academic',
            'date' => '2026-04-10',
            'end_date' => '2026-04-08',
            'is_all_day' => true,
        ];

        $this->actingAs($this->user)
            ->postJson(route('calendar.events.store'), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_can_update_event(): void
    {
        $event = CalendarEvent::create([
            'user_id' => $this->user->id,
            'title' => 'Event Lama',
            'description' => 'Desc lama',
            'start_time' => Carbon::parse('2026-04-12 08:00'),
            'end_time' => Carbon::parse('2026-04-12 10:00'),
            'end_date' => '2026-04-12',
            'type' => 'academic',
            'color' => '#3b82f6',
            'is_all_day' => false,
            'is_recurring' => false,
        ]);

        $payload = [
            'title' => 'Event Baru',
            'description' => 'Desc baru',
            'type' => 'deadline',
            'date' => '2026-04-13',
            'end_date' => '2026-04-14',
            'is_all_day' => true,
        ];

        $this->actingAs($this->user)
            ->putJson(route('calendar.events.update', $event->id), $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'title' => 'Event Baru',
            'type' => 'deadline',
            'is_all_day' => 1,
        ]);
    }

    public function test_cannot_update_other_users_event(): void
    {
        $event = CalendarEvent::create([
            'user_id' => $this->otherUser->id,
            'title' => 'Private Event',
            'description' => null,
            'start_time' => Carbon::parse('2026-04-12 08:00'),
            'end_time' => Carbon::parse('2026-04-12 10:00'),
            'end_date' => '2026-04-12',
            'type' => 'academic',
            'color' => '#3b82f6',
            'is_all_day' => false,
            'is_recurring' => false,
        ]);

        $this->actingAs($this->user)
            ->putJson(route('calendar.events.update', $event->id), [
                'title' => 'Hacked',
                'type' => 'personal',
                'date' => '2026-04-13',
                'is_all_day' => true,
            ])
            ->assertStatus(404);
    }

    public function test_can_delete_event(): void
    {
        $event = CalendarEvent::create([
            'user_id' => $this->user->id,
            'title' => 'Akan dihapus',
            'description' => null,
            'start_time' => Carbon::parse('2026-04-12 08:00'),
            'end_time' => Carbon::parse('2026-04-12 10:00'),
            'end_date' => '2026-04-12',
            'type' => 'academic',
            'color' => '#3b82f6',
            'is_all_day' => false,
            'is_recurring' => false,
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('calendar.events.destroy', $event->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_cannot_delete_other_users_event(): void
    {
        $event = CalendarEvent::create([
            'user_id' => $this->otherUser->id,
            'title' => 'Private Event',
            'description' => null,
            'start_time' => Carbon::parse('2026-04-12 08:00'),
            'end_time' => Carbon::parse('2026-04-12 10:00'),
            'end_date' => '2026-04-12',
            'type' => 'academic',
            'color' => '#3b82f6',
            'is_all_day' => false,
            'is_recurring' => false,
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('calendar.events.destroy', $event->id))
            ->assertStatus(404);
    }

    public function test_multi_day_event_is_expanded_in_index_view_data(): void
    {
        CalendarEvent::create([
            'user_id' => $this->user->id,
            'title' => 'Libur Panjang',
            'description' => 'Test expand',
            'start_time' => Carbon::parse('2026-04-01 00:00:00'),
            'end_time' => Carbon::parse('2026-04-03 23:59:59'),
            'end_date' => '2026-04-03',
            'type' => 'personal',
            'color' => '#8b5cf6',
            'is_all_day' => true,
            'is_recurring' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/dashboard/smart-calendar?month=4&year=2026');

        $response->assertStatus(200)
            ->assertViewHas('events', function ($events) {
                $dates = collect($events)->pluck('date')->values()->all();

                return in_array('2026-04-01', $dates, true)
                    && in_array('2026-04-02', $dates, true)
                    && in_array('2026-04-03', $dates, true);
            });
    }

    public function test_can_crud_recurring_schedule(): void
    {
        $createPayload = [
            'activity' => 'Olahraga Pagi',
            'type' => 'health',
            'frequency' => 'weekly',
            'days_of_week' => 'Senin,Rabu',
            'start_time' => '06:00',
            'end_time' => '07:00',
            'start_date' => '2026-04-01',
            'end_date' => '2026-07-01',
            'notes' => 'Cardio',
            'color' => '#ef4444',
        ];

        $this->actingAs($this->user)
            ->postJson(route('calendar.schedules.store'), $createPayload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $schedule = Schedule::query()->where('user_id', $this->user->id)->firstOrFail();

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'activity' => 'Olahraga Pagi',
            'frequency' => 'weekly',
        ]);

        $updatePayload = [
            'activity' => 'Olahraga Sore',
            'type' => 'health',
            'frequency' => 'monthly',
            'day_of_month' => 12,
            'start_time' => '17:00',
            'end_time' => '18:00',
            'start_date' => '2026-04-01',
            'end_date' => '2026-08-01',
            'notes' => 'Updated',
            'color' => '#ef4444',
        ];

        $this->actingAs($this->user)
            ->putJson(route('calendar.schedules.update', $schedule->id), $updatePayload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'activity' => 'Olahraga Sore',
            'frequency' => 'monthly',
            'day_of_month' => 12,
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('calendar.schedules.destroy', $schedule->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }
}
