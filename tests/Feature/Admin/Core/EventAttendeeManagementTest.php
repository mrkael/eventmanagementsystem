<?php

namespace Tests\Feature\Admin\Core;

use App\Mail\CoreRegistrationConfirmationMail;
use App\Models\Event;
use App\Models\OrganiserProfile;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventAttendeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manually_register_attendee_and_export_event_attendees(): void
    {
        $this->seed(AccessControlSeeder::class);
        Mail::fake();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $ticket = $this->ticketWithForm($event, $admin);

        $this->actingAs($admin)
            ->get(route('core.events.attendees.index', $event))
            ->assertOk()
            ->assertSee('No attendees yet');

        $this->actingAs($admin)
            ->get(route('core.events.attendees.create', $event))
            ->assertOk()
            ->assertSee($ticket->name)
            ->assertSee('Continue');

        $response = $this->actingAs($admin)->post(route('core.events.attendees.store', [$event, $ticket]), [
            'answers' => [
                'full_name' => 'Manual Participant',
                'email' => 'manual@example.com',
                'organization' => 'Core Testing',
            ],
        ]);

        $registration = $event->coreRegistrations()->firstOrFail();
        $response->assertRedirect(route('core.events.attendees.show', [$event, $registration]));

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'registration_form_id' => $ticket->registration_form_id,
            'full_name' => 'Manual Participant',
            'email' => 'manual@example.com',
            'status' => 'confirmed',
            'registered_by' => $admin->id,
        ]);

        $this->assertSame(9, $ticket->fresh()->available_quantity);
        $this->assertNotNull($registration->fresh()->qr_token);
        Mail::assertSent(CoreRegistrationConfirmationMail::class);

        $this->assertDatabaseHas('email_logs', [
            'event_id' => $event->id,
            'registration_id' => $registration->id,
            'email_type' => 'confirmation',
            'recipient_email' => config('event_management.confirmation_email_test_recipient'),
            'status' => 'sent',
        ]);

        $this->actingAs($admin)
            ->get(route('core.events.attendees.export', ['event' => $event, 'search' => 'Manual']))
            ->assertOk()
            ->assertSee('Manual Participant')
            ->assertSee('Organization');
    }

    private function event(User $admin): Event
    {
        $organiser = OrganiserProfile::create([
            'name' => 'Acme Events',
            'email' => 'events@acme.test',
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('core.events.store'), [
            'title' => 'Attendee Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'attendee-event',
            'custom_url' => 'attendee-event',
            'description' => 'Attendee event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
            'capacity' => 100,
        ])->assertRedirect();

        return Event::where('slug', 'attendee-event')->firstOrFail();
    }

    private function ticketWithForm(Event $event, User $admin): Ticket
    {
        $form = RegistrationForm::create([
            'event_id' => $event->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'title' => 'Attendee Form',
            'status' => 'active',
            'access_mode' => 'public',
            'is_enabled' => true,
        ]);

        foreach ([
            ['full_name', 'Full Name', 'text', true],
            ['email', 'Email', 'email', true],
            ['organization', 'Organization', 'text', false],
        ] as $index => [$key, $label, $type, $required]) {
            RegistrationFormField::create([
                'registration_form_id' => $form->id,
                'source_type' => 'basic',
                'field_key' => $key,
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'placeholder' => $label,
                'is_required' => $required,
                'sort_order' => $index + 1,
            ]);
        }

        return Ticket::create([
            'event_id' => $event->id,
            'registration_form_id' => $form->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'name' => 'General Admission',
            'description' => 'Main ticket.',
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => 10,
            'available_quantity' => 10,
            'min_quantity' => 1,
            'max_quantity' => 1,
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addMonth(),
            'is_hidden' => false,
            'status' => 'active',
        ]);
    }
}
