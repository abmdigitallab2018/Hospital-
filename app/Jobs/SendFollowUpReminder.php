<?php

namespace App\Jobs;

use App\Models\FollowUp;
use App\Models\Notification as ClinicNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFollowUpReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly FollowUp $followUp,
        public readonly string $channel = 'in_app',
    ) {}

    public function handle(): void
    {
        $followUp = $this->followUp->fresh();

        if (!$followUp || $followUp->status !== 'pending') {
            return; // Already handled or cancelled
        }

        $patient = $followUp->patient;
        $doctor = $followUp->doctor;
        $doctorName = $doctor?->user?->name ?? 'your doctor';
        $clinicName = $followUp->clinic?->name ?? 'the clinic';
        $followUpDate = Carbon::parse($followUp->follow_up_date)->format('d M Y');

        $recipient = $this->channel === 'email'
            ? ($patient?->email ?? $patient?->phone)
            : ($patient?->phone ?? '');

        $message = "Dear {$patient?->full_name}, this is a reminder from {$clinicName}: your follow-up appointment with {$doctorName} is scheduled on {$followUpDate}. Please contact us if you need to reschedule.";

        // Log the notification record
        ClinicNotification::create([
            'clinic_id' => $followUp->clinic_id,
            'patient_id' => $patient?->id,
            'channel' => $this->channel,
            'recipient' => $recipient ?? '',
            'title' => "Follow-up Reminder: {$followUpDate}",
            'message' => $message,
            'status' => 'sent', // Simulate: integrate with SMS/WhatsApp/Email provider here
            'sent_at' => Carbon::now(),
        ]);

        $followUp->update(['reminded_at' => Carbon::now()]);

        Log::info('Follow-up reminder sent', [
            'follow_up_id' => $followUp->id,
            'patient_id' => $patient?->id,
            'channel' => $this->channel,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Follow-up reminder job failed', [
            'follow_up_id' => $this->followUp->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
