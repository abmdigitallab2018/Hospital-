<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display clinic financial and operational reports.
     */
    public function index(Request $request): Response
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $range = $request->query('range', 'this_month');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        [$start, $end] = $this->resolveDateRange($range, $startDate, $endDate);

        // 1. Financial Totals
        $totalInvoiced = (float) Invoice::where('clinic_id', $activeClinicId)->whereBetween('invoice_date', [$start, $end])->sum('total_amount');
        $totalCollected = (float) Payment::where('clinic_id', $activeClinicId)->whereBetween('payment_date', [$start, $end])->where('status', 'completed')->sum('amount');
        $totalOutstanding = (float) Invoice::where('clinic_id', $activeClinicId)->whereBetween('invoice_date', [$start, $end])->whereIn('payment_status', ['unpaid', 'partially_paid'])->sum('balance_amount');
        $totalExpenses = (float) Expense::where('clinic_id', $activeClinicId)->whereBetween('expense_date', [$start, $end])->sum('amount');
        $netEarnings = $totalCollected - $totalExpenses;

        // 2. Collections by Payment Method
        $paymentMethods = Payment::where('clinic_id', $activeClinicId)
            ->whereBetween('payment_date', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw('payment_method, sum(amount) as total, count(*) as count')
            ->groupBy('payment_method')
            ->get();

        // 3. Appointment Analytics
        $totalAppointments = Appointment::where('clinic_id', $activeClinicId)->whereBetween('appointment_date', [$start, $end])->count();
        $completedAppointments = Appointment::where('clinic_id', $activeClinicId)->whereBetween('appointment_date', [$start, $end])->where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('clinic_id', $activeClinicId)->whereBetween('appointment_date', [$start, $end])->where('status', 'cancelled')->count();
        $noShowAppointments = Appointment::where('clinic_id', $activeClinicId)->whereBetween('appointment_date', [$start, $end])->where('status', 'no_show')->count();

        // 4. Doctor-Wise Performance
        $doctorStats = Doctor::withoutGlobalScopes()->where('clinic_id', $activeClinicId)->with('user')->get()->map(function ($doc) use ($start, $end) {
            $visits = Visit::withoutGlobalScopes()
                ->where('clinic_id', $doc->clinic_id)
                ->where('doctor_id', $doc->id)
                ->whereBetween('visit_date', [$start, $end])
                ->count();

            $completedApts = Appointment::withoutGlobalScopes()
                ->where('clinic_id', $doc->clinic_id)
                ->where('doctor_id', $doc->id)
                ->whereBetween('appointment_date', [$start, $end])
                ->where('status', 'completed')
                ->count();

            return [
                'doctor_id' => $doc->id,
                'name' => $doc->user?->name ?? 'Doctor',
                'specialization' => $doc->specialization,
                'visits_count' => $visits,
                'completed_appointments' => $completedApts,
                'consultation_fee' => $doc->consultation_fee,
                'estimated_revenue' => $visits * (float)$doc->consultation_fee,
            ];
        });

        // 5. Recent Transaction Logs
        $recentPayments = Payment::with(['invoice.patient', 'receivedBy'])
            ->where('clinic_id', $activeClinicId)
            ->whereBetween('payment_date', [$start, $end])
            ->latest('payment_date')
            ->take(10)
            ->get();

        return Inertia::render('Reports/Index', [
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'total_collected' => $totalCollected,
                'total_outstanding' => $totalOutstanding,
                'total_expenses' => $totalExpenses,
                'net_earnings' => $netEarnings,
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'cancelled_appointments' => $cancelledAppointments,
                'no_show_appointments' => $noShowAppointments,
            ],
            'paymentMethods' => $paymentMethods,
            'doctorStats' => $doctorStats,
            'recentPayments' => $recentPayments,
            'filters' => [
                'range' => $range,
                'start_date' => $start,
                'end_date' => $end,
            ],
        ]);
    }

    /**
     * Export report data to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $range = $request->query('range', 'this_month');
        [$start, $end] = $this->resolveDateRange($range, $request->query('start_date'), $request->query('end_date'));

        $fileName = "clinic_revenue_report_{$start}_to_{$end}.csv";

        return response()->streamDownload(function () use ($start, $end, $activeClinicId) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment Number', 'Invoice Number', 'Patient Name', 'Amount (INR)', 'Payment Method', 'Transaction Ref', 'Payment Date', 'Received By']);

            Payment::with(['invoice.patient', 'receivedBy'])
                ->where('clinic_id', $activeClinicId)
                ->whereBetween('payment_date', [$start, $end])
                ->chunk(100, function ($payments) use ($handle) {
                    foreach ($payments as $p) {
                        fputcsv($handle, [
                            $p->payment_number,
                            $p->invoice?->invoice_number,
                            $p->invoice?->patient?->full_name,
                            $p->amount,
                            strtoupper($p->payment_method),
                            $p->transaction_reference,
                            $p->payment_date->format('Y-m-d'),
                            $p->receivedBy?->name ?? 'System',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    private function resolveDateRange(string $range, ?string $start, ?string $end): array
    {
        $today = Carbon::today();

        return match ($range) {
            'today' => [$today->format('Y-m-d'), $today->format('Y-m-d')],
            'this_week' => [$today->copy()->startOfWeek()->format('Y-m-d'), $today->copy()->endOfWeek()->format('Y-m-d')],
            'this_month' => [$today->copy()->startOfMonth()->format('Y-m-d'), $today->copy()->endOfMonth()->format('Y-m-d')],
            'last_30_days' => [$today->copy()->subDays(30)->format('Y-m-d'), $today->format('Y-m-d')],
            'custom' => [$start ?? $today->copy()->startOfMonth()->format('Y-m-d'), $end ?? $today->format('Y-m-d')],
            default => [$today->copy()->startOfMonth()->format('Y-m-d'), $today->copy()->endOfMonth()->format('Y-m-d')],
        };
    }
}
