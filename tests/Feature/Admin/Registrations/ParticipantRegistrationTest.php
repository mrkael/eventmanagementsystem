<?php

namespace Tests\Feature\Admin\Registrations;

use App\Enums\EventLifecycleStatus;
use App\Enums\ParticipantRegistrationStatus;
use App\Mail\RegistrationConfirmationMail;
use App\Mail\RegistrationWaitlistMail;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\ParticipantRegistration;
use App\Models\RegistrationForm;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EventMasterSetupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ParticipantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_build_registration_form(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin);

        $response = $this->actingAs($admin)->put(route('admin.events.registrations.builder.update', $event), [
            'title' => 'Participant Registration',
            'description' => 'Join the event.',
            'access_mode' => 'public',
            'is_enabled' => '1',
            'requires_approval' => '1',
            'allow_waitlist' => '1',
            'is_multi_step' => '1',
            'schema' => json_encode([
                'groups' => [[
                    'title' => 'Profile',
                    'step_number' => 1,
                    'questions' => [[
                        'type' => 'dropdown',
                        'label' => 'Meal preference',
                        'key' => 'meal_preference',
                        'is_required' => true,
                        'options' => ['Vegetarian', 'Standard'],
                        'validation_rules' => [],
                        'conditional_logic' => [],
                    ]],
                ]],
            ]),
        ]);

        $response->assertRedirect(route('admin.events.registrations.builder.edit', $event));
        $this->assertDatabaseHas('registration_forms', ['event_id' => $event->id, 'requires_approval' => true]);
        $this->assertDatabaseHas('registration_questions', ['key' => 'meal_preference', 'type' => 'dropdown']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'registration_forms.update']);
    }

    public function test_public_registration_can_be_submitted_and_confirmed(): void
    {
        Mail::fake();
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin, ['capacity' => 2]);
        $this->form($event);

        $response = $this->post(route('public.registrations.store', $event->slug), [
            'name' => 'Taylor Guest',
            'email' => 'taylor@example.test',
            'answers' => ['meal' => 'Standard'],
        ]);

        $response->assertRedirect(route('public.registrations.show', $event->slug));
        $registration = ParticipantRegistration::firstOrFail();
        $this->assertSame(ParticipantRegistrationStatus::Confirmed, $registration->status);
        $this->assertDatabaseHas('participant_registration_answers', ['question_key' => 'meal']);
        $this->assertDatabaseHas('attendance_qr_tokens', ['participant_registration_id' => $registration->id]);
        Mail::assertSent(RegistrationConfirmationMail::class, fn (RegistrationConfirmationMail $mail) => filled($mail->ticketToken) && str_starts_with($mail->ticketQr, 'data:image/svg+xml'));
    }

    public function test_capacity_places_extra_public_registration_on_waitlist(): void
    {
        Mail::fake();
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = $this->event($admin, ['capacity' => 1]);
        $this->form($event);

        $this->post(route('public.registrations.store', $event->slug), [
            'name' => 'First Guest',
            'email' => 'first@example.test',
            'answers' => ['meal' => 'Standard'],
        ])->assertRedirect();

        $this->post(route('public.registrations.store', $event->slug), [
            'name' => 'Second Guest',
            'email' => 'second@example.test',
            'answers' => ['meal' => 'Vegetarian'],
        ])->assertRedirect();

        $this->assertDatabaseHas('participant_registrations', ['email' => 'second@example.test', 'status' => 'waitlisted']);
        Mail::assertSent(RegistrationWaitlistMail::class);
    }

    private function event(User $admin, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organizer_id' => $admin->id,
            'event_category_id' => EventCategory::firstOrFail()->id,
            'event_type_id' => EventType::firstOrFail()->id,
            'event_status_id' => EventStatus::where('key', 'approved')->firstOrFail()->id,
            'title' => 'Registration Test Event',
            'slug' => 'registration-test-event-'.uniqid(),
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'capacity' => 100,
            'is_registration_enabled' => true,
            'is_public' => true,
            'status_key' => EventLifecycleStatus::Published,
        ], $overrides));
    }

    private function form(Event $event): RegistrationForm
    {
        $form = RegistrationForm::create([
            'event_id' => $event->id,
            'title' => 'Registration',
            'access_mode' => 'public',
            'is_enabled' => true,
            'requires_approval' => false,
            'allow_waitlist' => true,
        ]);

        $group = $form->groups()->create(['title' => 'Details', 'step_number' => 1]);
        $form->questions()->create([
            'registration_question_group_id' => $group->id,
            'type' => 'dropdown',
            'label' => 'Meal',
            'key' => 'meal',
            'is_required' => true,
            'options' => ['Standard', 'Vegetarian'],
        ]);

        return $form;
    }
}
