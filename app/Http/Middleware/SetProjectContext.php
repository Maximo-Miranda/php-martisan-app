<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetProjectContext
{
    /**
     * Routes that should be excluded from project requirement check.
     *
     * @var array<int, string>
     */
    protected array $excludedRoutes = [
        // Project creation
        'projects.create',
        'projects.store',

        // Invitations
        'invitations.show',
        'invitations.accept',

        // Authentication routes
        'login',
        'login.store',
        'logout',
        'register',
        'register.store',

        // Password reset routes
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'password.confirm',
        'password.confirm.store',
        'password.confirmation',

        // Email verification routes
        'verification.notice',
        'verification.verify',
        'verification.send',

        // Two-factor authentication routes
        'two-factor.login',
        'two-factor.login.store',
        'two-factor.*',

        // Settings routes (allow access even without projects)
        'user-password.*',
    ];

    /**
     * Handle an incoming request.
     *
     * Sets the Spatie Permission team context to the user's current project.
     * This enables project-scoped roles and permissions.
     *
     * If the user doesn't have any projects, they are redirected to create one.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     *
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Check if user has any projects (owned or member)
        $hasProjects = $user->projects()->exists();

        // If user has no projects and isn't on an excluded route, redirect to create project
        if (! $hasProjects && ! $this->isExcludedRoute($request)) {
            return redirect()->route('projects.create')
                ->with('info', 'Please create your first project to get started.');
        }

        // Set the permission team context if user has a current project
        if ($user->current_project_id) {
            setPermissionsTeamId($user->current_project_id);
        }

        return $next($request);
    }

    /**
     * Determine if the current route should be excluded from project requirement.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        return $request->routeIs($this->excludedRoutes);
    }
}
