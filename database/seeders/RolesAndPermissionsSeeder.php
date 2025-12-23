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

        // Create global permissions (team_id = null)
        $globalPermissions = [
            'access admin panel',
            'manage users',
            'manage all projects',
        ];

        foreach ($globalPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create global roles (team_id = null) - Super Admin
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            'project_id' => null, // Global role
        ]);
        $superAdmin->syncPermissions($globalPermissions);

        // Project-scoped permissions (will be created per-project via teams feature)
        // These are template permissions that will be applied to project-scoped roles
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

        // Note: Project-scoped roles (Project Owner, Project Admin, etc.)
        // are created dynamically when a project is created.
        // This seeder just ensures the permissions exist.

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Global roles created: Super Admin');
        $this->command->info('Project roles will be created per-project.');
    }
}
