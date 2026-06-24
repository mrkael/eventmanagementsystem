<?php

namespace Tests\Feature\Admin;

use App\Models\OrganiserProfile;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganiserProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_organiser_profile(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->post(route('core.organisers.store'), [
            'name' => 'Acme Events',
            'email' => 'sender@acme.test',
            'phone' => '+60123456789',
            'website' => 'https://acme.test',
            'address' => 'Kuala Lumpur',
            'description' => 'Enterprise event organiser.',
            'status' => 'active',
        ])->assertRedirect();

        $profile = OrganiserProfile::where('email', 'sender@acme.test')->firstOrFail();

        $this->assertSame($admin->id, $profile->created_by);
        $this->assertSame($admin->id, $profile->updated_by);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organisers.create']);

        $this->actingAs($admin)
            ->get(route('core.organisers.index', ['search' => 'sender@acme.test', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Acme Events')
            ->assertSee('sender@acme.test')
            ->assertSee('0');

        $this->actingAs($admin)->put(route('core.organisers.update', $profile), [
            'name' => 'Acme Event Group',
            'email' => 'events@acme.test',
            'phone' => '+60987654321',
            'website' => 'https://events.acme.test',
            'address' => 'Cyberjaya',
            'description' => 'Updated organiser profile.',
            'status' => 'inactive',
        ])->assertRedirect(route('core.organisers.show', $profile));

        $this->assertDatabaseHas('organiser_profiles', [
            'id' => $profile->id,
            'name' => 'Acme Event Group',
            'email' => 'events@acme.test',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organisers.update']);

        $this->actingAs($admin)
            ->delete(route('core.organisers.destroy', $profile))
            ->assertRedirect(route('core.organisers.index'));

        $this->assertSoftDeleted('organiser_profiles', ['id' => $profile->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organisers.delete']);
    }

    public function test_organiser_profile_requires_valid_unique_email(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        OrganiserProfile::create([
            'name' => 'Existing Organiser',
            'email' => 'existing@example.test',
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('core.organisers.store'), [
            'name' => '',
            'email' => 'existing@example.test',
            'website' => 'not-a-url',
            'status' => 'archived',
        ])->assertSessionHasErrors(['name', 'email', 'website', 'status']);
    }
}
