<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('key', 'organizer')->first();
        $permission = DB::table('permissions')->where('key', 'events.publish')->first();

        if ($role && $permission) {
            DB::table('permission_role')->insertOrIgnore([
                'role_id' => $role->id,
                'permission_id' => $permission->id,
            ]);
        }
    }

    public function down(): void
    {
        $role = DB::table('roles')->where('key', 'organizer')->first();
        $permission = DB::table('permissions')->where('key', 'events.publish')->first();

        if ($role && $permission) {
            DB::table('permission_role')
                ->where('role_id', $role->id)
                ->where('permission_id', $permission->id)
                ->delete();
        }
    }
};
