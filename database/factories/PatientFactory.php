<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $clinicId = Clinic::inRandomOrder()->value('id') ?? 1;
        $seq = fake()->unique()->numberBetween(1000, 9999);
        return [
            'clinic_id' => $clinicId,
            'patient_uid' => 'PAT-' . $seq,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->numerify('##########'),
            'email' => fake()->optional()->safeEmail(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-1 year')->format('Y-m-d'),
            'blood_group' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->state(),
            'status' => 'active',
        ];
    }
}
