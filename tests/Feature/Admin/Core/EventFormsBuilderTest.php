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

class EventFormsBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_preview_update_and_delete_event_form(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $ticket = $this->ticket($event, $admin);

        $fields = [
            ['source_type' => 'basic', 'field_key' => 'full_name', 'label' => 'Full Name', 'type' => 'text', 'placeholder' => 'Full name', 'error_text' => 'Full name is required.', 'is_required' => true, 'options' => []],
            ['source_type' => 'custom', 'field_key' => 'meal', 'label' => 'Meal Preference', 'type' => 'dropdown', 'placeholder' => 'Select meal', 'error_text' => 'Meal preference is required.', 'is_required' => true, 'options' => ['Chicken', 'Vegetarian']],
        ];

        $this->actingAs($admin)->post(route('core.events.forms.store', $event), [
            'title' => 'VIP Registration',
            'ticket_ids' => [$ticket->id],
            'fields_payload' => json_encode($fields),
            'custom_questions_payload' => json_encode([
                ['question_name' => 'Meal Preference', 'type' => 'dropdown', 'placeholder' => 'Select meal', 'error_text' => 'Meal preference is required.', 'options' => ['Chicken', 'Vegetarian']],
            ]),
        ])->assertRedirect(route('core.events.forms.index', $event));

        $form = RegistrationForm::where('title', 'VIP Registration')->firstOrFail();
        $ticket->refresh();

        $this->assertSame($form->id, $ticket->registration_form_id);
        $this->assertCount(2, $form->fields);
        $this->assertDatabaseHas('custom_questions', ['event_id' => $event->id, 'question_name' => 'Meal Preference']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'forms.create']);

        $this->actingAs($admin)
            ->get(route('core.events.forms.preview', [$event, $form]))
            ->assertOk()
            ->assertSee('VIP Registration')
            ->assertSee('Meal Preference');

        $updatedFields = [
            ['source_type' => 'custom', 'field_key' => 'meal', 'label' => 'Meal Preference Updated', 'type' => 'dropdown', 'placeholder' => 'Select meal', 'error_text' => 'Choose a meal.', 'is_required' => false, 'options' => ['Chicken', 'Vegetarian', 'Fish']],
            ['source_type' => 'basic', 'field_key' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'Email address', 'error_text' => 'Valid email required.', 'is_required' => true, 'options' => []],
        ];

        $this->actingAs($admin)->put(route('core.events.forms.update', [$event, $form]), [
            'title' => 'VIP Registration Updated',
            'ticket_ids' => [$ticket->id],
            'fields_payload' => json_encode($updatedFields),
            'custom_questions_payload' => json_encode([]),
        ])->assertRedirect(route('core.events.forms.index', $event));

        $form->refresh();

        $this->assertSame('VIP Registration Updated', $form->title);
        $this->assertSame('active', $form->status);
        $this->assertSame('Meal Preference Updated', $form->fields()->orderBy('sort_order')->first()->label);
        $this->assertDatabaseHas('audit_logs', ['action' => 'forms.update']);

        $this->actingAs($admin)
            ->delete(route('core.events.forms.destroy', [$event, $form]))
            ->assertRedirect(route('core.events.forms.index', $event));

        $this->assertSoftDeleted('registration_forms', ['id' => $form->id]);
        $this->assertNull($ticket->fresh()->registration_form_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'forms.delete']);
    }

    public function test_form_requires_options_for_choice_fields(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);
        $ticket = $this->ticket($event, $admin);

        $this->actingAs($admin)->post(route('core.events.forms.store', $event), [
            'title' => 'Invalid Form',
            'ticket_ids' => [$ticket->id],
            'fields_payload' => json_encode([
                ['source_type' => 'custom', 'field_key' => 'country', 'label' => 'Country', 'type' => 'dropdown', 'placeholder' => 'Select country', 'error_text' => 'Country required.', 'is_required' => true, 'options' => []],
            ]),
        ])->assertSessionHasErrors('fields.0.options');
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
            'title' => 'Forms Event',
            'organiser_profile_id' => $organiser->id,
            'slug' => 'forms-event',
            'custom_url' => 'forms-event',
            'description' => 'Forms event.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Kuala Lumpur',
            'status_key' => 'draft',
        ])->assertRedirect();

        return Event::where('slug', 'forms-event')->firstOrFail();
    }

    private function ticket(Event $event, User $admin): Ticket
    {
        return $event->tickets()->create([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'name' => 'VIP Ticket',
            'description' => 'VIP ticket.',
            'currency' => 'MYR',
            'price' => 0,
            'quantity' => 50,
            'available_quantity' => 50,
            'min_quantity' => 1,
            'max_quantity' => 2,
            'sales_start_at' => now(),
            'sales_end_at' => now()->addMonth(),
            'status' => 'active',
        ]);
    }
}
