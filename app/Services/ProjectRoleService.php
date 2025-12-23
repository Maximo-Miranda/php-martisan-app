<?php

namespace App\Services;

use App\Models\Project;
use Spatie\Permission\Models\Role;

class ProjectRoleService
{
    /**
     * Role definitions with their permissions.
     *
     * @var array<string, list<string>>
     */
    protected array $rolePermissions = [
        'Project Owner' => [
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
        ],
        'Project Admin' => [
            'view project',
            'update project',
            'invite members',
            'remove members',
            'view content',
            'create content',
            'edit content',
            'delete content',
        ],
        'Project Editor' => [
            'view project',
            'view content',
            'create content',
            'edit content',
        ],
        'Project Viewer' => [
            'view project',
            'view content',
        ],
    ];

    /**
     * Create all project-scoped roles for a given project.
     */
    public function createRolesForProject(Project $project): void
    {
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'project_id' => $project->id,
            ]);

            $role->syncPermissions($permissions);
        }
    }

    /**
     * Get available roles for project assignment.
     *
     * @return list<string>
     */
    public function getAssignableRoles(): array
    {
        return array_keys($this->rolePermissions);
    }

    /**
     * Get roles that can be assigned to invited users.
     *
     * @return list<string>
     */
    public function getInvitableRoles(): array
    {
        return ['Project Admin', 'Project Editor', 'Project Viewer'];
    }
}
