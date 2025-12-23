<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ProjectInvitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invitations.show', $this->invitation->token);
        $inviterName = $this->invitation->inviter->name;
        $projectName = $this->invitation->project->name;

        return (new MailMessage)
            ->subject("You've been invited to join {$projectName}")
            ->greeting('Hello!')
            ->line("{$inviterName} has invited you to join **{$projectName}** as a **{$this->invitation->role}**.")
            ->action('Accept Invitation', $url)
            ->line("This invitation will expire on {$this->invitation->expires_at->format('F j, Y')}.")
            ->line('If you did not expect this invitation, you can ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'project_id' => $this->invitation->project_id,
            'project_name' => $this->invitation->project->name,
        ];
    }
}
