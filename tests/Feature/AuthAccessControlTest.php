<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_super_admin_can_login_and_reach_dashboard(): void
    {
        $this->seed(AccessControlSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'admin@example.com')->first());
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login']);
    }

    public function test_self_registration_is_disabled_by_default(): void
    {
        $this->seed(AccessControlSeeder::class);

        $this->get('/register')->assertNotFound();
    }

    public function test_staff_cannot_access_role_management(): void
    {
        $this->seed(AccessControlSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('key', 'staff')->value('id'));

        $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_super_admin_can_view_audit_logs(): void
    {
        $this->seed(AccessControlSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Audit Logs');
    }

    public function test_system_role_key_cannot_be_changed(): void
    {
        $this->seed(AccessControlSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $role = Role::where('key', 'super-admin')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => 'Super Administrator',
            'key' => 'renamed-super-admin',
            'description' => 'Renamed label only.',
            'permission_ids' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'key' => 'super-admin',
            'name' => 'Super Administrator',
        ]);
    }
}
