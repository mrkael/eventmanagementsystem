<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('registration_forms', 'status')) {
                $table->string('status')->default('active')->index()->after('description');
            }
            if (! Schema::hasColumn('registration_forms', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('event_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('registration_forms', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('registration_forms', 'is_enabled')) {
            DB::table('registration_forms')->update([
                'status' => DB::raw("case when is_enabled = 1 then 'active' else 'inactive' end"),
            ]);
        }

        Schema::table('registration_form_fields', function (Blueprint $table) {
            if (! Schema::hasColumn('registration_form_fields', 'source_type')) {
                $table->string('source_type')->default('custom')->after('registration_form_id');
            }
            if (! Schema::hasColumn('registration_form_fields', 'field_key')) {
                $table->string('field_key')->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('registration_form_fields', 'error_text')) {
                $table->string('error_text')->nullable()->after('placeholder');
            }
            if (! Schema::hasColumn('registration_form_fields', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (Schema::hasColumn('registration_form_fields', 'key') && Schema::hasColumn('registration_form_fields', 'field_key')) {
            DB::table('registration_form_fields')->whereNull('field_key')->update(['field_key' => DB::raw('`key`')]);
        }

        Schema::create('custom_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('question_name');
            $table->string('type');
            $table->string('placeholder')->nullable();
            $table->string('error_text')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['event_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_questions');

        Schema::table('registration_form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('registration_form_fields', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('registration_form_fields', 'error_text')) {
                $table->dropColumn('error_text');
            }
            if (Schema::hasColumn('registration_form_fields', 'field_key')) {
                $table->dropColumn('field_key');
            }
            if (Schema::hasColumn('registration_form_fields', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });

        Schema::table('registration_forms', function (Blueprint $table) {
            if (Schema::hasColumn('registration_forms', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('registration_forms', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('registration_forms', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
