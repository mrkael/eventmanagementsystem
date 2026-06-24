<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organiser_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('organiser_profiles', 'description')) {
                $table->text('description')->nullable()->after('address');
            }
            if (! Schema::hasColumn('organiser_profiles', 'status')) {
                $table->string('status')->default('active')->index()->after('logo_path');
            }
            if (! Schema::hasColumn('organiser_profiles', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('organiser_profiles', 'is_active')) {
            DB::table('organiser_profiles')->update([
                'status' => DB::raw("case when is_active = 1 then 'active' else 'inactive' end"),
            ]);

            Schema::table('organiser_profiles', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        Schema::dropIfExists('organiser_profile_user');
    }

    public function down(): void
    {
        Schema::create('organiser_profile_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organiser_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['organiser_profile_id', 'user_id']);
        });

        Schema::table('organiser_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('organiser_profiles', 'is_active')) {
                $table->boolean('is_active')->default(true)->index()->after('address');
            }
        });

        DB::table('organiser_profiles')->update([
            'is_active' => DB::raw("case when status = 'active' then 1 else 0 end"),
        ]);

        Schema::table('organiser_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('organiser_profiles', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('organiser_profiles', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('organiser_profiles', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
