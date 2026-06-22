<?php

namespace Tests\Feature\Admin\EventSetup;

use App\Models\EventConfiguration;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventMasterSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_event_category(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.event-categories.store'), [
            'name' => 'Leadership Briefing',
            'slug' => 'leadership-briefing',
            'description' => 'Executive communication events.',
            'is_active' => '1',
            'sort_order' => 10,
        ])->assertRedirect(route('admin.event-categories.index'));

        $this->assertDatabaseHas('event_categories', ['slug' => 'leadership-briefing']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event_categories.create']);
    }

    public function test_event_type_used_by_future_event_cannot_be_deleted(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $category = EventCategory::create(['name' => 'Protected Category', 'slug' => 'protected-category', 'is_active' => true]);
        $status = EventStatus::create(['name' => 'Draft', 'key' => 'draft', 'is_active' => true]);
        $type = EventType::create(['name' => 'Protected Type', 'slug' => 'protected-type', 'is_active' => true]);

        Event::create([
            'organizer_id' => $admin->id,
            'event_category_id' => $category->id,
            'event_type_id' => $type->id,
            'event_status_id' => $status->id,
            'title' => 'Protected Future Event',
            'slug' => 'protected-future-event',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'capacity' => 100,
            'status_key' => 'draft',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.event-types.destroy', $type))
            ->assertSessionHasErrors('type');

        $this->assertDatabaseHas('event_types', ['id' => $type->id, 'deleted_at' => null]);
    }

    public function test_event_configuration_default_is_unique(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        EventConfiguration::create([
            'name' => 'Existing Default',
            'is_default' => true,
            'registration_rules' => ['requires_approval' => false, 'allow_waitlist' => true, 'private_by_default' => false, 'open_days_before' => 30],
            'qr_rules' => ['enabled' => true, 'expires_after_event_hours' => 24, 'allow_reuse' => false],
            'capacity_rules' => ['enforce_limit' => true, 'waitlist_when_full' => true, 'overbooking_limit' => 0],
            'email_settings' => ['send_confirmation' => true, 'send_reminder' => true, 'reminder_hours_before' => 24],
        ]);

        $this->actingAs($admin)->post(route('admin.event-configurations.store'), [
            'name' => 'New Default',
            'is_default' => '1',
            'registration_open_days_before' => 14,
            'qr_enabled' => '1',
            'qr_expires_after_event_hours' => 12,
            'capacity_enforce_limit' => '1',
            'capacity_waitlist_when_full' => '1',
            'capacity_overbooking_limit' => 5,
            'email_send_confirmation' => '1',
            'email_send_reminder' => '1',
            'email_reminder_hours_before' => 48,
        ])->assertRedirect(route('admin.event-configurations.index'));

        $this->assertTrue(EventConfiguration::where('is_default', true)->where('name', 'New Default')->exists());
        $this->assertFalse(EventConfiguration::where('name', 'Existing Default')->firstOrFail()->is_default);
    }
}
