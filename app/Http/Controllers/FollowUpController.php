<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowUpController extends Controller
{
    /**
     * Display a listing of follow-ups.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $doctorId = $request->query('doctor_id');
        $date = $request->query('date');
        $search = $request->query('search');

        $user = $request->user();

        $query = FollowUp::with(['patient', 'doctor.user', 'visit']);

        if ($user->role === 'doctor' && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($doctorId && $doctorId !== 'all') {
            $query->where('doctor_id', $doctorId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('follow_up_date', $date);
        }

        if ($search) {
            $query->whereHas('patient', function ($p) use ($search) {
                $p->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('patient_uid', 'like', "%{$search}%");
            });
        }

        $followUps = $query->orderBy('follow_up_date')->paginate(15)->withQueryString();
        $doctors = Doctor::with('user')->where('is_available', true)->get();

        return Inertia::render('FollowUps/Index', [
            'followUps' => $followUps,
            'doctors' => $doctors,
            'filters' => [
                'status' => $status,
                'doctor_id' => $doctorId,
                'date' => $date,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Update follow-up status.
     */
    public function updateStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,missed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldValues = $followUp->toArray();
        $followUp->update($validated);

        AuditLog::log('follow_up_updated', $followUp, $oldValues, $followUp->toArray());

        return back()->with('success', 'Follow-up status updated to ' . ucfirst($validated['status']));
    }

    /**
     * Send notification reminder (WhatsApp / SMS / Email simulation).
     */
    public function sendReminder(Request $request, FollowUp $followUp): RedirectResponse
    {
        $channel = $request->input('channel', 'whatsapp');
        $patient = $followUp->patient;
        $doctor = $followUp->doctor;

        $recipient = $channel === 'email' ? ($patient->email ?? $patient->phone) : $patient->phone;
        $doctorName = $doctor->user?->name ?? 'Doctor';

        $message = "Dear {$patient->full_name}, this is a gentle reminder from {$followUp->clinic?->name} regarding your upcoming clinical follow-up with {$doctorName} on {$followUp->follow_up_date->format('d M Y')}. Please contact reception if you wish to reschedule.";

        Notification::create([
            'clinic_id' => $followUp->clinic_id,
            'user_id' => null,
            'patient_id' => $patient->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'title' => "Follow-up Reminder with {$doctorName}",
            'message' => $message,
            'status' => 'sent',
            'sent_at' => Carbon::now(),
        ]);

        $followUp->update(['reminded_at' => Carbon::now()]);

        return back()->with('success', "Follow-up reminder sent to {$patient->full_name} via " . strtoupper($channel) . "!");
    }
}
