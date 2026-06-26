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
            if (! Schema::hasColumn('organiser_profiles', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->unique('user_id');
            }
        });

        DB::table('organiser_profiles')
            ->join('users', 'organiser_profiles.email', '=', 'users.email')
            ->whereNull('organiser_profiles.user_id')
            ->update(['organiser_profiles.user_id' => DB::raw('users.id')]);

        $roleId = DB::table('roles')->where('key', 'organizer')->value('id');
        if ($roleId) {
            DB::table('organiser_profiles')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->pluck('user_id')
                ->unique()
                ->each(fn ($userId) => DB::table('role_user')->insertOrIgnore([
                    'role_id' => $roleId,
                    'user_id' => $userId,
                ]));
        }
    }

    public function down(): void
    {
        Schema::table('organiser_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('organiser_profiles', 'user_id')) {
                $table->dropUnique(['user_id']);
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
