<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\SwitchProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectRoleService $roleService
    ) {}

    public function index(): Response
    {
        $user = auth()->user();

        // Super Admin sees all projects in the platform
        if ($user->isSuperAdmin()) {
            $projects = Project::query()
                ->with('owner:id,name')
                ->withCount('members')
                ->selectRaw('projects.*, CASE WHEN projects.owner_id = ? THEN 1 ELSE 0 END as is_owner', [$user->id])
                ->latest('projects.created_at')
                ->paginate(12);

            return Inertia::render('Projects/Index', [
                'projects' => $projects,
                'isSuperAdmin' => true,
            ]);
        }

        // Regular users see only their projects (owned or member)
        $projects = Project::query()
            ->where(function ($query) use ($user) {
                $query->where('projects.owner_id', $user->id)
                    ->orWhereExists(function ($subquery) use ($user) {
                        $subquery->select(\DB::raw(1))
                            ->from('project_user')
                            ->whereColumn('project_user.project_id', 'projects.id')
                            ->where('project_user.user_id', $user->id);
                    });
            })
            ->with('owner:id,name')
            ->withCount('members')
            ->selectRaw('projects.*, CASE WHEN projects.owner_id = ? THEN 1 ELSE 0 END as is_owner', [$user->id])
            ->orderByRaw('CASE WHEN projects.owner_id = ? THEN 0 ELSE 1 END', [$user->id])
            ->latest('projects.created_at')
            ->paginate(12);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'isSuperAdmin' => false,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $user = $request->user();

        $project = Project::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.Str::random(6),
            'description' => $request->description,
            'owner_id' => $user->id,
        ]);

        // Create project-scoped roles
        $this->roleService->createRolesForProject($project);

        // Add user as a member
        $user->projects()->attach($project->id);

        // Set as current project
        $user->update(['current_project_id' => $project->id]);

        // Assign Project Owner role scoped to this project
        setPermissionsTeamId($project->id);
        $user->assignRole('Project Owner');

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'owner:id,name,email',
            'members:id,name,email',
            'invitations' => fn ($query) => $query->whereNull('accepted_at')
                ->where('expires_at', '>', now()),
        ]);

        // Get roles for each member in this project context
        setPermissionsTeamId($project->id);
        $membersWithRoles = $project->members->map(function ($member) use ($project) {
            $role = $member->roles()
                ->where('roles.project_id', $project->id)
                ->first();

            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $role?->name ?? 'Member',
                'is_owner' => $member->id === $project->owner_id,
            ];
        });

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'members' => $membersWithRoles,
            'canInvite' => auth()->user()->can('invite', $project),
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update([
            'name' => $request->name ?? $project->name,
            'description' => $request->description,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $user = auth()->user();

        // If this was the current project, switch to another one
        if ($user->current_project_id === $project->id) {
            $nextProject = $user->projects()
                ->where('project_id', '!=', $project->id)
                ->first();

            $user->update(['current_project_id' => $nextProject?->id]);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function switch(SwitchProjectRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'current_project_id' => $request->project_id,
        ]);

        setPermissionsTeamId($request->project_id);

        return back()->with('success', 'Project switched successfully.');
    }
}
