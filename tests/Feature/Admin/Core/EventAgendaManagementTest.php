<?php

namespace Tests\Feature\Admin\Core;

use App\Models\Event;
use App\Models\EventAgenda;
use App\Models\OrganiserProfile;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAgendaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_agenda_and_manage_sessions_with_ticket_assignment(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $ticket = $this->ticket($event, $admin, 'General Admission');
        $vipTicket = $this->ticket($event, $admin, 'VIP Pass');

        $this->actingAs($admin)
            ->get(route('core.events.agendas.index', $event))
            ->assertOk()
            ->assertSee('No agenda yet');

        $this->actingAs($admin)
            ->post(route('core.events.agendas.store', $event), ['title' => 'Main Agenda'])
            ->assertRedirect();

        $agenda = EventAgenda::where('event_id', $event->id)->firstOrFail();

        $this->assertSame('Main Agenda', $agenda->title);

        $this->actingAs($admin)
            ->post(route('core.events.agendas.sessions.store', [$event, $agenda]), [
                'title' => 'Opening Keynote',
                'description' => 'Welcome and opening.',
                'session_type' => 'keynote',
                'capacity' => 80,
                'venue_name' => 'Main Hall',
                'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMonth()->addHour()->format('Y-m-d H:i:s'),
                'ticket_ids' => [$ticket->id, $vipTicket->id],
            ])
            ->assertRedirect(route('core.events.agendas.show', [$event, $agenda]));

        $session = $agenda->sessions()->firstOrFail();

        $this->assertSame('Opening Keynote', $session->title);
        $this->assertSame('keynote', $session->session_type);
        $this->assertSame('Main Hall', $session->venue_name);
        $this->assertCount(2, $session->tickets);

        $this->actingAs($admin)
            ->get(route('core.events.agendas.show', [$event, $agenda]))
            ->assertOk()
            ->assertSee('Opening Keynote')
            ->assertSee('General Admission')
            ->assertSee('VIP Pass');

        $this->actingAs($admin)
            ->put(route('core.events.agendas.sessions.update', [$event, $agenda, $session]), [
                'title' => 'Updated Keynote',
                'description' => 'Updated welcome.',
                'session_type' => 'panel',
                'capacity' => 100,
                'venue_name' => 'Hall A',
                'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
                'ticket_ids' => [$vipTicket->id],
            ])
            ->assertRedirect(route('core.events.agendas.show', [$event, $agenda]));

        $session->refresh();
        $this->assertSame('Updated Keynote', $session->title);
        $this->assertSame('panel', $session->session_type);
        $this->assertEquals([$vipTicket->id], $session->tickets()->pluck('tickets.id')->all());

        $this->actingAs($admin)
            ->delete(route('core.events.agendas.sessions.destroy', [$event, $agenda, $session]))
            ->assertRedirect(route('core.events.agendas.show', [$event, $agenda]));

        $this->assertSoftDeleted('event_sessions', ['id' => $session->id]);
    }

    private function event(User $admin): Event
    {
        $organiser = OrganiserProfile::create([
            'name' => 'Agenda Organiser',
            'email' => 'agenda@example.test',
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('core.events.store'), [
            'title' => 'Agenda Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'agenda-event',
            'custom_url' => 'agenda-event',
            'description' => 'Agenda event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(3)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
        ])->assertRedirect();

        return Event::where('slug', 'agenda-event')->firstOrFail();
    }

    private function ticket(Event $event, User $admin, string $name): Ticket
    {
        return Ticket::create([
            'event_id' => $event->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'name' => $name,
            'description' => 'Ticket for agenda test.',
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => 50,
            'available_quantity' => 50,
            'min_quantity' => 1,
            'max_quantity' => 2,
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addMonth(),
            'is_hidden' => false,
            'status' => 'active',
        ]);
    }
}
