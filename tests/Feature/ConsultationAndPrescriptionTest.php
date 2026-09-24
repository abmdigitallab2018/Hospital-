<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Visit;
use App\Models\Vital;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationAndPrescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'Apex Healthcare',
            'slug' => 'apex-healthcare',
            'email' => 'apex@clinic.com',
            'phone' => '9876543210',
            'status' => 'active',
            'prescription_disclaimer' => 'Valid for 15 days from consultation date.',
            'invoice_prefix' => 'APX',
        ]);

        $this->doctorUser = User::factory()->create([
            'name' => 'Dr. Sarah Connor',
            'email' => 'sarah@apex.com',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->doctorUser->id, ['role' => 'doctor', 'is_primary' => true]);

        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->doctorUser->id,
            'specialization' => 'Internal Medicine',
            'qualification' => 'MBBS, MD',
            'license_number' => 'MD-554433',
            'consultation_fee' => 600.00,
            'is_available' => true,
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_uid' => 'PAT-APX-001',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'phone' => '9876500001',
            'gender' => 'male',
        ]);
    }

    public function test_doctor_can_record_consultation_with_vitals_and_prescriptions(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $response = $this->actingAs($this->doctorUser)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'visit_date' => $today,
                'chief_complaints' => 'Severe headache and elevated fever for 3 days',
                'symptoms' => 'Chills, body fatigue, dry cough',
                'examination_notes' => 'Chest clear, throat mildly congested',
                'diagnosis' => 'Acute viral upper respiratory tract infection',
                'treatment_plan' => 'Hydration, rest, antipyretics',
                'clinical_advice' => 'Drink plenty of warm fluids, return if fever persists > 102F',
                'follow_up_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
                'follow_up_instructions' => 'Review vitals and CBC report',
                'vitals' => [
                    'temperature' => 101.4,
                    'blood_pressure_systolic' => 120,
                    'blood_pressure_diastolic' => 80,
                    'pulse_rate' => 84,
                    'oxygen_saturation' => 98,
                    'weight_kg' => 70,
                    'height_cm' => 175,
                ],
                'prescriptions' => [
                    [
                        'medicine_name' => 'Paracetamol 650mg',
                        'dosage' => '1 tablet',
                        'frequency' => 'Three times daily',
                        'duration' => '3 days',
                        'instructions' => 'After food when temperature > 100F',
                    ],
                    [
                        'medicine_name' => 'Cetirizine 10mg',
                        'dosage' => '1 tablet',
                        'frequency' => 'Once at night',
                        'duration' => '5 days',
                        'instructions' => 'Before bed',
                    ],
                ],
            ]);

        $response->assertSessionHas('success');

        // Verify Visit record
        $this->assertDatabaseHas('visits', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'diagnosis' => 'Acute viral upper respiratory tract infection',
            'status' => 'completed',
        ]);

        $visit = Visit::where('patient_id', $this->patient->id)->first();
        $this->assertNotNull($visit);

        // Verify Vitals & BMI calculation (70 / (1.75 * 1.75) = 22.857 -> 22.9)
        $vital = Vital::where('visit_id', $visit->id)->first();
        $this->assertNotNull($vital);
        $this->assertEquals(22.9, (float) $vital->bmi);
        $this->assertEquals(101.4, (float) $vital->temperature);

        // Verify Prescription and Items
        $prescription = Prescription::where('visit_id', $visit->id)->first();
        $this->assertNotNull($prescription);
        $this->assertEquals(2, $prescription->items()->count());
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Paracetamol 650mg',
        ]);
    }

    public function test_printable_prescription_renders_successfully(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $visit = Visit::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => $today,
            'chief_complaints' => 'General health evaluation',
            'diagnosis' => 'Normal clinical findings',
            'status' => 'completed',
        ]);

        $prescription = Prescription::create([
            'clinic_id' => $this->clinic->id,
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'prescription_number' => 'RX-2026-TEST-001',
            'date' => $today,
            'disclaimer' => 'Valid for 15 days',
            'is_signed' => true,
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->get(route('prescriptions.print', $prescription->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Prescriptions/Print')
            ->has('prescription')
            ->where('prescription.prescription_number', 'RX-2026-TEST-001')
        );
    }
}
