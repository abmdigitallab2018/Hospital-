<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $specializations = ['General Medicine', 'Pediatrics', 'Gynecology', 'Orthopedics', 'Cardiology', 'Dermatology', 'ENT', 'Ophthalmology'];
        $clinicId = Clinic::inRandomOrder()->value('id') ?? 1;

        return [
            'clinic_id' => $clinicId,
            'user_id' => null,
            'specialization' => fake()->randomElement($specializations),
            'qualification' => fake()->randomElement(['MBBS', 'MD', 'MS', 'MBBS, MD', 'MBBS, MS']),
            'license_number' => 'LIC-' . fake()->unique()->numerify('######'),
            'consultation_fee' => fake()->randomElement([500, 700, 1000, 1200, 1500]),
            'bio' => fake()->optional()->paragraph(),
            'is_available' => true,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['is_available' => false]);
    }
}
