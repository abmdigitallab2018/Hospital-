<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;
    protected User $patientUser;
    protected Patient $patient;
    protected Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'CareWell Polyclinic',
            'slug' => 'carewell',
            'email' => 'care@carewell.com',
            'status' => 'active',
            'invoice_prefix' => 'CWL',
        ]);

        $this->patientUser = User::factory()->create([
            'name' => 'Emma Watson',
            'email' => 'emma@example.com',
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->patientUser->id,
            'patient_uid' => 'PAT-CWL-001',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'email' => 'emma@example.com',
            'phone' => '9988776655',
            'gender' => 'female',
            'blood_group' => 'A+',
        ]);

        $docUser = User::factory()->create([
            'name' => 'Dr. Gregory House',
            'email' => 'house@carewell.com',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $docUser->id,
            'specialization' => 'Diagnostics',
            'qualification' => 'MD',
            'license_number' => 'DOC-9988',
            'consultation_fee' => 1000.00,
            'is_available' => true,
        ]);
    }

    public function test_patient_can_view_portal_dashboard(): void
    {
        $response = $this->actingAs($this->patientUser)
            ->get(route('patient.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PatientPortal/Dashboard')
            ->has('patient')
            ->where('patient.patient_uid', 'PAT-CWL-001')
        );
    }

    public function test_unlinked_patient_renders_no_profile_view(): void
    {
        $unlinkedUser = User::factory()->create([
            'name' => 'Stranger User',
            'email' => 'stranger@example.com',
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $response = $this->actingAs($unlinkedUser)
            ->get(route('patient.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PatientPortal/NoProfile')
        );
    }

    public function test_patient_can_request_appointment(): void
    {
        $targetDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->actingAs($this->patientUser)
            ->post(route('patient.appointments.request'), [
                'doctor_id' => $this->doctor->id,
                'appointment_date' => $targetDate,
                'start_time' => '11:00',
                'reason' => 'Persistent joint pain',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'token_number' => 1,
            'status' => 'scheduled',
            'start_time' => '11:00:00',
        ]);
    }

    public function test_patient_can_view_prescriptions_list(): void
    {
        $rx = Prescription::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'prescription_number' => 'RX-CWL-001',
            'date' => Carbon::today()->format('Y-m-d'),
            'is_signed' => true,
        ]);

        $response = $this->actingAs($this->patientUser)
            ->get(route('patient.prescriptions'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PatientPortal/Prescriptions')
            ->has('prescriptions.data', 1)
            ->where('prescriptions.data.0.prescription_number', 'RX-CWL-001')
        );
    }

    public function test_patient_can_view_invoices_and_pay_online(): void
    {
        $invoice = Invoice::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'invoice_number' => 'CWL-2026-001',
            'invoice_date' => Carbon::today()->format('Y-m-d'),
            'subtotal' => 600.00,
            'total_amount' => 600.00,
            'paid_amount' => 0.00,
            'balance_amount' => 600.00,
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->patientUser)
            ->get(route('patient.invoices'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PatientPortal/Invoices')
            ->has('invoices.data', 1)
        );

        // Pay online
        $payResponse = $this->actingAs($this->patientUser)
            ->post(route('patient.invoices.pay', $invoice->id), [
                'payment_method' => 'upi',
            ]);

        $payResponse->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(600.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_amount);
        $this->assertEquals('paid', $invoice->payment_status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'payment_method' => 'upi',
            'notes' => 'Patient Portal Online Payment',
        ]);
    }

    public function test_patient_cannot_pay_another_patients_invoice(): void
    {
        $otherPatient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_uid' => 'PAT-OTHER-001',
            'first_name' => 'Stranger',
            'last_name' => 'Danger',
            'gender' => 'male',
        ]);

        $otherInvoice = Invoice::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $otherPatient->id,
            'invoice_number' => 'CWL-2026-OTHER',
            'invoice_date' => Carbon::today()->format('Y-m-d'),
            'subtotal' => 500.00,
            'total_amount' => 500.00,
            'paid_amount' => 0.00,
            'balance_amount' => 500.00,
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->patientUser)
            ->post(route('patient.invoices.pay', $otherInvoice->id), [
                'payment_method' => 'upi',
            ]);

        $response->assertForbidden();
    }
}
