<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(): Response
    {
        $doctors = Doctor::with(['user', 'schedules'])->latest()->get();

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
        ]);
    }

    /**
     * Store a newly created doctor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:25',
            'password' => 'required|string|min:6',
            'specialization' => 'required|string|max:100',
            'qualification' => 'required|string|max:150',
            'license_number' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0',
            'consultation_fee' => 'required|numeric|min:0',
            'room_number' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:1000',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;

        // 1. Create User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'clinic_id' => $activeClinicId,
            'is_active' => true,
        ]);

        $clinic = Clinic::find($activeClinicId);
        if ($clinic) {
            $clinic->users()->attach($user->id, ['role' => 'doctor']);
        }

        // 2. Create Doctor Profile
        $doctor = Doctor::create([
            'clinic_id' => $activeClinicId,
            'user_id' => $user->id,
            'specialization' => $validated['specialization'],
            'qualification' => $validated['qualification'],
            'license_number' => $validated['license_number'],
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $validated['consultation_fee'],
            'room_number' => $validated['room_number'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'is_available' => true,
        ]);

        // 3. Default Schedule: Monday - Friday 09:00 - 13:00 and 17:00 - 20:00
        for ($day = 1; $day <= 5; $day++) {
            DoctorSchedule::create([
                'clinic_id' => $activeClinicId,
                'doctor_id' => $doctor->id,
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'slot_duration_minutes' => 15,
                'max_patients' => 16,
                'is_active' => true,
            ]);
        }

        AuditLog::log('doctor_created', $doctor, null, $doctor->toArray());

        return back()->with('success', "Doctor {$validated['name']} registered with default schedule.");
    }

    /**
     * Update doctor profile.
     */
    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:25',
            'specialization' => 'required|string|max:100',
            'qualification' => 'required|string|max:150',
            'license_number' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0',
            'consultation_fee' => 'required|numeric|min:0',
            'room_number' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:1000',
            'is_available' => 'required|boolean',
        ]);

        $doctor->user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $doctor->update([
            'specialization' => $validated['specialization'],
            'qualification' => $validated['qualification'],
            'license_number' => $validated['license_number'],
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $validated['consultation_fee'],
            'room_number' => $validated['room_number'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'is_available' => $validated['is_available'],
        ]);

        return back()->with('success', 'Doctor profile updated successfully.');
    }

    /**
     * Show schedule editor for a doctor.
     */
    public function schedule(Doctor $doctor): Response
    {
        $doctor->load(['user', 'schedules']);

        return Inertia::render('Doctors/Schedule', [
            'doctor' => $doctor,
        ]);
    }

    /**
     * Update schedule for doctor.
     */
    public function updateSchedule(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|between:0,6',
            'schedules.*.start_time' => 'required|string',
            'schedules.*.end_time' => 'required|string',
            'schedules.*.slot_duration_minutes' => 'required|integer|min:5|max:120',
            'schedules.*.max_patients' => 'required|integer|min:1|max:100',
            'schedules.*.is_active' => 'required|boolean',
        ]);

        // Replace schedules
        $doctor->schedules()->delete();

        foreach ($validated['schedules'] as $s) {
            DoctorSchedule::create([
                'clinic_id' => $doctor->clinic_id,
                'doctor_id' => $doctor->id,
                'day_of_week' => $s['day_of_week'],
                'start_time' => $s['start_time'],
                'end_time' => $s['end_time'],
                'slot_duration_minutes' => $s['slot_duration_minutes'],
                'max_patients' => $s['max_patients'],
                'is_active' => $s['is_active'],
            ]);
        }

        return redirect()->route('doctors.index')->with('success', 'Doctor working hours and slots updated successfully.');
    }
}
