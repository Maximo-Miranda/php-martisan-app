<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        // Get all accessible projects for the dropdown
        $allProjects = [];
        if ($user) {
            if ($user->isSuperAdmin()) {
                // Super Admin sees all projects
                $allProjects = \App\Models\Project::query()
                    ->select('projects.id', 'projects.name', 'projects.slug', 'projects.owner_id')
                    ->orderByRaw('CASE WHEN projects.owner_id = ? THEN 0 ELSE 1 END', [$user->id])
                    ->latest('projects.created_at')
                    ->get();
            } else {
                // Regular users see owned projects + projects they're members of
                $allProjects = \App\Models\Project::query()
                    ->where(function ($query) use ($user) {
                        $query->where('projects.owner_id', $user->id)
                            ->orWhereExists(function ($subquery) use ($user) {
                                $subquery->select(\DB::raw(1))
                                    ->from('project_user')
                                    ->whereColumn('project_user.project_id', 'projects.id')
                                    ->where('project_user.user_id', $user->id);
                            });
                    })
                    ->select('projects.id', 'projects.name', 'projects.slug', 'projects.owner_id')
                    ->orderByRaw('CASE WHEN projects.owner_id = ? THEN 0 ELSE 1 END', [$user->id])
                    ->latest('projects.created_at')
                    ->get();
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'projects' => $allProjects,
            'currentProject' => $user?->currentProject,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
