<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): Response
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $method = $request->query('payment_method');
        $date = $request->query('date');
        $search = $request->query('search');

        $query = Payment::with(['invoice.patient', 'receivedBy'])
            ->where('clinic_id', $activeClinicId);

        if ($method && $method !== 'all') {
            $query->where('payment_method', $method);
        }

        if ($date) {
            $query->whereDate('payment_date', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($inv) use ($search) {
                        $inv->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('patient', function ($p) use ($search) {
                                $p->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => [
                'payment_method' => $method,
                'date' => $date,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Record a new payment for an invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,upi,card,bank_transfer,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if ($invoice->balance_amount <= 0) {
            return back()->with('error', 'This invoice has already been fully paid.');
        }

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $payCount = Payment::where('clinic_id', $activeClinicId)->count() + 1;
        $payNumber = sprintf('PAY-%s-%04d', Carbon::parse($validated['payment_date'])->format('Y'), $payCount);

        $paymentAmount = min((float)$validated['amount'], (float)$invoice->balance_amount);

        $payment = Payment::create([
            'clinic_id' => $activeClinicId,
            'invoice_id' => $invoice->id,
            'payment_number' => $payNumber,
            'amount' => $paymentAmount,
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'payment_date' => $validated['payment_date'],
            'notes' => $validated['notes'] ?? null,
            'received_by_user_id' => $request->user()->id,
            'status' => 'completed',
        ]);

        // Update Invoice Paid and Balance Amount
        $newPaidAmount = (float)$invoice->paid_amount + $paymentAmount;
        $newBalanceAmount = max(0, (float)$invoice->total_amount - $newPaidAmount);
        $newStatus = $newBalanceAmount <= 0 ? 'paid' : 'partially_paid';

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'balance_amount' => $newBalanceAmount,
            'payment_status' => $newStatus,
        ]);

        AuditLog::log('payment_received', $payment, null, $payment->toArray());

        return back()->with('success', "Payment of {$paymentAmount} recorded successfully ({$payNumber}).");
    }
}
