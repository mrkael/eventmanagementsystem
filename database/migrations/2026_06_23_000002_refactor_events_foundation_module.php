<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('organizer_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('events', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('event_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tickets', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('tickets', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('events', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
