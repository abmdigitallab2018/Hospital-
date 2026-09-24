<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the CMS Dashboard.
     */
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();

        // If user is Patient, redirect to Patient Portal
        if ($user->role === 'patient') {
            return redirect()->route('patient.dashboard');
        }

        // If user is Super Admin without active clinic, redirect to Super Admin Dashboard
        if ($user->role === 'super_admin' && !session('active_clinic_id')) {
            return redirect()->route('superadmin.dashboard');
        }

        $today = Carbon::today()->format('Y-m-d');
        $startOfMonth = Carbon::today()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::today()->endOfMonth()->format('Y-m-d');

        // Appointments query
        $todayAppointmentsQuery = Appointment::with(['patient', 'doctor.user'])
            ->whereDate('appointment_date', $today);

        // If logged in as Doctor, filter to doctor's own appointments
        if ($user->role === 'doctor' && $user->doctor) {
            $todayAppointmentsQuery->where('doctor_id', $user->doctor->id);
        }

        $todayAppointments = $todayAppointmentsQuery->orderBy('token_number')->get();

        // Queue breakdown
        $waitingCount = $todayAppointments->where('status', 'checked_in')->count();
        $inConsultationCount = $todayAppointments->where('status', 'in_consultation')->count();
        $completedTodayCount = $todayAppointments->where('status', 'completed')->count();
        $scheduledCount = $todayAppointments->where('status', 'scheduled')->count();

        // Patient count
        $totalPatients = Patient::count();
        $newPatientsThisMonth = Patient::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Revenue calculations
        $todayRevenue = (float) Payment::whereDate('payment_date', $today)->where('status', 'completed')->sum('amount');
        $monthRevenue = (float) Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->where('status', 'completed')->sum('amount');
        $pendingReceivables = (float) Invoice::whereIn('payment_status', ['unpaid', 'partially_paid'])->sum('balance_amount');

        // Follow-ups due
        $followUpsDue = FollowUp::with('patient', 'doctor.user')
            ->where('status', 'pending')
            ->whereDate('follow_up_date', '<=', Carbon::today()->addDays(2)->format('Y-m-d'))
            ->orderBy('follow_up_date')
            ->take(5)
            ->get();

        // Doctor Workload Summary for Today
        $doctorWorkload = Doctor::with('user')
            ->where('is_available', true)
            ->get()
            ->map(function ($doc) use ($today) {
                $count = Appointment::withoutGlobalScopes()
                    ->where('doctor_id', $doc->id)
                    ->whereDate('appointment_date', $today)
                    ->count();

                $completed = Appointment::withoutGlobalScopes()
                    ->where('doctor_id', $doc->id)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'completed')
                    ->count();

                return [
                    'id' => $doc->id,
                    'name' => $doc->user?->name ?? 'Doctor',
                    'specialization' => $doc->specialization,
                    'room_number' => $doc->room_number,
                    'total_appointments' => $count,
                    'completed_appointments' => $completed,
                ];
            });

        // Recent Visits
        $recentVisits = Visit::with(['patient', 'doctor.user', 'prescription.items', 'vital'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'today_appointments' => $todayAppointments->count(),
                'waiting_count' => $waitingCount,
                'in_consultation_count' => $inConsultationCount,
                'completed_today' => $completedTodayCount,
                'scheduled_count' => $scheduledCount,
                'total_patients' => $totalPatients,
                'new_patients_month' => $newPatientsThisMonth,
                'today_revenue' => $todayRevenue,
                'month_revenue' => $monthRevenue,
                'pending_receivables' => $pendingReceivables,
            ],
            'todayAppointments' => $todayAppointments,
            'doctorWorkload' => $doctorWorkload,
            'followUpsDue' => $followUpsDue,
            'recentVisits' => $recentVisits,
        ]);
    }
}
