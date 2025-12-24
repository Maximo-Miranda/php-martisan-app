<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Project-scoped permissions
        $projectPermissions = [
            'view project',
            'update project',
            'delete project',
            'invite members',
            'remove members',
            'manage roles',
            'view content',
            'create content',
            'edit content',
            'delete content',
        ];

        foreach ($projectPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create global roles (project_id = null) for platform-wide access
        // These roles bypass project-scoped restrictions
        $globalRoles = [
            'Super Admin' => 'Full platform access, can manage all projects and users',
            'Platform Moderator' => 'Can moderate content across all projects',
        ];

        foreach ($globalRoles as $roleName => $description) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'project_id' => null,
            ]);
        }

        // Note: Project-scoped roles (Project Owner, Project Admin, etc.)
        // are created dynamically when a project is created.

        $this->command->info('Permissions seeded successfully!');
        $this->command->info('Global roles created: '.implode(', ', array_keys($globalRoles)));
        $this->command->info('Project roles will be created per-project.');
    }
}
