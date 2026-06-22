<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(
            ['code' => 'ADMIN'],
            [
                'name' => 'Administration',
                'description' => 'System administration and governance.',
                'is_active' => true,
            ],
        );

        $permissions = collect([
            ['Dashboard', 'dashboard.view', 'Dashboard', 'Access the admin dashboard.'],
            ['Update Profile', 'profile.update', 'Profile', 'Update own profile.'],
            ['View Users', 'users.view', 'Users', 'View user accounts.'],
            ['Create Users', 'users.create', 'Users', 'Create user accounts.'],
            ['Update Users', 'users.update', 'Users', 'Update user accounts.'],
            ['Delete Users', 'users.delete', 'Users', 'Delete user accounts.'],
            ['View Departments', 'departments.view', 'Departments', 'View department records.'],
            ['Create Departments', 'departments.create', 'Departments', 'Create department records.'],
            ['Update Departments', 'departments.update', 'Departments', 'Update department records.'],
            ['Delete Departments', 'departments.delete', 'Departments', 'Delete department records.'],
            ['View Roles', 'roles.view', 'Roles', 'View role records.'],
            ['Create Roles', 'roles.create', 'Roles', 'Create role records.'],
            ['Update Roles', 'roles.update', 'Roles', 'Update role records.'],
            ['Delete Roles', 'roles.delete', 'Roles', 'Delete role records.'],
            ['View Permissions', 'permissions.view', 'Permissions', 'View permission records.'],
            ['Create Permissions', 'permissions.create', 'Permissions', 'Create permission records.'],
            ['Update Permissions', 'permissions.update', 'Permissions', 'Update permission records.'],
            ['Delete Permissions', 'permissions.delete', 'Permissions', 'Delete permission records.'],
            ['View Audit Logs', 'audit_logs.view', 'Audit Logs', 'View system audit trail.'],
            ['View Organisers', 'organisers.view', 'Organiser Profile', 'View organiser profiles.'],
            ['Create Organisers', 'organisers.create', 'Organiser Profile', 'Create organiser profiles.'],
            ['Update Organisers', 'organisers.update', 'Organiser Profile', 'Update organiser profiles.'],
            ['Delete Organisers', 'organisers.delete', 'Organiser Profile', 'Delete organiser profiles.'],
            ['View Contacts', 'contacts.view', 'Contacts', 'View contacts and groups.'],
            ['Create Contacts', 'contacts.create', 'Contacts', 'Create and import contacts.'],
            ['Update Contacts', 'contacts.update', 'Contacts', 'Update contacts.'],
            ['Delete Contacts', 'contacts.delete', 'Contacts', 'Delete contacts.'],
            ['Export Contacts', 'contacts.export', 'Contacts', 'Export contacts.'],
            ['View Emails', 'emails.view', 'Emails', 'View email templates and campaigns.'],
            ['Create Emails', 'emails.create', 'Emails', 'Create email templates.'],
            ['Send Emails', 'emails.send', 'Emails', 'Send or schedule email campaigns.'],
            ['View Reports', 'reports.view', 'Reports', 'View event reports.'],
            ['Create Reports', 'reports.create', 'Reports', 'Create custom event reports.'],
            ['Export Reports', 'reports.export', 'Reports', 'Export event reports.'],
            ['View Event Categories', 'event_categories.view', 'Event Categories', 'View event categories.'],
            ['Create Event Categories', 'event_categories.create', 'Event Categories', 'Create event categories.'],
            ['Update Event Categories', 'event_categories.update', 'Event Categories', 'Update event categories.'],
            ['Delete Event Categories', 'event_categories.delete', 'Event Categories', 'Delete event categories.'],
            ['View Event Types', 'event_types.view', 'Event Types', 'View event types.'],
            ['Create Event Types', 'event_types.create', 'Event Types', 'Create event types.'],
            ['Update Event Types', 'event_types.update', 'Event Types', 'Update event types.'],
            ['Delete Event Types', 'event_types.delete', 'Event Types', 'Delete event types.'],
            ['View Venues', 'venues.view', 'Venues', 'View venues.'],
            ['Create Venues', 'venues.create', 'Venues', 'Create venues.'],
            ['Update Venues', 'venues.update', 'Venues', 'Update venues.'],
            ['Delete Venues', 'venues.delete', 'Venues', 'Delete venues.'],
            ['View Event Statuses', 'event_statuses.view', 'Event Statuses', 'View event statuses.'],
            ['Create Event Statuses', 'event_statuses.create', 'Event Statuses', 'Create event statuses.'],
            ['Update Event Statuses', 'event_statuses.update', 'Event Statuses', 'Update event statuses.'],
            ['Delete Event Statuses', 'event_statuses.delete', 'Event Statuses', 'Delete event statuses.'],
            ['View Event Configurations', 'event_configurations.view', 'Event Configurations', 'View event configurations.'],
            ['Create Event Configurations', 'event_configurations.create', 'Event Configurations', 'Create event configurations.'],
            ['Update Event Configurations', 'event_configurations.update', 'Event Configurations', 'Update event configurations.'],
            ['Delete Event Configurations', 'event_configurations.delete', 'Event Configurations', 'Delete event configurations.'],
            ['View Events', 'events.view', 'Events', 'View events.'],
            ['Create Events', 'events.create', 'Events', 'Create events.'],
            ['Update Events', 'events.update', 'Events', 'Update events.'],
            ['Delete Events', 'events.delete', 'Events', 'Delete events.'],
            ['Submit Events', 'events.submit', 'Events', 'Submit events for review.'],
            ['Publish Events', 'events.publish', 'Events', 'Publish events and event pages.'],
            ['View Registration Forms', 'registration_forms.view', 'Registration Forms', 'View event registration forms.'],
            ['Manage Registration Forms', 'registration_forms.manage', 'Registration Forms', 'Build event registration forms.'],
            ['View Registrations', 'registrations.view', 'Registrations', 'View participant registrations.'],
            ['Create Registrations', 'registrations.create', 'Registrations', 'Create participant registrations.'],
            ['Update Registrations', 'registrations.update', 'Registrations', 'Update participant registration statuses.'],
            ['Approve Registrations', 'registrations.approve', 'Registrations', 'Approve pending registrations.'],
            ['Invite Registrations', 'registrations.invite', 'Registrations', 'Create invite registration links.'],
            ['Delete Registrations', 'registrations.delete', 'Registrations', 'Delete participant registrations.'],
            ['View Attendance', 'attendance.view', 'Attendance', 'View attendance dashboards and scan history.'],
            ['Scan Attendance', 'attendance.scan', 'Attendance', 'Use QR scanner for check-in and checkout.'],
            ['Override Attendance', 'attendance.override', 'Attendance', 'Manually override attendance records.'],
            ['Export Attendance', 'attendance.export', 'Attendance', 'Export attendance reports.'],
        ])->mapWithKeys(function (array $permission) {
            $record = Permission::updateOrCreate(
                ['key' => $permission[1]],
                [
                    'name' => $permission[0],
                    'group' => $permission[2],
                    'description' => $permission[3],
                ],
            );

            return [$record->key => $record];
        });

        $roleDefinitions = [
            'super-admin' => ['Super Admin', $permissions->keys()->all()],
            'admin' => ['Admin', $permissions->keys()->reject(fn ($key) => $key === 'permissions.delete')->all()],
            'organizer' => ['Editor', ['dashboard.view', 'profile.update', 'organisers.view', 'organisers.create', 'organisers.update', 'contacts.view', 'contacts.create', 'contacts.update', 'contacts.export', 'emails.view', 'emails.create', 'emails.send', 'reports.view', 'reports.create', 'reports.export', 'events.view', 'events.create', 'events.update', 'events.submit', 'registration_forms.view', 'registration_forms.manage', 'registrations.view', 'registrations.create', 'registrations.update', 'registrations.invite', 'attendance.view', 'attendance.scan', 'attendance.override', 'attendance.export']],
            'management-viewer' => ['Reviewer', ['dashboard.view', 'profile.update', 'organisers.view', 'contacts.view', 'emails.view', 'reports.view', 'reports.export', 'events.view', 'registration_forms.view', 'registrations.view', 'attendance.view', 'attendance.export']],
            'approver' => ['Approver', ['dashboard.view', 'profile.update']],
            'staff' => ['Staff', ['dashboard.view', 'profile.update']],
        ];

        foreach ($roleDefinitions as $key => [$name, $permissionKeys]) {
            $role = Role::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $name,
                    'description' => "{$name} access profile.",
                    'is_system' => true,
                ],
            );

            $role->permissions()->sync(
                $permissions->only($permissionKeys)->pluck('id')->all(),
            );
        }

        $adminEmail = env('EMS_SUPER_ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('EMS_SUPER_ADMIN_PASSWORD', 'password');

        if (app()->isProduction() && $adminPassword === 'password') {
            throw new RuntimeException('Set EMS_SUPER_ADMIN_PASSWORD before seeding production.');
        }

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('EMS_SUPER_ADMIN_NAME', 'Super Admin'),
                'password' => Hash::make($adminPassword),
                'department_id' => $department->id,
                'position' => 'System Administrator',
                'status' => UserStatus::Active->value,
            ],
        );

        $admin->roles()->sync([Role::where('key', 'super-admin')->value('id')]);
    }
}
