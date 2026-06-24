<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_records', 'ticket_id')) {
                $table->foreignId('ticket_id')->nullable()->after('registration_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('attendance_records', 'status')) {
                $table->string('status')->default('checked_in')->after('notes')->index();
            }
            if (! Schema::hasColumn('attendance_records', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('attendance_scan_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_scan_logs', 'ticket_id')) {
                $table->foreignId('ticket_id')->nullable()->after('registration_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('attendance_scan_logs', 'qr_token')) {
                $table->text('qr_token')->nullable()->after('ticket_id');
            }
            if (! Schema::hasColumn('attendance_scan_logs', 'scan_result')) {
                $table->string('scan_result')->nullable()->after('result')->index();
            }
            if (! Schema::hasColumn('attendance_scan_logs', 'scanned_at')) {
                $table->timestamp('scanned_at')->nullable()->after('scanned_by')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_scan_logs', function (Blueprint $table) {
            foreach (['ticket_id'] as $column) {
                if (Schema::hasColumn('attendance_scan_logs', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['qr_token', 'scan_result', 'scanned_at'] as $column) {
                if (Schema::hasColumn('attendance_scan_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_records', 'ticket_id')) {
                $table->dropConstrainedForeignId('ticket_id');
            }

            foreach (['status', 'deleted_at'] as $column) {
                if (Schema::hasColumn('attendance_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
