<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['event_id', 'created_at']);
        });

        Schema::table('event_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('event_sessions', 'event_agenda_id')) {
                $table->foreignId('event_agenda_id')->nullable()->after('event_id')->constrained('event_agendas')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('event_sessions', 'session_type')) {
                $table->string('session_type')->default('session')->after('description');
            }
            if (! Schema::hasColumn('event_sessions', 'venue_name')) {
                $table->string('venue_name')->nullable()->after('capacity');
            }
            if (! Schema::hasColumn('event_sessions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('sort_order')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('event_sessions', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('event_sessions', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_sessions', function (Blueprint $table) {
            foreach (['event_agenda_id', 'created_by', 'updated_by'] as $column) {
                if (Schema::hasColumn('event_sessions', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['session_type', 'venue_name', 'deleted_at'] as $column) {
                if (Schema::hasColumn('event_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('event_agendas');
    }
};
