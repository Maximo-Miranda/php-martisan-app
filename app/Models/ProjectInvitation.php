<?php

namespace App\Models;

use Database\Factories\ProjectInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $invited_by
 * @property string $email
 * @property string $role
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User $inviter
 * @property-read \App\Models\Project $project
 * @method static \Database\Factories\ProjectInvitationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereInvitedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectInvitation whereUpdatedAt($value)
 * @mixin \Eloquent
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class ProjectInvitation extends Model
{
    /** @use HasFactory<ProjectInvitationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'invited_by',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isPending(): bool
    {
        return ! $this->isExpired() && ! $this->isAccepted();
    }
}
