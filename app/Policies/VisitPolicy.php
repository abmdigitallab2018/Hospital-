<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'doctor', 'receptionist']);
    }

    public function view(User $user, Visit $visit): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int)$user->clinic_id !== (int)$visit->clinic_id) return false;
        if ($user->role === 'doctor' && $user->doctor) {
            return (int)$user->doctor->id === (int)$visit->doctor_id;
        }
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'doctor']);
    }

    public function update(User $user, Visit $visit): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int)$user->clinic_id !== (int)$visit->clinic_id) return false;
        if ($user->role === 'doctor' && $user->doctor) {
            return (int)$user->doctor->id === (int)$visit->doctor_id;
        }
        return $user->role === 'clinic_admin';
    }
}
