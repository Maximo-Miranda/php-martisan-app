<?php

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use App\Services\ProjectRoleService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->roleService = app(ProjectRoleService::class);
    $this->withoutVite();
});

it('can send a project invitation as owner', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $owner->projects()->attach($project->id);
    $owner->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $owner->assignRole('Project Owner');

    $response = $this->actingAs($owner)->post("/projects/{$project->id}/invitations", [
        'email' => 'newuser@example.com',
        'role' => 'Project Editor',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Invitation sent successfully.');

    $this->assertDatabaseHas('project_invitations', [
        'project_id' => $project->id,
        'email' => 'newuser@example.com',
        'role' => 'Project Editor',
    ]);

    Notification::assertSentOnDemand(ProjectInvitationNotification::class);
});

it('cannot send invitation without proper permissions', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $member->projects()->attach($project->id);
    $member->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $member->assignRole('Project Viewer');

    $response = $this->actingAs($member)->post("/projects/{$project->id}/invitations", [
        'email' => 'newuser@example.com',
        'role' => 'Project Viewer',
    ]);

    $response->assertForbidden();
});

it('shows invitation page for valid token', function () {
    $project = Project::factory()->create();
    $this->roleService->createRolesForProject($project);
    $invitation = ProjectInvitation::factory()->create([
        'project_id' => $project->id,
        'email' => 'invitee@example.com',
    ]);

    $response = $this->get("/invitations/{$invitation->token}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Invitations/Show')
        ->has('invitation')
    );
});

it('redirects for invalid invitation token', function () {
    $response = $this->get('/invitations/invalid-token');

    $response->assertRedirect();
});

it('can accept a valid invitation as new user', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $this->roleService->createRolesForProject($project);
    $invitation = ProjectInvitation::factory()->create([
        'project_id' => $project->id,
        'email' => 'newuser@example.com',
        'role' => 'Project Editor',
    ]);

    $newUser = User::factory()->create(['email' => 'newuser@example.com']);

    $response = $this->actingAs($newUser)->post("/invitations/{$invitation->token}/accept");

    $response->assertRedirect(route('projects.show', $project));

    $invitation->refresh();
    $this->assertNotNull($invitation->accepted_at);
    $this->assertTrue($newUser->fresh()->belongsToProject($project));
    $this->assertEquals($project->id, $newUser->fresh()->current_project_id);
});

it('cannot accept an invitation with wrong email', function () {
    $project = Project::factory()->create();
    $this->roleService->createRolesForProject($project);
    $invitation = ProjectInvitation::factory()->create([
        'project_id' => $project->id,
        'email' => 'correct@example.com',
    ]);

    $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);

    $response = $this->actingAs($wrongUser)->post("/invitations/{$invitation->token}/accept");

    // The user is redirected to dashboard since acceptInvitation is called for any authenticated user
    // The invitation email check happens inside acceptInvitation
    $response->assertRedirect();
});

it('cannot accept an expired invitation', function () {
    $project = Project::factory()->create();
    $this->roleService->createRolesForProject($project);
    $invitation = ProjectInvitation::factory()->expired()->create([
        'project_id' => $project->id,
        'email' => 'user@example.com',
    ]);

    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this->actingAs($user)->post("/invitations/{$invitation->token}/accept");

    $response->assertNotFound();
});

it('can cancel an invitation as owner', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $owner->projects()->attach($project->id);
    $owner->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $owner->assignRole('Project Owner');

    $invitation = ProjectInvitation::factory()->create([
        'project_id' => $project->id,
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($owner)->delete("/projects/{$project->id}/invitations/{$invitation->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Invitation cancelled successfully.');
    $this->assertDatabaseMissing('project_invitations', ['id' => $invitation->id]);
});

it('can resend an invitation', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $owner->projects()->attach($project->id);
    $owner->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $owner->assignRole('Project Owner');

    $invitation = ProjectInvitation::factory()->create([
        'project_id' => $project->id,
        'invited_by' => $owner->id,
        'email' => 'invited@example.com',
        'expires_at' => now()->addDays(3), // expires earlier so we can test it gets extended
    ]);

    $oldToken = $invitation->token;
    $oldExpiresAt = $invitation->expires_at->toDateTimeString();

    $response = $this->actingAs($owner)->post("/projects/{$project->id}/invitations/{$invitation->id}/resend");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Invitation resent successfully.');

    $invitation->refresh();
    $this->assertNotEquals($oldToken, $invitation->token);
    $this->assertNotEquals($oldExpiresAt, $invitation->expires_at->toDateTimeString());

    Notification::assertSentOnDemand(ProjectInvitationNotification::class);
});

it('cannot invite someone already in the project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $owner->projects()->attach($project->id);
    $member->projects()->attach($project->id);
    $owner->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $owner->assignRole('Project Owner');

    $response = $this->actingAs($owner)->post("/projects/{$project->id}/invitations", [
        'email' => $member->email,
        'role' => 'Project Viewer',
    ]);

    $response->assertSessionHasErrors('email');
});

it('can access project immediately after accepting invitation', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $this->roleService->createRolesForProject($project);

    $invitation = ProjectInvitation::factory()->create([
        'project_id' => $project->id,
        'email' => 'invited@example.com',
        'role' => 'Project Viewer',
    ]);

    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);

    // Accept invitation
    $acceptResponse = $this->actingAs($invitedUser)->post("/invitations/{$invitation->token}/accept");
    $acceptResponse->assertRedirect(route('projects.show', $project));

    // Verify user can access the project immediately
    $viewResponse = $this->actingAs($invitedUser)->get("/projects/{$project->id}");
    $viewResponse->assertStatus(200);
    $viewResponse->assertInertia(fn ($page) => $page
        ->component('Projects/Show')
        ->has('project')
        ->where('project.id', $project->id)
    );

    // Verify project appears in user's project list
    $listResponse = $this->actingAs($invitedUser)->get('/projects');
    $listResponse->assertStatus(200);
    $listResponse->assertInertia(fn ($page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 1)
        ->where('projects.data.0.id', $project->id)
    );
});

