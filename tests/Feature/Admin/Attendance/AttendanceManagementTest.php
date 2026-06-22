<?php

namespace Tests\Feature\Admin\Attendance;

use App\Enums\EventLifecycleStatus;
use App\Enums\ParticipantRegistrationStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\ParticipantRegistration;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EventMasterSetupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_participant_can_be_checked_in_by_qr(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $registration = $this->registration($event);
        $qr = app(AttendanceService::class)->generateQr($registration, $admin);

        $this->actingAs($admin)
            ->postJson(route('admin.events.attendance.check-in', $event), ['token' => $qr['token']])
            ->assertOk()
            ->assertJsonPath('registration.email', 'attendee@example.test');

        $registration->refresh();
        $this->assertSame(ParticipantRegistrationStatus::Attended, $registration->status);
        $this->assertNotNull($registration->checked_in_at);
        $this->assertDatabaseHas('attendance_logs', ['action' => 'check_in', 'result' => 'success']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'attendance.check_in']);
    }

    public function test_duplicate_check_in_is_prevented(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $registration = $this->registration($event);
        $qr = app(AttendanceService::class)->generateQr($registration, $admin);

        $this->actingAs($admin)->postJson(route('admin.events.attendance.check-in', $event), ['token' => $qr['token']])->assertOk();
        $this->actingAs($admin)->postJson(route('admin.events.attendance.check-in', $event), ['token' => $qr['token']])->assertUnprocessable();

        $this->assertDatabaseHas('attendance_logs', ['action' => 'check_in', 'result' => 'failed', 'notes' => 'Duplicate check-in prevented.']);
    }

    public function test_cross_event_qr_usage_is_rejected(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $otherEvent = $this->event($admin, ['slug' => 'other-attendance-event']);
        $registration = $this->registration($event);
        $qr = app(AttendanceService::class)->generateQr($registration, $admin);

        $this->actingAs($admin)
            ->postJson(route('admin.events.attendance.check-in', $otherEvent), ['token' => $qr['token']])
            ->assertUnprocessable();

        $this->assertDatabaseHas('attendance_logs', ['event_id' => $otherEvent->id, 'result' => 'failed', 'notes' => 'Cross-event QR usage blocked.']);
    }

    public function test_attendance_exports_are_available(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $this->registration($event);

        $this->actingAs($admin)->get(route('admin.events.attendance.export', [$event, 'excel']))->assertOk()->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->actingAs($admin)->get(route('admin.events.attendance.export', [$event, 'pdf']))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    private function event(User $admin, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organizer_id' => $admin->id,
            'event_category_id' => EventCategory::firstOrFail()->id,
            'event_type_id' => EventType::firstOrFail()->id,
            'event_status_id' => EventStatus::where('key', 'approved')->firstOrFail()->id,
            'title' => 'Attendance Test Event',
            'slug' => 'attendance-test-event-'.uniqid(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'capacity' => 100,
            'is_registration_enabled' => true,
            'is_public' => true,
            'status_key' => EventLifecycleStatus::Published,
        ], $overrides));
    }

    private function registration(Event $event): ParticipantRegistration
    {
        return ParticipantRegistration::create([
            'event_id' => $event->id,
            'name' => 'Attendance Guest',
            'email' => 'attendee@example.test',
            'status' => ParticipantRegistrationStatus::Confirmed,
            'source' => 'admin',
            'approved_at' => now(),
        ]);
    }
}
