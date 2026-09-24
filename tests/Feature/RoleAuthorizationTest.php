<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;
    protected User $clinicAdmin;
    protected User $doctor;
    protected User $receptionist;
    protected User $accountant;
    protected User $patient;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'St. Jude Health',
            'slug' => 'st-jude-health',
            'email' => 'contact@stjude.com',
            'status' => 'active',
            'invoice_prefix' => 'SJH',
        ]);

        $this->clinicAdmin = User::factory()->create([
            'name' => 'Admin Jane',
            'email' => 'admin@stjude.com',
            'role' => 'clinic_admin',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->clinicAdmin->id, ['role' => 'clinic_admin', 'is_primary' => true]);

        $this->doctor = User::factory()->create([
            'name' => 'Dr. Paul',
            'email' => 'dr.paul@stjude.com',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->doctor->id, ['role' => 'doctor', 'is_primary' => false]);

        $this->receptionist = User::factory()->create([
            'name' => 'Desk Clerk',
            'email' => 'clerk@stjude.com',
            'role' => 'receptionist',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->receptionist->id, ['role' => 'receptionist', 'is_primary' => false]);

        $this->accountant = User::factory()->create([
            'name' => 'Finance Clerk',
            'email' => 'finance@stjude.com',
            'role' => 'accountant',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->accountant->id, ['role' => 'accountant', 'is_primary' => false]);

        $this->patient = User::factory()->create([
            'name' => 'Patient Peter',
            'email' => 'peter@patient.com',
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'SaaS Master',
            'email' => 'master@cms.com',
            'role' => 'super_admin',
            'clinic_id' => null,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('appointments.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_doctor_cannot_access_staff_management(): void
    {
        $response = $this->actingAs($this->doctor)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->get(route('staff.index'));

        $response->assertForbidden();
    }

    public function test_receptionist_cannot_access_financial_reports(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->get(route('reports.index'));

        $response->assertForbidden();
    }

    public function test_accountant_can_access_financial_reports(): void
    {
        $response = $this->actingAs($this->accountant)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->get(route('reports.index'));

        $response->assertOk();
    }

    public function test_clinic_admin_cannot_access_super_admin_hub(): void
    {
        $response = $this->actingAs($this->clinicAdmin)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->get(route('superadmin.dashboard'));

        $response->assertForbidden();
    }

    public function test_patient_is_redirected_from_main_dashboard_to_patient_portal(): void
    {
        $response = $this->actingAs($this->patient)
            ->get(route('dashboard'));

        $response->assertRedirect(route('patient.dashboard'));
    }

    public function test_super_admin_can_access_super_admin_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'));

        $response->assertOk();
    }
}
