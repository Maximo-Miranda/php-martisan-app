<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ProjectScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that belong to a project (tenant).
 *
 * Automatically applies ProjectScope to filter records by the user's current project.
 * Also auto-sets project_id when creating new records.
 *
 * @mixin Model
 *
 * @property int $project_id
 *
 * @method static Builder<static> withoutProjectScope()
 */
trait BelongsToProject
{
    public static function bootBelongsToProject(): void
    {
        static::addGlobalScope(new ProjectScope);

        static::creating(function (Model $model) {
            if (! $model->project_id && auth()->check()) {
                $model->project_id = auth()->user()->current_project_id;
            }
        });
    }

    /**
     * @return BelongsTo<\App\Models\Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    /**
     * Scope to query without the project scope.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutProjectScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ProjectScope::class);
    }
}
