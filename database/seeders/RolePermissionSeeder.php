<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Staff Center', 'slug' => 'staff.access', 'group' => 'Staff'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'Staff'],
            ['name' => 'View PIREPs', 'slug' => 'pireps.view', 'group' => 'PIREPs'],
            ['name' => 'Approve PIREPs', 'slug' => 'pireps.approve', 'group' => 'PIREPs'],
            ['name' => 'Reject PIREPs', 'slug' => 'pireps.reject', 'group' => 'PIREPs'],
            ['name' => 'View Pilots', 'slug' => 'pilots.view', 'group' => 'Pilots'],
            ['name' => 'Manage Pilots', 'slug' => 'pilots.manage', 'group' => 'Pilots'],
            ['name' => 'View Fleet', 'slug' => 'fleet.view', 'group' => 'Fleet'],
            ['name' => 'Manage Fleet', 'slug' => 'fleet.manage', 'group' => 'Fleet'],
            ['name' => 'View Schedules', 'slug' => 'schedules.view', 'group' => 'Schedules'],
            ['name' => 'Manage Schedules', 'slug' => 'schedules.manage', 'group' => 'Schedules'],
            ['name' => 'Manage News', 'slug' => 'news.manage', 'group' => 'News'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'Settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        $chiefPilot = Role::firstOrCreate(
            ['slug' => 'chief-pilot'],
            ['name' => 'Chief Pilot', 'description' => 'Senior pilot with oversight responsibilities', 'is_staff' => true]
        );

        $moderator = Role::firstOrCreate(
            ['slug' => 'moderator'],
            ['name' => 'Moderator', 'description' => 'Can manage PIREPs and pilots', 'is_staff' => true]
        );

        $chiefPilot->permissions()->sync(
            Permission::whereIn('slug', [
                'staff.access', 'pireps.view', 'pireps.approve', 'pireps.reject',
                'pilots.view', 'pilots.manage', 'fleet.view', 'schedules.view',
            ])->pluck('id')
        );

        $moderator->permissions()->sync(
            Permission::whereIn('slug', [
                'staff.access', 'pireps.view', 'pireps.approve', 'pireps.reject',
                'pilots.view',
            ])->pluck('id')
        );
    }
}
