<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientPortalController extends Controller
{
    /**
     * Get or resolve current logged in patient record.
     */
    private function resolvePatient(Request $request): ?Patient
    {
        $user = $request->user();

        // 1. Direct user_id association
        $patient = Patient::where('user_id', $user->id)->first();
        if ($patient) {
            return $patient;
        }

        // 2. Fallback to matching by email or phone
        $patient = Patient::where('email', $user->email)
            ->orWhere('phone', $user->phone)
            ->first();

        if ($patient && !$patient->user_id) {
            $patient->update(['user_id' => $user->id]);
        }

        return $patient;
    }

    /**
     * Patient Dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $patient = $this->resolvePatient($request);

        if (!$patient) {
            return Inertia::render('PatientPortal/NoProfile');
        }

        $upcomingAppointments = Appointment::with('doctor.user')
            ->where('patient_id', $patient->id)
            ->whereDate('appointment_date', '>=', Carbon::today()->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('appointment_date')
            ->get();

        $recentPrescriptions = Prescription::with(['doctor.user', 'items'])
            ->where('patient_id', $patient->id)
            ->latest('date')
            ->take(5)
            ->get();

        $recentInvoices = Invoice::where('patient_id', $patient->id)
            ->latest('invoice_date')
            ->take(5)
            ->get();

        $totalDue = (float) Invoice::where('patient_id', $patient->id)
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->sum('balance_amount');

        return Inertia::render('PatientPortal/Dashboard', [
            'patient' => $patient,
            'upcomingAppointments' => $upcomingAppointments,
            'recentPrescriptions' => $recentPrescriptions,
            'recentInvoices' => $recentInvoices,
            'totalDue' => $totalDue,
        ]);
    }

    /**
     * Patient Appointments List & Request Form.
     */
    public function appointments(Request $request): Response
    {
        $patient = $this->resolvePatient($request);
        $clinicId = $patient ? $patient->clinic_id : session('active_clinic_id');

        $appointments = Appointment::with('doctor.user')
            ->where('patient_id', $patient?->id)
            ->orderByDesc('appointment_date')
            ->paginate(10);

        $doctors = Doctor::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where('is_available', true)
            ->with('user')
            ->get();

        return Inertia::render('PatientPortal/Appointments', [
            'patient' => $patient,
            'appointments' => $appointments,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Patient requests a new appointment.
     */
    public function requestAppointment(Request $request): RedirectResponse
    {
        $patient = $this->resolvePatient($request);

        if (!$patient) {
            return back()->with('error', 'Patient profile not found.');
        }

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $activeClinicId = $patient->clinic_id;

        $tokenNumber = Appointment::withoutGlobalScopes()
            ->where('clinic_id', $activeClinicId)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->max('token_number') + 1;

        $datePart = Carbon::parse($validated['appointment_date'])->format('Ymd');
        $appointmentNumber = sprintf('APT-%s-%03d', $datePart, $tokenNumber);

        $startTime = Carbon::createFromFormat('H:i', substr($validated['start_time'], 0, 5));
        $endTime = $startTime->copy()->addMinutes(15)->format('H:i');

        $appointment = Appointment::create([
            'clinic_id' => $activeClinicId,
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'],
            'appointment_number' => $appointmentNumber,
            'token_number' => $tokenNumber,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime,
            'type' => 'in_person',
            'status' => 'scheduled',
            'reason' => $validated['reason'] ?? 'Online Patient Portal Booking',
        ]);

        AuditLog::log('patient_booked_appointment', $appointment);

        return back()->with('success', "Appointment booked successfully! Your Token is #{$tokenNumber}.");
    }

    /**
     * Patient Prescriptions.
     */
    public function prescriptions(Request $request): Response
    {
        $patient = $this->resolvePatient($request);

        $prescriptions = Prescription::with(['doctor.user', 'items', 'visit'])
            ->where('patient_id', $patient?->id)
            ->latest('date')
            ->paginate(10);

        return Inertia::render('PatientPortal/Prescriptions', [
            'patient' => $patient,
            'prescriptions' => $prescriptions,
        ]);
    }

    /**
     * Patient Invoices & Bills.
     */
    public function invoices(Request $request): Response
    {
        $patient = $this->resolvePatient($request);

        $invoices = Invoice::with(['items', 'payments'])
            ->where('patient_id', $patient?->id)
            ->latest('invoice_date')
            ->paginate(10);

        return Inertia::render('PatientPortal/Invoices', [
            'patient' => $patient,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Simulate paying an invoice online via UPI/Card.
     */
    public function payInvoice(Request $request, Invoice $invoice): RedirectResponse
    {
        $patient = $this->resolvePatient($request);

        if ($invoice->patient_id !== $patient?->id) {
            abort(403, 'Unauthorized');
        }

        if ($invoice->balance_amount <= 0) {
            return back()->with('info', 'This invoice is already fully settled.');
        }

        $amountToPay = (float) $invoice->balance_amount;
        $payCount = Payment::withoutGlobalScopes()->where('clinic_id', $invoice->clinic_id)->count() + 1;
        $payNumber = sprintf('PAY-ONLINE-%s-%04d', Carbon::today()->format('Y'), $payCount);

        Payment::create([
            'clinic_id' => $invoice->clinic_id,
            'invoice_id' => $invoice->id,
            'payment_number' => $payNumber,
            'amount' => $amountToPay,
            'payment_method' => $request->input('payment_method', 'upi'),
            'transaction_reference' => 'ONLINE-' . strtoupper(bin2hex(random_bytes(4))),
            'payment_date' => Carbon::today()->format('Y-m-d'),
            'notes' => 'Patient Portal Online Payment',
            'received_by_user_id' => null,
            'status' => 'completed',
        ]);

        $invoice->update([
            'paid_amount' => (float)$invoice->paid_amount + $amountToPay,
            'balance_amount' => 0.00,
            'payment_status' => 'paid',
        ]);

        return back()->with('success', "Payment of ₹" . number_format($amountToPay, 2) . " received successfully!");
    }
}
