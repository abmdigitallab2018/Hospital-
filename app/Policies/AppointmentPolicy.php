<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'doctor', 'receptionist']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int)$user->clinic_id !== (int)$appointment->clinic_id) return false;
        if ($user->role === 'doctor' && $user->doctor) {
            return (int)$user->doctor->id === (int)$appointment->doctor_id;
        }
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'receptionist', 'patient']);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$appointment->clinic_id
            && in_array($user->role, ['clinic_admin', 'receptionist']);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$appointment->clinic_id
            && in_array($user->role, ['clinic_admin', 'receptionist']);
    }
}
