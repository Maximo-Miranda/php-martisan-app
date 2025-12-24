<?php

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectRoleService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->roleService = app(ProjectRoleService::class);
    $this->withoutVite();
});

it('can create a new project', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/projects', [
        'name' => 'Test Project',
        'description' => 'A test project description',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'name' => 'Test Project',
        'description' => 'A test project description',
        'owner_id' => $user->id,
    ]);

    $project = Project::where('name', 'Test Project')->first();
    $this->assertTrue($user->fresh()->belongsToProject($project));
    $this->assertEquals($project->id, $user->fresh()->current_project_id);
});

it('cannot create two projects with the same name for the same user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->post('/projects', [
        'name' => 'Unique Project',
        'description' => 'First project',
    ])->assertRedirect();

    $response = $this->actingAs($user)->post('/projects', [
        'name' => 'Unique Project',
        'description' => 'Second project',
    ]);

    $response->assertSessionHasErrors('name');
});

it('can create projects with the same name for different users', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user1)->post('/projects', [
        'name' => 'Shared Name',
        'description' => 'User 1 project',
    ])->assertRedirect();

    $this->actingAs($user2)->post('/projects', [
        'name' => 'Shared Name',
        'description' => 'User 2 project',
    ])->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'name' => 'Shared Name',
        'owner_id' => $user1->id,
    ]);
    $this->assertDatabaseHas('projects', [
        'name' => 'Shared Name',
        'owner_id' => $user2->id,
    ]);
});

it('can view a project they belong to', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach($project->id);
    $user->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $user->assignRole('Project Owner');

    $response = $this->actingAs($user)->get("/projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Show')
        ->has('project')
    );
});

it('cannot view a project they do not belong to', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $otherUser->id]);
    $this->roleService->createRolesForProject($project);

    $response = $this->actingAs($user)->get("/projects/{$project->id}");

    $response->assertForbidden();
});

it('can update a project as owner', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach($project->id);
    $user->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $user->assignRole('Project Owner');

    $response = $this->actingAs($user)->put("/projects/{$project->id}", [
        'name' => 'Updated Project Name',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Project Name',
    ]);
});

it('can delete a project as owner', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach($project->id);
    $user->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $user->assignRole('Project Owner');

    $response = $this->actingAs($user)->delete("/projects/{$project->id}");

    $response->assertRedirect('/projects');
    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});

it('cannot delete a project if not owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $member->projects()->attach($project->id);
    $member->update(['current_project_id' => $project->id]);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $member->assignRole('Project Viewer');

    $response = $this->actingAs($member)->delete("/projects/{$project->id}");

    $response->assertForbidden();
});

it('can switch to a project they belong to', function () {
    $user = User::factory()->create();
    $project1 = Project::factory()->create(['owner_id' => $user->id]);
    $project2 = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach([$project1->id, $project2->id]);
    $user->update(['current_project_id' => $project1->id]);

    $response = $this->actingAs($user)->post('/projects/switch', [
        'project_id' => $project2->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Project switched successfully.');
    $this->assertEquals($project2->id, $user->fresh()->current_project_id);
});

it('cannot switch to a project they do not belong to', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $otherUser->id]);

    $response = $this->actingAs($user)->post('/projects/switch', [
        'project_id' => $project->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('project_id');
});

it('shows both owned and member projects in index', function () {
    $user = User::factory()->create();

    // Create owned projects
    $ownedProject = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach($ownedProject->id);

    // Create project where user is member but not owner
    $otherUser = User::factory()->create();
    $memberProject = Project::factory()->create(['owner_id' => $otherUser->id]);
    $user->projects()->attach($memberProject->id);

    $response = $this->actingAs($user)->get('/projects');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 2)
    );
});

