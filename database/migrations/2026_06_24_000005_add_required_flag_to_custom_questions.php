<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('custom_questions', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('error_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_questions', function (Blueprint $table) {
            if (Schema::hasColumn('custom_questions', 'is_required')) {
                $table->dropColumn('is_required');
            }
        });
    }
};
