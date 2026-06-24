<?php

namespace Tests\Feature\Admin\Core;

use App\Models\Event;
use App\Models\EventAgenda;
use App\Models\EventSession;
use App\Models\OrganiserProfile;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Core\CoreRegistrationService;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventCheckInManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_check_in_registered_participant_by_qr_token_for_selected_session(): void
    {
        $this->seed(AccessControlSeeder::class);
        Mail::fake();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $ticket = $this->ticketWithForm($event, $admin);
        $session = $this->eventSession($event, $admin, $ticket);

        $registration = app(CoreRegistrationService::class)->registerManually($event, $ticket, [
            'full_name' => 'Check In Tester',
            'email' => 'checkin@example.com',
            'answers' => [
                'full_name' => 'Check In Tester',
                'email' => 'checkin@example.com',
            ],
            'answer_files' => [],
        ], $admin->id);

        $this->actingAs($admin)
            ->get(route('core.events.check-in.index', ['event' => $event, 'session_id' => $session->id]))
            ->assertOk()
            ->assertSee('Check-In')
            ->assertSee('Eligible')
            ->assertSee($session->title);

        $this->actingAs($admin)
            ->postJson(route('core.events.check-in.scan', $event), [
                'session_id' => $session->id,
                'qr_token' => $registration->qr_token,
                'device_name' => 'Feature Test Camera',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('participant_name', 'Check In Tester')
            ->assertJsonPath('ticket_name', $ticket->name)
            ->assertJsonPath('session_name', $session->title)
            ->assertJsonPath('counts.eligible', 1)
            ->assertJsonPath('counts.checked_in', 1)
            ->assertJsonPath('counts.pending', 0);

        $this->assertDatabaseHas('attendance_records', [
            'event_id' => $event->id,
            'event_session_id' => $session->id,
            'registration_id' => $registration->id,
            'ticket_id' => $ticket->id,
            'checked_in_by' => $admin->id,
            'status' => 'checked_in',
        ]);

        $this->assertDatabaseHas('attendance_scan_logs', [
            'event_id' => $event->id,
            'event_session_id' => $session->id,
            'registration_id' => $registration->id,
            'ticket_id' => $ticket->id,
            'result' => 'success',
            'scan_result' => 'success',
            'scanned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->postJson(route('core.events.check-in.scan', $event), [
                'session_id' => $session->id,
                'qr_token' => $registration->qr_token,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Participant already checked in.');
    }

    private function event(User $admin): Event
    {
        $organiser = OrganiserProfile::create([
            'name' => 'Check In Organiser',
            'email' => 'checkin@example.test',
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('core.events.store'), [
            'title' => 'Check In Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'check-in-event',
            'custom_url' => 'check-in-event',
            'description' => 'Check in event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(3)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
        ])->assertRedirect();

        return Event::where('slug', 'check-in-event')->firstOrFail();
    }

    private function ticketWithForm(Event $event, User $admin): Ticket
    {
        $form = RegistrationForm::create([
            'event_id' => $event->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'title' => 'Check In Form',
            'status' => 'active',
            'access_mode' => 'public',
            'is_enabled' => true,
        ]);

        foreach ([['full_name', 'Full Name', 'text'], ['email', 'Email', 'email']] as $index => [$key, $label, $type]) {
            RegistrationFormField::create([
                'registration_form_id' => $form->id,
                'source_type' => 'basic',
                'field_key' => $key,
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'placeholder' => $label,
                'is_required' => true,
                'sort_order' => $index + 1,
            ]);
        }

        return Ticket::create([
            'event_id' => $event->id,
            'registration_form_id' => $form->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'name' => 'Check In Pass',
            'description' => 'Ticket for check-in test.',
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => 50,
            'available_quantity' => 50,
            'min_quantity' => 1,
            'max_quantity' => 1,
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addMonth(),
            'is_hidden' => false,
            'status' => 'active',
        ]);
    }

    private function eventSession(Event $event, User $admin, Ticket $ticket): EventSession
    {
        $agenda = EventAgenda::create([
            'event_id' => $event->id,
            'title' => 'Check In Agenda',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $session = EventSession::create([
            'event_id' => $event->id,
            'event_agenda_id' => $agenda->id,
            'title' => 'Morning Check In',
            'description' => 'Entry check-in.',
            'session_type' => 'check_in',
            'capacity' => 50,
            'venue_name' => 'Main Entrance',
            'location' => 'Main Entrance',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonth()->addHours(2),
            'status' => 'active',
            'one_time_check_in' => true,
            'checkout_enabled' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $session->tickets()->sync([$ticket->id]);

        return $session;
    }
}
