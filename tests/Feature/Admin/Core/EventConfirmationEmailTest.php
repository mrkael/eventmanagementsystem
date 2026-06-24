<?php

namespace Tests\Feature\Admin\Core;

use App\Mail\CoreRegistrationConfirmationMail;
use App\Models\Event;
use App\Models\EventPage;
use App\Models\OrganiserProfile;
use App\Models\Registration;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_configure_preview_and_send_confirmation_test_email(): void
    {
        $this->seed(AccessControlSeeder::class);
        Mail::fake();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);

        $this->actingAs($admin)
            ->get(route('core.events.email.edit', $event))
            ->assertOk()
            ->assertSee('{{event_name}}')
            ->assertSee('{{qr_code}}');

        $this->actingAs($admin)->put(route('core.events.email.update', $event), [
            'subject' => 'Registration Confirmation - {{event_name}}',
            'header_content' => 'Welcome {{participant_name}}',
            'body_content' => 'Your ticket is {{ticket_name}}. Reference {{registration_reference}}. {{qr_code}}',
            'footer_content' => 'Automated email',
            'is_active' => '1',
        ])->assertRedirect(route('core.events.email.edit', $event));

        $this->assertDatabaseHas('event_email_templates', [
            'event_id' => $event->id,
            'type' => 'confirmation',
            'subject' => 'Registration Confirmation - {{event_name}}',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('core.events.email.preview', $event))
            ->assertOk()
            ->assertSee('Registration Confirmation - Email Event');

        $this->actingAs($admin)
            ->post(route('core.events.email.test', $event))
            ->assertRedirect(route('core.events.email.edit', $event));

        Mail::assertSent(CoreRegistrationConfirmationMail::class);
        $this->assertDatabaseHas('email_logs', [
            'event_id' => $event->id,
            'email_type' => 'confirmation_test',
            'recipient_email' => config('event_management.confirmation_email_test_recipient'),
            'status' => 'sent',
        ]);
    }

    public function test_public_site_registration_creates_registration_and_sends_confirmation(): void
    {
        $this->seed(AccessControlSeeder::class);
        Mail::fake();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        [$ticket] = $this->ticketWithForm($event, $admin);

        $event->update([
            'status_key' => 'published',
            'is_public' => true,
            'published_at' => now(),
            'allow_duplicate_email' => true,
        ]);

        EventPage::create([
            'event_id' => $event->id,
            'template' => 'Default Site',
            'status' => 'published',
            'published_at' => now(),
        ])->sections()->createMany([
            ['type' => 'hero', 'title' => 'Welcome', 'content' => 'Join us.', 'sort_order' => 0],
            ['type' => 'ticket_selection', 'title' => 'Tickets', 'content' => 'Choose a ticket.', 'sort_order' => 1],
            ['type' => 'footer', 'title' => 'Footer', 'content' => 'Thank you.', 'sort_order' => 2],
        ]);

        $this->get(route('core.public.events.show', ['event' => $event->custom_url]))
            ->assertOk()
            ->assertDontSee('Site Preview')
            ->assertSee('Select Ticket');

        $this->post(route('core.public.submit', ['event' => $event->custom_url, 'ticket' => $ticket]), [
            'participants' => [
                [
                    'full_name' => 'Public Tester',
                    'email' => 'participant@example.com',
                    'phone_number' => '0123456789',
                    'organization' => 'Testing Co',
                    'designation' => 'QA',
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'full_name' => 'Public Tester',
            'email' => 'participant@example.com',
            'status' => 'confirmed',
        ]);

        Mail::assertSent(CoreRegistrationConfirmationMail::class);

        $registration = Registration::where('email', 'participant@example.com')->firstOrFail();
        $this->assertNotNull($registration->confirmation_email_sent_at);

        $this->assertDatabaseHas('email_logs', [
            'event_id' => $event->id,
            'registration_id' => $registration->id,
            'email_type' => 'confirmation',
            'recipient_email' => config('event_management.confirmation_email_test_recipient'),
            'original_participant_email' => 'participant@example.com',
            'status' => 'sent',
        ]);
    }

    private function event(User $admin): Event
    {
        $organiser = OrganiserProfile::create([
            'name' => 'Acme Events',
            'email' => 'organiser@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('core.events.store'), [
            'title' => 'Email Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'email-event',
            'custom_url' => 'email-event',
            'description' => 'Email event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
        ])->assertRedirect();

        return Event::where('slug', 'email-event')->firstOrFail();
    }

    private function ticketWithForm(Event $event, User $admin): array
    {
        $form = RegistrationForm::create([
            'event_id' => $event->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'title' => 'Main Registration Form',
            'status' => 'active',
            'access_mode' => 'public',
            'is_enabled' => true,
        ]);

        RegistrationFormField::create([
            'registration_form_id' => $form->id,
            'source_type' => 'basic',
            'field_key' => 'full_name',
            'key' => 'full_name',
            'label' => 'Full Name',
            'type' => 'text',
            'placeholder' => 'Full name',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        RegistrationFormField::create([
            'registration_form_id' => $form->id,
            'source_type' => 'basic',
            'field_key' => 'email',
            'key' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'placeholder' => 'Email',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'registration_form_id' => $form->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'name' => 'General Admission',
            'description' => 'Main ticket.',
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => 100,
            'available_quantity' => 100,
            'min_quantity' => 1,
            'max_quantity' => 3,
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addMonth(),
            'is_hidden' => false,
            'status' => 'active',
        ]);

        return [$ticket, $form];
    }
}
