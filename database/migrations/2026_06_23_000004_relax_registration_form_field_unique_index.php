<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_form_fields', function (Blueprint $table) {
            try {
                $table->index('registration_form_id', 'registration_form_fields_form_id_idx');
            } catch (Throwable) {
            }

            try {
                $table->dropUnique('registration_form_fields_registration_form_id_key_unique');
            } catch (Throwable) {
            }

            try {
                $table->index(['registration_form_id', 'sort_order'], 'registration_form_fields_form_sort_idx');
            } catch (Throwable) {
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_form_fields', function (Blueprint $table) {
            try {
                $table->dropIndex('registration_form_fields_form_sort_idx');
            } catch (Throwable) {
            }

            try {
                $table->unique(['registration_form_id', 'key']);
            } catch (Throwable) {
            }

            try {
                $table->dropIndex('registration_form_fields_form_id_idx');
            } catch (Throwable) {
            }
        });
    }
};
