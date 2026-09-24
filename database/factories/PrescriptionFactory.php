<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        $clinicId = Clinic::inRandomOrder()->value('id') ?? 1;
        $seq = fake()->unique()->numberBetween(1, 9999);

        return [
            'clinic_id' => $clinicId,
            'patient_id' => Patient::where('clinic_id', $clinicId)->inRandomOrder()->value('id'),
            'doctor_id' => Doctor::where('clinic_id', $clinicId)->inRandomOrder()->value('id'),
            'prescription_number' => sprintf('RX-%d-%04d', date('Y'), $seq),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
            'is_signed' => true,
        ];
    }
}
