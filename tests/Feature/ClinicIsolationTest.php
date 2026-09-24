<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinicA;
    protected Clinic $clinicB;
    protected User $adminA;
    protected User $adminB;
    protected User $superAdmin;
    protected Patient $patientA;
    protected Patient $patientB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Clinic A
        $this->clinicA = Clinic::create([
            'name' => 'Clinic Alpha',
            'slug' => 'clinic-alpha',
            'email' => 'alpha@clinic.com',
            'phone' => '9999900001',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'invoice_prefix' => 'ALP',
            'status' => 'active',
        ]);

        // Create Clinic B
        $this->clinicB = Clinic::create([
            'name' => 'Clinic Beta',
            'slug' => 'clinic-beta',
            'email' => 'beta@clinic.com',
            'phone' => '9999900002',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'invoice_prefix' => 'BET',
            'status' => 'active',
        ]);

        // Users for Clinic A
        $this->adminA = User::factory()->create([
            'name' => 'Alpha Admin',
            'email' => 'admin@alpha.com',
            'role' => 'clinic_admin',
            'clinic_id' => $this->clinicA->id,
        ]);
        $this->clinicA->users()->attach($this->adminA->id, ['role' => 'clinic_admin', 'is_primary' => true]);

        // Users for Clinic B
        $this->adminB = User::factory()->create([
            'name' => 'Beta Admin',
            'email' => 'admin@beta.com',
            'role' => 'clinic_admin',
            'clinic_id' => $this->clinicB->id,
        ]);
        $this->clinicB->users()->attach($this->adminB->id, ['role' => 'clinic_admin', 'is_primary' => true]);

        // Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'Platform Super Admin',
            'email' => 'super@cms.com',
            'role' => 'super_admin',
            'clinic_id' => null,
        ]);

        // Patients
        $this->patientA = Patient::create([
            'clinic_id' => $this->clinicA->id,
            'patient_uid' => 'PAT-ALP-001',
            'first_name' => 'John',
            'last_name' => 'Alpha',
            'phone' => '9111111111',
            'gender' => 'male',
        ]);

        $this->patientB = Patient::create([
            'clinic_id' => $this->clinicB->id,
            'patient_uid' => 'PAT-BET-001',
            'first_name' => 'Jane',
            'last_name' => 'Beta',
            'phone' => '9222222222',
            'gender' => 'female',
        ]);
    }

    public function test_clinic_a_cannot_see_clinic_b_patients(): void
    {
        $response = $this->actingAs($this->adminA)
            ->withSession(['active_clinic_id' => $this->clinicA->id])
            ->get(route('patients.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Patients/Index')
            ->has('patients.data', 1)
            ->where('patients.data.0.patient_uid', 'PAT-ALP-001')
        );
    }

    public function test_clinic_a_cannot_view_patient_from_clinic_b(): void
    {
        // Global scope BelongsToClinic prevents resolving Patient B under Clinic A context
        $response = $this->actingAs($this->adminA)
            ->withSession(['active_clinic_id' => $this->clinicA->id])
            ->get(route('patients.show', $this->patientB->id));

        $response->assertNotFound();
    }

    public function test_clinic_a_cannot_edit_or_delete_clinic_b_patient(): void
    {
        $response = $this->actingAs($this->adminA)
            ->withSession(['active_clinic_id' => $this->clinicA->id])
            ->put(route('patients.update', $this->patientB->id), [
                'first_name' => 'Tampered Name',
                'last_name' => 'Hacked',
                'phone' => '9222222222',
                'gender' => 'female',
            ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('patients', [
            'id' => $this->patientB->id,
            'first_name' => 'Jane',
        ]);
    }

    public function test_super_admin_can_switch_tenant_context(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('clinic.switch'), ['clinic_id' => $this->clinicB->id]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($this->clinicB->id, session('active_clinic_id'));
    }
}