it('shows owned projects first in index', function () {
    $user = User::factory()->create();

    // Create project where user is member but not owner
    $otherUser = User::factory()->create();
    $memberProject = Project::factory()->create([
        'owner_id' => $otherUser->id,
        'name' => 'Member Project',
    ]);
    $user->projects()->attach($memberProject->id);

    // Create owned project after member project
    $ownedProject = Project::factory()->create([
        'owner_id' => $user->id,
        'name' => 'Owned Project',
    ]);
    $user->projects()->attach($ownedProject->id);

    $response = $this->actingAs($user)->get('/projects');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Index')
        ->where('projects.data.0.name', 'Owned Project')
        ->where('projects.data.0.is_owner', 1)
        ->where('projects.data.1.name', 'Member Project')
        ->where('projects.data.1.is_owner', 0)
    );
});

it('updates tenant context when switching projects', function () {
    $user = User::factory()->create();
    $project1 = Project::factory()->create(['owner_id' => $user->id]);
    $project2 = Project::factory()->create(['owner_id' => $user->id]);

    $user->projects()->attach([$project1->id, $project2->id]);
    $user->update(['current_project_id' => $project1->id]);

    $this->roleService->createRolesForProject($project1);
    $this->roleService->createRolesForProject($project2);

    setPermissionsTeamId($project1->id);
    $user->assignRole('Project Owner');

    $response = $this->actingAs($user)->post('/projects/switch', [
        'project_id' => $project2->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Project switched successfully.');
    $this->assertEquals($project2->id, $user->fresh()->current_project_id);

    // Verify tenant context was updated
    $this->assertEquals($project2->id, getPermissionsTeamId());
});

it('can view project after being added as member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $this->roleService->createRolesForProject($project);

    // Add member to project (simulating invitation acceptance)
    $member->projects()->attach($project->id);

    setPermissionsTeamId($project->id);
    $member->assignRole('Project Editor');

    // Member should be able to view the project
    $response = $this->actingAs($member)->get("/projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Show')
        ->has('project')
    );
});

it('member projects appear in projects list', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $project = Project::factory()->create(['owner_id' => $owner->id]);

    // Add member to project
    $member->projects()->attach($project->id);

    $response = $this->actingAs($member)->get('/projects');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 1)
        ->where('projects.data.0.id', $project->id)
        ->where('projects.data.0.is_owner', 0)
    );
});

it('super admin can see all projects in the platform', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignGlobalRole('Super Admin');

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create projects for different users
    $project1 = Project::factory()->create(['owner_id' => $user1->id]);
    $project2 = Project::factory()->create(['owner_id' => $user2->id]);
    $project3 = Project::factory()->create(['owner_id' => $user2->id]);

    $user1->projects()->attach($project1->id);
    $user2->projects()->attach([$project2->id, $project3->id]);

    $response = $this->actingAs($superAdmin)->get('/projects');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 3)
        ->where('isSuperAdmin', true)
    );
});

it('super admin can switch to any project without being a member', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignGlobalRole('Super Admin');

    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $otherUser->id]);
    $otherUser->projects()->attach($project->id);

    // Super Admin is NOT a member of this project
    $this->assertFalse($superAdmin->belongsToProject($project));

    $response = $this->actingAs($superAdmin)->post('/projects/switch', [
        'project_id' => $project->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Project switched successfully.');
    $this->assertEquals($project->id, $superAdmin->fresh()->current_project_id);
});

it('super admin can view any project without being a member', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignGlobalRole('Super Admin');

    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $otherUser->id]);
    $otherUser->projects()->attach($project->id);
    $this->roleService->createRolesForProject($project);
    setPermissionsTeamId($project->id);
    $otherUser->assignRole('Project Owner');

    $response = $this->actingAs($superAdmin)->get("/projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Show')
        ->has('project')
        ->has('members')
    );
});

it('regular user cannot see projects they do not belong to in index', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // User's own project
    $ownProject = Project::factory()->create(['owner_id' => $user->id]);
    $user->projects()->attach($ownProject->id);

    // Other user's project (user is NOT a member)
    $otherProject = Project::factory()->create(['owner_id' => $otherUser->id]);
    $otherUser->projects()->attach($otherProject->id);

    $response = $this->actingAs($user)->get('/projects');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Index')
        ->has('projects.data', 1)
        ->where('projects.data.0.id', $ownProject->id)
        ->where('isSuperAdmin', false)
    );
});
