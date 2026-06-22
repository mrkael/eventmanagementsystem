<?php

namespace Tests\Feature\Admin\Events;

use App\Enums\EventLifecycleStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventConfiguration;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EventMasterSetupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_event_with_session_and_page_draft(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.events.store'), $this->payload([
            'sessions' => [[
                'title' => 'Opening',
                'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addWeek()->addHour()->format('Y-m-d H:i:s'),
                'capacity' => 50,
            ]],
            'page_sections' => json_encode([['id' => 'one', 'type' => 'hero', 'title' => 'Hero Banner', 'content' => 'Welcome', 'settings' => []]]),
        ]))->assertRedirect();

        $event = Event::where('title', 'Module 3 Event')->firstOrFail();
        $this->assertSame(EventLifecycleStatus::Draft, $event->status_key);
        $this->assertCount(1, $event->sessions);
        $this->assertDatabaseHas('event_page_versions', ['event_id' => $event->id, 'status' => 'draft']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'events.create']);
    }

    public function test_event_can_be_submitted_and_published(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = Event::create($this->eventRecord($admin));
        $event->pageVersions()->create(['version' => 1, 'status' => 'draft', 'sections' => []]);

        $this->actingAs($admin)->post(route('admin.events.submit', $event))->assertRedirect();
        $this->assertSame(EventLifecycleStatus::Submitted, $event->fresh()->status_key);

        $this->actingAs($admin)->post(route('admin.events.publish', $event))->assertRedirect();
        $event->refresh();

        $this->assertSame(EventLifecycleStatus::Published, $event->status_key);
        $this->assertTrue($event->is_public);
        $this->assertNotNull($event->published_page_version_id);
    }

    public function test_public_event_page_requires_published_event(): void
    {
        $this->seed([AccessControlSeeder::class, EventMasterSetupSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $event = Event::create($this->eventRecord($admin));

        $this->get(route('public.events.show', $event))->assertNotFound();
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'title' => 'Module 3 Event',
            'event_category_id' => EventCategory::first()->id,
            'event_type_id' => EventType::first()->id,
            'venue_id' => Venue::first()->id,
            'event_status_id' => EventStatus::where('key', 'draft')->first()->id,
            'event_configuration_id' => EventConfiguration::first()->id,
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
            'capacity' => 100,
            'summary' => 'A test event.',
            'description' => 'Event description.',
        ], $overrides);
    }

    private function eventRecord(User $admin): array
    {
        return $this->payload([
            'organizer_id' => $admin->id,
            'slug' => 'module-3-event',
            'status_key' => EventLifecycleStatus::Draft->value,
        ]);
    }
}
