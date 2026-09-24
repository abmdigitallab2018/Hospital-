<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    /**
     * Display live waiting room queue and token management.
     */
    public function index(Request $request): Response
    {
        $today = Carbon::today()->format('Y-m-d');
        $doctorId = $request->query('doctor_id');

        $query = Appointment::with(['patient', 'doctor.user'])
            ->whereDate('appointment_date', $today);

        if ($doctorId && $doctorId !== 'all') {
            $query->where('doctor_id', $doctorId);
        }

        $allAppointments = $query->orderBy('token_number')->get();

        $inConsultation = $allAppointments->where('status', 'in_consultation')->values();
        $waiting = $allAppointments->where('status', 'checked_in')->values();
        $upcoming = $allAppointments->where('status', 'scheduled')->values();
        $completed = $allAppointments->where('status', 'completed')->values();

        $doctors = Doctor::with('user')->where('is_available', true)->get();

        return Inertia::render('Queue/Index', [
            'inConsultation' => $inConsultation,
            'waiting' => $waiting,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'doctors' => $doctors,
            'selectedDoctorId' => $doctorId,
        ]);
    }
}
