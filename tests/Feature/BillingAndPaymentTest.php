<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;
    protected User $accountant;
    protected Patient $patient;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'City Health Center',
            'slug' => 'city-health',
            'email' => 'city@clinic.com',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'invoice_prefix' => 'CHC',
            'status' => 'active',
        ]);

        $this->accountant = User::factory()->create([
            'name' => 'Accounting Officer',
            'email' => 'accounts@cityhealth.com',
            'role' => 'accountant',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->clinic->users()->attach($this->accountant->id, ['role' => 'accountant', 'is_primary' => true]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_uid' => 'PAT-CHC-001',
            'first_name' => 'David',
            'last_name' => 'Miller',
            'phone' => '9555512345',
            'gender' => 'male',
        ]);

        $this->service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Comprehensive Blood Panel',
            'price' => 800.00,
            'is_active' => true,
        ]);
    }

    public function test_create_invoice_with_itemized_billing_and_partial_payment(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $response = $this->actingAs($this->accountant)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->post(route('invoices.store'), [
                'patient_id' => $this->patient->id,
                'invoice_date' => $today,
                'due_date' => $today,
                'items' => [
                    [
                        'service_id' => null,
                        'description' => 'Doctor Consultation',
                        'unit_price' => 500.00,
                        'quantity' => 1,
                    ],
                    [
                        'service_id' => $this->service->id,
                        'description' => 'Comprehensive Blood Panel',
                        'unit_price' => 800.00,
                        'quantity' => 1,
                    ],
                ],
                'discount_amount' => 100.00,
                'tax_amount' => 50.00,
                'notes' => 'Health checkup package discount applied',
                'initial_payment' => [
                    'record' => true,
                    'amount' => 450.00,
                    'payment_method' => 'cash',
                ],
            ]);

        $response->assertSessionHas('success');

        // Subtotal = 500 + 800 = 1300
        // Total = 1300 - 100 + 50 = 1250
        // Paid = 450
        // Balance = 800
        $invoice = Invoice::where('patient_id', $this->patient->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(1300.00, (float) $invoice->subtotal);
        $this->assertEquals(1250.00, (float) $invoice->total_amount);
        $this->assertEquals(450.00, (float) $invoice->paid_amount);
        $this->assertEquals(800.00, (float) $invoice->balance_amount);
        $this->assertEquals('partially_paid', $invoice->payment_status);

        // Verify Initial Payment was created
        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(450.00, (float) $payment->amount);
        $this->assertEquals('cash', $payment->payment_method);
    }

    public function test_reconcile_and_settle_invoice_balance(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Create an existing unpaid invoice of ₹1000
        $invoice = Invoice::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'invoice_number' => 'CHC-2026-0001',
            'invoice_date' => $today,
            'subtotal' => 1000.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance_amount' => 1000.00,
            'payment_status' => 'unpaid',
        ]);

        // Record full payment of 1000 via UPI
        $response = $this->actingAs($this->accountant)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->id,
                'amount' => 1000.00,
                'payment_method' => 'upi',
                'transaction_reference' => 'UPI-REF-998877',
                'payment_date' => $today,
                'notes' => 'Settled via Google Pay',
            ]);

        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(1000.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_amount);
        $this->assertEquals('paid', $invoice->payment_status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'payment_method' => 'upi',
            'transaction_reference' => 'UPI-REF-998877',
            'amount' => 1000.00,
        ]);
    }

    public function test_printable_invoice_receipt_renders_successfully(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $invoice = Invoice::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'invoice_number' => 'CHC-2026-0002',
            'invoice_date' => $today,
            'subtotal' => 500.00,
            'total_amount' => 500.00,
            'paid_amount' => 500.00,
            'balance_amount' => 0.00,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->accountant)
            ->withSession(['active_clinic_id' => $this->clinic->id])
            ->get(route('invoices.print', $invoice->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Invoices/Print')
            ->has('invoice')
            ->where('invoice.invoice_number', 'CHC-2026-0002')
        );
    }
}
