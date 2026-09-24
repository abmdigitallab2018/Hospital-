<?php

namespace Tests\Traits;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesClinicData
{
    protected function createClinic(array $attrs = []): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Test Clinic',
            'slug' => 'test-clinic-' . uniqid(),
            'email' => 'test@clinic.com',
            'invoice_prefix' => 'TC',
            'status' => 'active',
            'consultation_fee' => 500,
            'currency_symbol' => '₹',
        ], $attrs));
    }

    protected function createUserWithRole(string $role, Clinic $clinic, array $attrs = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role) . ' User',
            'email' => $role . '@test' . uniqid() . '.com',
            'password' => Hash::make('password'),
            'role' => $role,
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ], $attrs));
    }

    protected function createPatient(Clinic $clinic, array $attrs = []): Patient
    {
        static $counter = 0;
        $counter++;
        return Patient::create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_uid' => 'PAT-TC-' . $counter,
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'phone' => '9900' . str_pad($counter, 6, '0', STR_PAD_LEFT),
            'gender' => 'male',
            'is_active' => true,
        ], $attrs));
    }

    protected function createDoctor(Clinic $clinic, User $user, array $attrs = []): Doctor
    {
        return Doctor::create(array_merge([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'specialization' => 'General Medicine',
            'qualification' => 'MBBS',
            'license_number' => 'LIC-' . uniqid(),
            'consultation_fee' => 500,
            'is_available' => true,
        ], $attrs));
    }
}
