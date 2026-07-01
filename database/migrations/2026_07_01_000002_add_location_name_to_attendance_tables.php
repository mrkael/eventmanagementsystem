<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('location_name')->nullable()->after('longitude');
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('location_name')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('location_name');
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn('location_name');
        });
    }
};
