<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;
    protected User $receptionist;
    protected Doctor $doctor;
    protected Patient $patient1;
    protected Patient $patient2;
    protected string $bookingDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'Metro Health Clinic',
            'slug' => 'metro-health',
            'email' => 'metro@clinic.com',
            'status' => 'active',
            'invoice_prefix' => 'MTR',
        ]);

        $this->receptionist = User::factory()->create([
            'name' => 'Front Desk Receptionist',
            'email' => 'reception@metro.com',
            'role' => 'receptionist',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->receptionist->id, ['role' => 'receptionist', 'is_primary' => true]);

        // Doctor User and Profile
        $doctorUser = User::factory()->create([
            'name' => 'Dr. Robert Smith',
            'email' => 'smith@metro.com',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($doctorUser->id, ['role' => 'doctor', 'is_primary' => false]);

        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $doctorUser->id,
            'specialization' => 'Cardiology',
            'qualification' => 'MBBS, MD',
            'license_number' => 'MED-12345',
            'consultation_fee' => 700.00,
            'is_available' => true,
        ]);

        // Patients
        $this->patient1 = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_uid' => 'PAT-MTR-001',
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'phone' => '9888800001',
            'gender' => 'female',
        ]);

        $this->patient2 = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_uid' => 'PAT-MTR-002',
            'first_name' => 'Bob',
            'last_name' => 'White',
            'phone' => '9888800002',
            'gender' => 'male',
        ]);

        // Target Date: next day
        $this->bookingDate = Carbon::now()->addDay()->format('Y-m-d');
        $targetDayOfWeek = Carbon::parse($this->bookingDate)->dayOfWeek;

        // Schedule for doctor on that day
        DoctorSchedule::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'day_of_week' => $targetDayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'slot_duration_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_receptionist_can_book_appointment_with_token_generation(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->post(route('appointments.store'), [
                'patient_id' => $this->patient1->id,
                'doctor_id' => $this->doctor->id,
                'appointment_date' => $this->bookingDate,
                'start_time' => '10:00',
                'type' => 'in_person',
                'reason' => 'Routine heart evaluation',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'token_number' => 1,
            'status' => 'scheduled',
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
        ]);
    }

    public function test_prevent_double_booking_same_doctor_overlapping_time(): void
    {
        // First appointment
        Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'appointment_number' => 'APT-TEST-001',
            'token_number' => 1,
            'appointment_date' => $this->bookingDate,
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
            'type' => 'in_person',
            'status' => 'scheduled',
        ]);

        // Attempting to book second appointment for same doctor at overlapping slot
        $response = $this->actingAs($this->receptionist)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->post(route('appointments.store'), [
                'patient_id' => $this->patient2->id,
                'doctor_id' => $this->doctor->id,
                'appointment_date' => $this->bookingDate,
                'start_time' => '10:00',
                'type' => 'in_person',
                'reason' => 'Checkup',
            ]);

        $response->assertSessionHas('error');

        // Only 1 appointment should exist in database
        $this->assertEquals(1, Appointment::where('doctor_id', $this->doctor->id)->count());
    }

    public function test_check_in_patient_updates_status_and_time(): void
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'appointment_number' => 'APT-TEST-002',
            'token_number' => 1,
            'appointment_date' => $this->bookingDate,
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
            'type' => 'in_person',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->receptionist)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->post(route('appointments.checkIn', $appointment->id));

        $response->assertSessionHas('success');

        $appointment->refresh();
        $this->assertEquals('checked_in', $appointment->status);
        $this->assertNotNull($appointment->checked_in_at);
    }

    public function test_available_slots_api_marks_booked_slots_correctly(): void
    {
        // Book 09:30 slot
        Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'appointment_number' => 'APT-TEST-003',
            'token_number' => 1,
            'appointment_date' => $this->bookingDate,
            'start_time' => '09:30:00',
            'end_time' => '09:45:00',
            'type' => 'in_person',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->receptionist)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->getJson(route('appointments.availableSlots', [
                'doctor_id' => $this->doctor->id,
                'date' => $this->bookingDate,
            ]));

        $response->assertOk();
        $slots = $response->json('slots');

        $this->assertNotEmpty($slots);

        // Find 09:30 slot
        $bookedSlot = collect($slots)->firstWhere('start_time', '09:30');
        $this->assertNotNull($bookedSlot);
        $this->assertTrue($bookedSlot['is_booked']);

        // Find 09:00 slot (free)
        $freeSlot = collect($slots)->firstWhere('start_time', '09:00');
        $this->assertNotNull($freeSlot);
        $this->assertFalse($freeSlot['is_booked']);
    }
}
