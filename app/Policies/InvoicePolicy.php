<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'receptionist', 'accountant']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->role === 'patient' && $user->patient) {
            return (int)$user->patient->id === (int)$invoice->patient_id;
        }
        return (int)$user->clinic_id === (int)$invoice->clinic_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'clinic_admin', 'receptionist', 'accountant']);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$invoice->clinic_id
            && in_array($user->role, ['clinic_admin', 'accountant']);
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        // Patient can only pay their own invoice
        if ($user->role === 'patient' && $user->patient) {
            return (int)$user->patient->id === (int)$invoice->patient_id;
        }
        if ($user->isSuperAdmin()) return true;
        return (int)$user->clinic_id === (int)$invoice->clinic_id;
    }
}
