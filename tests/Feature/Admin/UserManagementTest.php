<?php

namespace Tests\Feature\Admin;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_user_with_role_and_department(): void
    {
        $this->seed(AccessControlSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $department = Department::firstOrFail();
        $role = Role::where('key', 'organizer')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Event Organizer',
            'email' => 'organizer@example.com',
            'department_id' => $department->id,
            'position' => 'Organizer',
            'status' => UserStatus::Active->value,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_ids' => [$role->id],
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'organizer@example.com')->firstOrFail();

        $this->assertTrue($user->roles()->where('key', 'organizer')->exists());
        $this->assertDatabaseHas('audit_logs', ['action' => 'users.create']);
    }

    public function test_user_cannot_delete_own_account(): void
    {
        $this->seed(AccessControlSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertNotSoftDeleted($admin);
    }
}
