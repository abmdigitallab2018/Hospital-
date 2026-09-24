<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request): Response
    {
        $doctorId = $request->query('doctor_id');
        $status = $request->query('status');
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $search = $request->query('search');

        $user = $request->user();

        $query = Appointment::with(['patient', 'doctor.user']);

        // Doctor only sees their own appointments if role is doctor
        if ($user->role === 'doctor' && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($doctorId && $doctorId !== 'all') {
            $query->where('doctor_id', $doctorId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('appointment_date', $date);
        }

        if ($search) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('patient_uid', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderBy('appointment_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::with('user')->where('is_available', true)->get();
        $patients = Patient::where('status', 'active')->select('id', 'first_name', 'last_name', 'patient_uid', 'phone')->get();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'patients' => $patients,
            'filters' => [
                'doctor_id' => $doctorId,
                'status' => $status,
                'date' => $date,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'type' => 'required|in:in_person,follow_up,emergency',
            'reason' => 'nullable|string|max:500',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;

        // Auto-calculate end_time if not provided (default 15 minutes)
        if (empty($validated['end_time'])) {
            $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
            $validated['end_time'] = $startTime->copy()->addMinutes(15)->format('H:i');
        }

        // Prevent Double Booking Check:
        // Check if the doctor already has an appointment overlapping this slot on the same date
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($sub) use ($validated) {
                        $sub->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'This doctor already has an appointment booked during this time slot. Please choose another time.');
        }

        // Generate Token Number for the date
        $tokenNumber = Appointment::where('clinic_id', $activeClinicId)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->max('token_number') + 1;

        $datePart = Carbon::parse($validated['appointment_date'])->format('Ymd');
        $appointmentNumber = sprintf('APT-%s-%03d', $datePart, $tokenNumber);

        $validated['clinic_id'] = $activeClinicId;
        $validated['appointment_number'] = $appointmentNumber;
        $validated['token_number'] = $tokenNumber;
        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        AuditLog::log('created', $appointment, null, $appointment->toArray());

        return back()->with('success', "Appointment booked successfully! Token #{$tokenNumber} ({$appointmentNumber})");
    }

    /**
     * Check-in appointment (Reception desk flow).
     */
    public function checkIn(Appointment $appointment): RedirectResponse
    {
        $oldValues = $appointment->toArray();
        $appointment->update([
            'status' => 'checked_in',
            'checked_in_at' => Carbon::now(),
        ]);

        AuditLog::log('checked_in', $appointment, $oldValues, $appointment->toArray());

        return back()->with('success', "Patient checked in! Token #{$appointment->token_number} is now in the waiting queue.");
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,checked_in,in_consultation,completed,cancelled,no_show',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $oldValues = $appointment->toArray();

        if ($validated['status'] === 'completed' && !$appointment->completed_at) {
            $validated['completed_at'] = Carbon::now();
        }

        $appointment->update($validated);

        AuditLog::log('status_updated', $appointment, $oldValues, $appointment->toArray());

        return back()->with('success', "Appointment status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])));
    }

    /**
     * Reschedule appointment.
     */
    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        if (empty($validated['end_time'])) {
            $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
            $validated['end_time'] = $startTime->copy()->addMinutes(15)->format('H:i');
        }

        // Prevent Double Booking Check (excluding current appointment)
        $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('id', '!=', $appointment->id)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'This slot is already booked for this doctor. Please pick another slot.');
        }

        $oldValues = $appointment->toArray();
        $validated['status'] = 'scheduled';
        $appointment->update($validated);

        AuditLog::log('rescheduled', $appointment, $oldValues, $appointment->toArray());

        return back()->with('success', "Appointment rescheduled successfully to {$validated['appointment_date']} at {$validated['start_time']}.");
    }

    /**
     * API: Get available time slots for doctor and date.
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $doctorId = $request->query('doctor_id');
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        if (!$doctorId || !$date) {
            return response()->json(['slots' => []]);
        }

        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek; // 0=Sun, 1=Mon, ..., 6=Sat

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json(['slots' => [], 'message' => 'Doctor has no schedule on this day.']);
        }

        // Existing booked appointments for that doctor on that date
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];
        foreach ($schedules as $sched) {
            $start = Carbon::createFromFormat('H:i:s', $sched->start_time);
            $end = Carbon::createFromFormat('H:i:s', $sched->end_time);
            $duration = $sched->slot_duration_minutes ?: 15;

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $timeString = $start->format('H:i');
                $endTimeString = $start->copy()->addMinutes($duration)->format('H:i');

                $isBooked = in_array($timeString, $bookedSlots, true);

                $slots[] = [
                    'start_time' => $timeString,
                    'end_time' => $endTimeString,
                    'is_booked' => $isBooked,
                    'display' => Carbon::createFromFormat('H:i', $timeString)->format('h:i A'),
                ];

                $start->addMinutes($duration);
            }
        }

        return response()->json(['slots' => $slots]);
    }
}
