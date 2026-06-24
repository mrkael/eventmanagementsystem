<?php

namespace Tests\Feature\Admin\Core;

use App\Models\Event;
use App\Models\OrganiserProfile;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventSiteBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_cannot_publish_without_required_ticket_and_form_blocks(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);

        $event->pages()->create(['template' => 'Main Site', 'status' => 'draft']);

        $this->actingAs($admin)
            ->post(route('core.events.microsite.publish', $event))
            ->assertSessionHasErrors('site');

        $this->assertFalse($event->fresh()->is_public);
    }

    public function test_site_can_publish_with_visible_ticket_and_assigned_form(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $ticket = $this->ticket($event, $admin);
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

        $ticket->update(['registration_form_id' => $form->id]);

        $this->actingAs($admin)->put(route('core.events.microsite.update', $event), [
            'template' => 'Main Site',
            'sections' => json_encode([
                ['type' => 'hero', 'title' => 'Welcome', 'content' => 'Join us.', 'settings' => []],
                ['type' => 'ticket_selection', 'title' => 'Tickets', 'content' => 'Choose a ticket.', 'settings' => []],
                ['type' => 'registration_form', 'title' => 'Registration Form', 'content' => 'Preview fields.', 'settings' => []],
            ]),
        ])->assertRedirect();

        $this->actingAs($admin)
            ->post(route('core.events.microsite.publish', $event))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $event->refresh();

        $this->assertTrue($event->is_public);
        $this->assertDatabaseHas('event_pages', ['event_id' => $event->id, 'status' => 'published']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'core.microsite.publish']);
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
            'title' => 'Site Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'site-event',
            'custom_url' => 'site-event',
            'description' => 'Site event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
        ])->assertRedirect();

        return Event::where('slug', 'site-event')->firstOrFail();
    }

    private function ticket(Event $event, User $admin): Ticket
    {
        return $event->tickets()->create([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'name' => 'General Admission',
            'description' => 'Main ticket.',
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => 100,
            'available_quantity' => 100,
            'min_quantity' => 1,
            'max_quantity' => 5,
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addMonth(),
            'is_hidden' => false,
            'status' => 'active',
        ]);
    }
}
