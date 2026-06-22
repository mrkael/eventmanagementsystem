<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_department_and_audit_log_is_written(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.departments.store'), [
            'name' => 'Corporate Affairs',
            'code' => 'CA',
            'description' => 'Corporate event coordination.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.departments.index'));

        $this->assertDatabaseHas('departments', ['name' => 'Corporate Affairs', 'code' => 'CA']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'departments.create']);
    }

    public function test_department_with_users_cannot_be_deleted(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $department = Department::create(['name' => 'Finance', 'code' => 'FIN', 'is_active' => true]);
        $user = User::factory()->create(['department_id' => $department->id, 'status' => 'active']);
        $user->roles()->attach(Role::where('key', 'staff')->value('id'));

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department))
            ->assertSessionHasErrors('department');

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
    }
}
