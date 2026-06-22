<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use App\Models\EventConfiguration;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventMasterSetupSeeder extends Seeder
{
    public function run(): void
    {
        collect(['Training', 'Town Hall', 'Conference', 'Workshop'])->each(function (string $name, int $index) {
            EventCategory::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => "{$name} events.", 'is_active' => true, 'sort_order' => $index + 1],
            );
        });

        collect([
            ['Internal', false],
            ['External', true],
            ['Hybrid', true],
        ])->each(function (array $type, int $index) {
            EventType::firstOrCreate(
                ['slug' => Str::slug($type[0])],
                ['name' => $type[0], 'description' => "{$type[0]} event type.", 'requires_approval' => $type[1], 'is_active' => true, 'sort_order' => $index + 1],
            );
        });

        Venue::firstOrCreate(
            ['code' => 'MAIN-HALL'],
            ['name' => 'Main Hall', 'capacity' => 300, 'location' => 'Headquarters', 'description' => 'Primary corporate event hall.', 'status' => 'active'],
        );

        collect([
            ['Draft', 'draft', 'slate', false],
            ['Submitted', 'submitted', 'blue', false],
            ['Approved', 'approved', 'emerald', false],
            ['Completed', 'completed', 'violet', true],
            ['Cancelled', 'cancelled', 'red', true],
        ])->each(function (array $status, int $index) {
            EventStatus::firstOrCreate(
                ['key' => $status[1]],
                ['name' => $status[0], 'color' => $status[2], 'is_terminal' => $status[3], 'is_active' => true, 'sort_order' => $index + 1],
            );
        });

        EventConfiguration::firstOrCreate(
            ['name' => 'Default Event Configuration'],
            [
                'is_default' => true,
                'registration_rules' => [
                    'requires_approval' => false,
                    'allow_waitlist' => true,
                    'private_by_default' => false,
                    'open_days_before' => 30,
                ],
                'qr_rules' => [
                    'enabled' => true,
                    'expires_after_event_hours' => 24,
                    'allow_reuse' => false,
                ],
                'capacity_rules' => [
                    'enforce_limit' => true,
                    'waitlist_when_full' => true,
                    'overbooking_limit' => 0,
                ],
                'email_settings' => [
                    'send_confirmation' => true,
                    'send_reminder' => true,
                    'reminder_hours_before' => 24,
                ],
            ],
        );
    }
}
