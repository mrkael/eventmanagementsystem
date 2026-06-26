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
use Illuminate\Support\Facades\Mail;
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

    public function test_site_preserves_safe_wysiwyg_content_and_strips_unsafe_markup(): void
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
                ['type' => 'hero', 'title' => 'Welcome', 'content' => '<h2>Featured</h2><p><strong>Bold content</strong><script>alert("x")</script><a href="javascript:alert(1)" onclick="alert(2)">Link</a></p>', 'settings' => []],
                ['type' => 'text_content', 'title' => 'Agenda', 'content' => '<ul><li>First</li><li>Second</li></ul>', 'settings' => []],
                ['type' => 'ticket_selection', 'title' => 'Tickets', 'content' => 'Choose a ticket.', 'settings' => []],
                ['type' => 'registration_form', 'title' => 'Registration Form', 'content' => 'Preview fields.', 'settings' => []],
            ]),
        ])->assertRedirect();

        $this->assertDatabaseHas('event_page_sections', ['type' => 'hero', 'content' => '<h2>Featured</h2><p><strong>Bold content</strong><a href="#">Link</a></p>']);

        $this->actingAs($admin)
            ->post(route('core.events.microsite.publish', $event))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        auth()->logout();

        $this->get(route('core.public.events.show', ['event' => $event->custom_url]))
            ->assertOk()
            ->assertSee('<strong>Bold content</strong>', false)
            ->assertSee('<li>First</li>', false)
            ->assertDontSee('alert("x")', false)
            ->assertDontSee('onclick', false)
            ->assertDontSee('javascript:', false);
    }

    public function test_site_builder_uses_inline_editor_and_neutral_canvas_blocks(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);

        $this->actingAs($admin)
            ->get(route('core.events.microsite.edit', $event))
            ->assertOk()
            ->assertSee('data-row-toolbar', false)
            ->assertSee('data-editable-row', false)
            ->assertSee('contenteditable="true"', false)
            ->assertSee('data-html-source', false)
            ->assertSee('dblclick', false)
            ->assertSee('bg-white text-slate-950', false)
            ->assertDontSee('data-inline-toolbar', false)
            ->assertDontSee('data-editor-panel', false)
            ->assertDontSee('openEditor', false)
            ->assertDontSee('bg-slate-950 p-8', false)
            ->assertDontSee('border-blue-100 bg-blue-50', false);
    }

    public function test_visitor_can_submit_inline_public_registration_form_from_published_site(): void
    {
        $this->seed(AccessControlSeeder::class);
        Mail::fake();

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

        auth()->logout();

        $this->get(route('core.public.events.show', ['event' => $event->custom_url]))
            ->assertOk()
            ->assertSee('Select Ticket')
            ->assertSee('data-file-upload', false)
            ->assertSee('space-y-4', false)
            ->assertSee('data-public-event', false);

        $response = $this->post(route('core.public.submit', ['event' => $event->custom_url, 'ticket' => $ticket]), [
            'selected_ticket_id' => $ticket->id,
            'ticket_quantity' => 1,
            'participants' => [
                [
                    'full_name' => 'Public Participant',
                    'email' => 'public@example.com',
                ],
            ],
        ]);

        $registration = $event->coreRegistrations()->firstOrFail();
        $response->assertRedirectContains(route('core.public.success', $registration, false));

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'registration_form_id' => $form->id,
            'full_name' => 'Public Participant',
            'email' => 'public@example.com',
            'status' => 'confirmed',
        ]);

        $this->assertSame(99, $ticket->fresh()->available_quantity);
        $this->assertNotNull($registration->fresh()->qr_token);
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
