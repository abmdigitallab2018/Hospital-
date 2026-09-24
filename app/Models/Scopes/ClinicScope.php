<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ClinicScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Super admins can view all clinics unless a specific clinic is active in session
        if ($user->role === 'super_admin') {
            $sessionClinicId = session('active_clinic_id');
            if ($sessionClinicId) {
                $builder->where($model->getTable() . '.clinic_id', $sessionClinicId);
            }
            return;
        }

        // For clinic users, always restrict to their clinic_id
        if ($user->clinic_id) {
            $builder->where($model->getTable() . '.clinic_id', $user->clinic_id);
        }
    }
}
