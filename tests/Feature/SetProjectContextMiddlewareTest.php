<?php

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectRoleService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects user without projects to create project page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('projects.create'))
        ->assertSessionHas('info', 'Please create your first project to get started.');
});

it('allows user with projects to access protected routes', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    // Create project roles
    app(ProjectRoleService::class)->createRolesForProject($project);

    $user->projects()->attach($project->id);
    $user->update(['current_project_id' => $project->id]);
    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('allows access to project creation routes for users without projects', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('projects.create'))
        ->assertSuccessful();
});

it('allows unauthenticated users to access public routes', function () {
    get(route('home'))
        ->assertSuccessful();
});

it('sets permission team id when user has current project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach($project->id);
    $user->update(['current_project_id' => $project->id]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();

    expect(getPermissionsTeamId())->toBe($project->id);
});

it('does not redirect user who is member but not owner', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $member = User::factory()->create();
    $member->projects()->attach($project->id);
    $member->update(['current_project_id' => $project->id]);

    actingAs($member)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('redirects to create project when user has no projects even if invited', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    // User received invitation but hasn't accepted yet
    $project->invitations()->create([
        'email' => $user->email,
        'invited_by' => $project->owner_id,
        'role' => 'Member',
        'token' => 'test-token-456',
        'expires_at' => now()->addDays(7),
    ]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('projects.create'));
});

it('allows users without projects to access login page', function () {
    get(route('login'))
        ->assertSuccessful();
});

it('allows users without projects to logout', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));
});

it('allows unverified users without projects to access verification notice', function () {
    $user = User::factory()->unverified()->create();

    actingAs($user)
        ->get(route('verification.notice'))
        ->assertSuccessful();
});

it('allows users to access password reset page without projects', function () {
    get(route('password.request'))
        ->assertSuccessful();
});
