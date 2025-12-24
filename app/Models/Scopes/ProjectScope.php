<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProjectScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Super Admin bypasses tenant filtering
        if ($user->isSuperAdmin()) {
            return;
        }

        $builder->where($model->getTable().'.project_id', $user->current_project_id);
    }
}
