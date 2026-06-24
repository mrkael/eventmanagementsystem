<?php

namespace Tests\Feature\Admin\Core;

use App\Models\Event;
use App\Models\OrganiserProfile;
use App\Models\RegistrationForm;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_update_core_event_settings(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $organiser = $this->organiser($admin);

        $this->actingAs($admin)->post(route('core.events.store'), [
            'title' => 'Leadership Forum',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'leadership-forum',
            'custom_url' => 'leadership-forum-2027',
            'description' => 'Executive event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(4)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
            'registration_opens_at' => now()->format('Y-m-d H:i:s'),
            'registration_closes_at' => now()->addWeeks(3)->format('Y-m-d H:i:s'),
            'allow_duplicate_email_registration' => '1',
        ])->assertRedirect();

        $event = Event::where('slug', 'leadership-forum')->firstOrFail();

        $this->assertSame($organiser->id, $event->organiser_profile_id);
        $this->assertTrue($event->allow_duplicate_email);
        $this->assertDatabaseHas('audit_logs', ['action' => 'events.create']);

        $this->actingAs($admin)
            ->get(route('core.events.index', ['search' => 'Leadership', 'organiser_profile_id' => $organiser->id, 'status' => 'draft']))
            ->assertOk()
            ->assertSee('Leadership Forum')
            ->assertSee($organiser->name);

        $this->actingAs($admin)->put(route('core.events.update', $event), [
            'title' => 'Leadership Forum Updated',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'leadership-forum-updated',
            'custom_url' => 'leadership-forum-updated',
            'description' => 'Updated executive event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(4)->format('Y-m-d H:i:s'),
            'location' => 'Putrajaya',
            'status_key' => 'published',
            'allow_duplicate_email_registration' => '0',
        ])->assertRedirect(route('core.events.show', $event));

        $event->refresh();

        $this->assertSame('Leadership Forum Updated', $event->title);
        $this->assertFalse($event->allow_duplicate_email);
        $this->assertTrue($event->is_public);
        $this->assertDatabaseHas('audit_logs', ['action' => 'events.update']);
    }

    public function test_super_admin_can_manage_event_tickets(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $form = RegistrationForm::create([
            'event_id' => $event->id,
            'title' => 'Default Registration',
            'description' => 'Default form.',
            'access_mode' => 'public',
            'is_enabled' => true,
        ]);

        $this->actingAs($admin)->post(route('core.events.tickets.store', $event), [
            'name' => 'General Admission',
            'description' => 'Standard ticket.',
            'quantity' => 100,
            'min_quantity' => 1,
            'max_quantity' => 5,
            'sales_start_at' => now()->format('Y-m-d H:i:s'),
            'sales_end_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'is_hidden' => '1',
            'status' => 'active',
        ])->assertRedirect(route('core.events.tickets.index', $event));

        $ticket = Ticket::where('event_id', $event->id)->firstOrFail();

        $this->assertNull($ticket->registration_form_id);
        $this->assertSame(100, $ticket->available_quantity);
        $this->assertTrue($ticket->is_hidden);
        $this->assertDatabaseHas('audit_logs', ['action' => 'tickets.create']);

        $this->actingAs($admin)->put(route('core.events.tickets.update', [$event, $ticket]), [
            'name' => 'General Admission Updated',
            'description' => 'Updated ticket.',
            'quantity' => 120,
            'min_quantity' => 1,
            'max_quantity' => 10,
            'sales_start_at' => now()->format('Y-m-d H:i:s'),
            'sales_end_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'status' => 'inactive',
        ])->assertRedirect(route('core.events.tickets.index', $event));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'name' => 'General Admission Updated',
            'quantity' => 120,
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->delete(route('core.events.tickets.destroy', [$event, $ticket]))
            ->assertRedirect(route('core.events.tickets.index', $event));

        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'tickets.delete']);
    }

    private function organiser(User $admin): OrganiserProfile
    {
        return OrganiserProfile::create([
            'name' => 'Acme Events',
            'email' => 'events@acme.test',
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }

    private function event(User $admin): Event
    {
        $organiser = $this->organiser($admin);

        $this->actingAs($admin)->post(route('core.events.store'), [
            'title' => 'Ticket Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'ticket-event',
            'custom_url' => 'ticket-event',
            'description' => 'Ticket event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
        ])->assertRedirect();

        return Event::where('slug', 'ticket-event')->firstOrFail();
    }
}
