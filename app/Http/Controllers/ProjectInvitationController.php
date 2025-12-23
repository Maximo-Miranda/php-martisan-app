<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteToProjectRequest;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectInvitationController extends Controller
{
    public function store(InviteToProjectRequest $request, Project $project): RedirectResponse
    {
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by' => auth()->id(),
            'email' => $request->email,
            'role' => $request->role,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $request->email)
            ->notify(new ProjectInvitationNotification($invitation));

        return back()->with('success', 'Invitation sent successfully.');
    }

    public function show(string $token): Response|RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->with('project:id,name,description')
            ->first();

        if (! $invitation) {
            return redirect()->route('login')
                ->with('error', 'This invitation is invalid or has already been used.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired.');
        }

        // If user is logged in and email matches, accept directly
        if (auth()->check() && auth()->user()->email === $invitation->email) {
            return $this->acceptInvitation($invitation, auth()->user());
        }

        // Check if a user with this email already exists
        $existingUser = User::where('email', $invitation->email)->first();

        return Inertia::render('Invitations/Show', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'project' => $invitation->project,
                'expires_at' => $invitation->expires_at,
            ],
            'hasExistingAccount' => (bool) $existingUser,
        ]);
    }

    public function accept(string $token): RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = auth()->user();

        if (! $user) {
            // Store invitation token in session and redirect to register/login
            session(['pending_invitation' => $token]);

            $existingUser = User::where('email', $invitation->email)->exists();

            if ($existingUser) {
                return redirect()->route('login')
                    ->with('info', 'Please log in to accept the invitation.');
            }

            return redirect()->route('register')
                ->with('info', 'Please create an account to accept the invitation.');
        }

        return $this->acceptInvitation($invitation, $user);
    }

    public function destroy(Project $project, ProjectInvitation $invitation): RedirectResponse
    {
        $this->authorize('delete', [$invitation, $project]);

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled successfully.');
    }

    public function resend(Project $project, ProjectInvitation $invitation): RedirectResponse
    {
        $this->authorize('resend', [$invitation, $project]);

        // Update token and expiration
        $invitation->update([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new ProjectInvitationNotification($invitation));

        return back()->with('success', 'Invitation resent successfully.');
    }

    protected function acceptInvitation(ProjectInvitation $invitation, User $user): RedirectResponse
    {
        // Add user to project
        if (! $user->belongsToProject($invitation->project)) {
            $invitation->project->members()->attach($user->id);
        }

        // Assign role scoped to this project
        setPermissionsTeamId($invitation->project_id);
        $user->assignRole($invitation->role);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        // Switch to the new project
        $user->update(['current_project_id' => $invitation->project_id]);

        // Redirect to project page to show updated member list in real-time
        return redirect()->route('projects.show', $invitation->project)
            ->with('success', "You've joined {$invitation->project->name}!");
    }
}
