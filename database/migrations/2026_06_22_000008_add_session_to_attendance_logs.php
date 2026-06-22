<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->foreignId('event_session_id')->nullable()->after('event_id')->constrained()->nullOnDelete();
            $table->index(['event_id', 'event_session_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['event_session_id']);
            $table->dropIndex(['event_id', 'event_session_id']);
            $table->dropColumn('event_session_id');
        });
    }
};
