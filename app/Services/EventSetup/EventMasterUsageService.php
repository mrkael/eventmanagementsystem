<?php

namespace App\Services\EventSetup;

use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventMasterUsageService
{
    public function isUsed(Model $model): bool
    {
        if (! Schema::hasTable('events')) {
            return false;
        }

        $column = match ($model::class) {
            EventCategory::class => 'event_category_id',
            EventType::class => 'event_type_id',
            Venue::class => 'venue_id',
            EventStatus::class => 'event_status_id',
            default => null,
        };

        if (! $column || ! Schema::hasColumn('events', $column)) {
            return false;
        }

        return DB::table('events')->where($column, $model->getKey())->exists();
    }
}
