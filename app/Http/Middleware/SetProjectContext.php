<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetProjectContext
{
    /**
     * Handle an incoming request.
     *
     * Sets the Spatie Permission team context to the user's current project.
     * This enables project-scoped roles and permissions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->current_project_id) {
            setPermissionsTeamId(auth()->user()->current_project_id);
        }

        return $next($request);
    }
}
