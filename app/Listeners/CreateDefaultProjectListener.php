<?php

namespace App\Listeners;

use App\Models\Project;
use App\Services\ProjectRoleService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;

class CreateDefaultProjectListener
{
    public function __construct(
        protected ProjectRoleService $roleService
    ) {}

    /**
     * Handle the event.
     *
     * Creates a default project for the user when their email is verified.
     */
    public function handle(Verified $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        // Skip if user already has projects (e.g., accepted an invitation before verifying)
        if ($user->projects()->exists()) {
            return;
        }

        $project = Project::create([
            'name' => "{$user->name}'s Project",
            'slug' => Str::slug("{$user->name}-project-".Str::random(6)),
            'owner_id' => $user->id,
        ]);

        // Create project-scoped roles
        $this->roleService->createRolesForProject($project);

        // Add user as a member of the project
        $user->projects()->attach($project->id);

        // Set as current project
        $user->update(['current_project_id' => $project->id]);

        // Assign Project Owner role scoped to this project
        setPermissionsTeamId($project->id);
        $user->assignRole('Project Owner');
    }
}
