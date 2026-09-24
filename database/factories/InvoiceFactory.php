<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $clinicId = Clinic::inRandomOrder()->value('id') ?? 1;
        $clinic = \App\Models\Clinic::find($clinicId);
        $total = fake()->randomFloat(2, 200, 5000);
        $paid = fake()->randomElement([0, $total, round($total / 2, 2)]);
        $balance = max(0, $total - $paid);
        $status = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partially_paid' : 'unpaid');
        $date = fake()->dateTimeBetween('-60 days', 'now')->format('Y-m-d');
        $seq = fake()->unique()->numberBetween(1, 9999);
        $prefix = $clinic?->invoice_prefix ?? 'INV';

        return [
            'clinic_id' => $clinicId,
            'patient_id' => Patient::where('clinic_id', $clinicId)->inRandomOrder()->value('id'),
            'invoice_number' => sprintf('%s-%d-%04d', $prefix, date('Y'), $seq),
            'invoice_date' => $date,
            'due_date' => $date,
            'subtotal' => $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'payment_status' => $status,
        ];
    }

    public function unpaid(): static
    {
        return $this->state(fn ($attrs) => [
            'paid_amount' => 0,
            'balance_amount' => $attrs['total_amount'],
            'payment_status' => 'unpaid',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn ($attrs) => [
            'paid_amount' => $attrs['total_amount'],
            'balance_amount' => 0,
            'payment_status' => 'paid',
        ]);
    }
}
