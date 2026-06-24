<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventConfiguration;
use App\Models\EventPage;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\OrganiserProfile;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Venue;
use App\Services\Core\MicrositeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleEventSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::where('email', env('EMS_SUPER_ADMIN_EMAIL', 'admin@example.com'))->first()
                ?? User::query()->firstOrFail();

            $organiser = OrganiserProfile::withTrashed()->updateOrCreate(
                ['email' => 'sample.organiser@example.com'],
                [
                    'name' => 'Sample Organiser',
                    'phone' => '+603 1234 5678',
                    'website' => 'https://example.com',
                    'address' => 'Kuala Lumpur, Malaysia',
                    'description' => 'Reusable organiser profile for event testing.',
                    'status' => 'active',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'deleted_at' => null,
                ],
            );

            $category = EventCategory::where('slug', 'conference')->first() ?? EventCategory::query()->first();
            $type = EventType::where('slug', 'external')->first() ?? EventType::query()->first();
            $venue = Venue::where('code', 'MAIN-HALL')->first() ?? Venue::query()->first();
            $status = EventStatus::where('key', 'approved')->first() ?? EventStatus::where('key', 'draft')->first() ?? EventStatus::query()->first();
            $configuration = EventConfiguration::where('is_default', true)->first() ?? EventConfiguration::query()->first();

            $event = Event::withTrashed()->updateOrCreate(
                ['slug' => 'sample-event'],
                [
                    'organiser_profile_id' => $organiser->id,
                    'organizer_id' => $admin->id,
                    'event_category_id' => $category?->id,
                    'event_type_id' => $type?->id,
                    'venue_id' => $venue?->id,
                    'event_status_id' => $status?->id,
                    'event_configuration_id' => $configuration?->id,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'title' => 'Sample Event',
                    'custom_url' => 'sample-event',
                    'description' => 'A ready-to-test sample event with ticket, form, site, and confirmation email setup.',
                    'summary' => 'Reusable sample event for testing.',
                    'starts_at' => now()->addWeeks(2)->setTime(9, 0),
                    'ends_at' => now()->addWeeks(2)->setTime(17, 0),
                    'location' => 'Main Hall, Headquarters',
                    'capacity' => 100,
                    'is_registration_enabled' => true,
                    'is_public' => true,
                    'status_key' => 'published',
                    'published_at' => now(),
                    'registration_opens_at' => now()->subDay(),
                    'registration_closes_at' => now()->addWeeks(2)->subDay(),
                    'allow_duplicate_email' => false,
                    'sender_name' => $organiser->name,
                    'sender_email' => $organiser->email,
                    'deleted_at' => null,
                ],
            );

            $form = RegistrationForm::withTrashed()->updateOrCreate(
                ['event_id' => $event->id, 'title' => 'Sample Registration Form'],
                [
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'description' => null,
                    'status' => 'active',
                    'access_mode' => 'public',
                    'is_enabled' => true,
                    'requires_approval' => false,
                    'allow_waitlist' => false,
                    'is_multi_step' => false,
                    'deleted_at' => null,
                ],
            );

            $ticket = Ticket::withTrashed()->updateOrCreate(
                ['event_id' => $event->id, 'name' => 'General Admission'],
                [
                    'registration_form_id' => $form->id,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'description' => 'Standard admission ticket for the sample event.',
                    'currency' => 'MYR',
                    'price' => 0,
                    'quantity' => 100,
                    'available_quantity' => 100,
                    'min_quantity' => 1,
                    'max_quantity' => 5,
                    'is_hidden' => false,
                    'sales_start_at' => now()->subDay(),
                    'sales_end_at' => now()->addWeeks(2)->subDay(),
                    'status' => 'active',
                    'deleted_at' => null,
                ],
            );

            $this->syncFields($form);

            $page = EventPage::updateOrCreate(
                ['event_id' => $event->id, 'status' => 'published'],
                [
                    'template' => 'Default Event Site',
                    'published_at' => now(),
                    'settings' => [],
                ],
            );

            $page->sections()->delete();
            foreach (app(MicrositeService::class)->defaultSections($event) as $index => $section) {
                $page->sections()->create([...$section, 'sort_order' => $index]);
            }

            $event->tickets()->whereKey($ticket->id)->update(['registration_form_id' => $form->id]);
        });
    }

    private function syncFields(RegistrationForm $form): void
    {
        $form->fields()->delete();

        collect([
            ['field_key' => 'full_name', 'label' => 'Full Name', 'type' => 'text', 'placeholder' => 'Full name', 'is_required' => true],
            ['field_key' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'Email address', 'is_required' => true],
            ['field_key' => 'phone_number', 'label' => 'Phone Number', 'type' => 'text', 'placeholder' => 'Phone number', 'is_required' => false],
            ['field_key' => 'organization', 'label' => 'Organization', 'type' => 'text', 'placeholder' => 'Organization', 'is_required' => false],
            ['field_key' => 'designation', 'label' => 'Designation', 'type' => 'text', 'placeholder' => 'Designation', 'is_required' => false],
        ])->each(function (array $field, int $index) use ($form) {
            RegistrationFormField::create([
                'registration_form_id' => $form->id,
                'source_type' => 'basic',
                'field_key' => $field['field_key'],
                'key' => $field['field_key'],
                'label' => $field['label'],
                'type' => $field['type'],
                'placeholder' => $field['placeholder'],
                'error_text' => $field['label'].' is required.',
                'is_required' => $field['is_required'],
                'options' => null,
                'validation_rules' => $field['is_required'] ? ['required'] : null,
                'sort_order' => $index + 1,
            ]);
        });
    }
}
