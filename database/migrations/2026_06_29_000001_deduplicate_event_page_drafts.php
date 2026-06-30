<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate draft EventPages per event, keeping the most recently created one.
        // Duplicates can accumulate from concurrent requests or prior code paths.
        $duplicates = DB::table('event_pages')
            ->select('event_id', DB::raw('MAX(id) as keep_id'))
            ->where('status', 'draft')
            ->whereNull('deleted_at')
            ->groupBy('event_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $row) {
            DB::table('event_pages')
                ->where('event_id', $row->event_id)
                ->where('status', 'draft')
                ->whereNull('deleted_at')
                ->where('id', '!=', $row->keep_id)
                ->update(['deleted_at' => now()]);
        }
    }

    public function down(): void
    {
        // No rollback — restoring soft-deleted duplicates is not meaningful.
    }
};
