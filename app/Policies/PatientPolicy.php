<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'doctor', 'receptionist', 'accountant']);
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$patient->clinic_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'receptionist']);
    }

    public function update(User $user, Patient $patient): bool
    {
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$patient->clinic_id
            && in_array($user->role, ['clinic_admin', 'receptionist']);
    }

    public function delete(User $user, Patient $patient): bool
    {
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$patient->clinic_id
            && $user->role === 'clinic_admin';
    }
}
