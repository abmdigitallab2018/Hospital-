<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $date = $request->query('date');

        $query = Invoice::with(['patient', 'payments']);

        if ($status && $status !== 'all') {
            $query->where('payment_status', $status);
        }

        if ($date) {
            $query->whereDate('invoice_date', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($p) use ($search) {
                        $p->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patient_uid', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(12)->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'date' => $date,
            ],
        ]);
    }

    /**
     * Show form to create a new invoice.
     */
    public function create(Request $request): Response
    {
        $patientId = $request->query('patient_id');
        $patient = $patientId ? Patient::find($patientId) : null;
        $patients = Patient::where('status', 'active')->select('id', 'first_name', 'last_name', 'patient_uid', 'phone')->get();
        $services = Service::where('is_active', true)->get();

        return Inertia::render('Invoices/Create', [
            'selectedPatient' => $patient,
            'patients' => $patients,
            'services' => $services,
        ]);
    }

    /**
     * Store a newly created invoice and optional initial payment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',

            // Initial payment
            'initial_payment.record' => 'nullable|boolean',
            'initial_payment.amount' => 'nullable|numeric|min:1',
            'initial_payment.payment_method' => 'nullable|in:cash,upi,card,bank_transfer,cheque',
            'initial_payment.transaction_reference' => 'nullable|string|max:100',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $clinic = Clinic::find($activeClinicId);
        $prefix = $clinic ? $clinic->invoice_prefix : 'INV';

        // Calculate Totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += (float)$item['unit_price'] * (int)$item['quantity'];
        }

        $discount = (float)($validated['discount_amount'] ?? 0);
        $tax = (float)($validated['tax_amount'] ?? 0);
        $totalAmount = max(0, $subtotal - $discount + $tax);

        $initialPaymentAmount = 0;
        if (!empty($validated['initial_payment']['record']) && !empty($validated['initial_payment']['amount'])) {
            $initialPaymentAmount = min($totalAmount, (float)$validated['initial_payment']['amount']);
        }

        $balanceAmount = max(0, $totalAmount - $initialPaymentAmount);

        $paymentStatus = 'unpaid';
        if ($initialPaymentAmount >= $totalAmount && $totalAmount > 0) {
            $paymentStatus = 'paid';
        } elseif ($initialPaymentAmount > 0) {
            $paymentStatus = 'partially_paid';
        }

        $count = Invoice::where('clinic_id', $activeClinicId)->count() + 1;
        $invoiceNumber = sprintf('%s-%s-%04d', $prefix, Carbon::parse($validated['invoice_date'])->format('Y'), $count);

        $invoice = Invoice::create([
            'clinic_id' => $activeClinicId,
            'patient_id' => $validated['patient_id'],
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? $validated['invoice_date'],
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $totalAmount,
            'paid_amount' => $initialPaymentAmount,
            'balance_amount' => $balanceAmount,
            'payment_status' => $paymentStatus,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'total' => (float)$item['unit_price'] * (int)$item['quantity'],
            ]);
        }

        // Record Initial Payment if made
        if ($initialPaymentAmount > 0) {
            $payCount = Payment::where('clinic_id', $activeClinicId)->count() + 1;
            $payNumber = sprintf('PAY-%s-%04d', Carbon::parse($validated['invoice_date'])->format('Y'), $payCount);

            Payment::create([
                'clinic_id' => $activeClinicId,
                'invoice_id' => $invoice->id,
                'payment_number' => $payNumber,
                'amount' => $initialPaymentAmount,
                'payment_method' => $validated['initial_payment']['payment_method'] ?? 'cash',
                'transaction_reference' => $validated['initial_payment']['transaction_reference'] ?? null,
                'payment_date' => $validated['invoice_date'],
                'notes' => 'Initial payment at invoice creation.',
                'received_by_user_id' => $request->user()->id,
                'status' => 'completed',
            ]);
        }

        AuditLog::log('created', $invoice, null, $invoice->toArray());

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Invoice {$invoiceNumber} generated successfully.");
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'patient',
            'items.service',
            'payments.receivedBy',
            'clinic',
            'visit',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Printable invoice receipt.
     */
    public function print(Invoice $invoice): Response
    {
        $invoice->load([
            'patient',
            'items.service',
            'payments.receivedBy',
            'clinic',
        ]);

        AuditLog::log('printed', $invoice);

        return Inertia::render('Invoices/Print', [
            'invoice' => $invoice,
        ]);
    }
}
