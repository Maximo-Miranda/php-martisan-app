<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;

class ProjectInvitationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectInvitation $invitation, Project $project): bool
    {
        return $this->canManageInvitations($user, $project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->canManageInvitations($user, $project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectInvitation $invitation, Project $project): bool
    {
        return $this->canManageInvitations($user, $project);
    }

    /**
     * Determine whether the user can resend the invitation.
     */
    public function resend(User $user, ProjectInvitation $invitation, Project $project): bool
    {
        return $this->canManageInvitations($user, $project);
    }

    /**
     * Check if user can manage invitations for a project.
     */
    protected function canManageInvitations(User $user, Project $project): bool
    {
        if ($user->id === $project->owner_id) {
            return true;
        }

        setPermissionsTeamId($project->id);

        return $user->hasAnyRole(['Project Owner', 'Project Admin']);
    }
}
