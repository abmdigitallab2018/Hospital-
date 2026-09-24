<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $clinicId = Clinic::inRandomOrder()->value('id') ?? 1;
        $date = fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d');
        $startHour = fake()->numberBetween(9, 17);
        $startTime = sprintf('%02d:00:00', $startHour);
        $endTime = sprintf('%02d:30:00', $startHour);

        return [
            'clinic_id' => $clinicId,
            'patient_id' => Patient::where('clinic_id', $clinicId)->inRandomOrder()->value('id'),
            'doctor_id' => Doctor::where('clinic_id', $clinicId)->inRandomOrder()->value('id'),
            'appointment_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'token_number' => fake()->numberBetween(1, 50),
            'type' => fake()->randomElement(['in_person', 'follow_up', 'emergency']),
            'reason' => fake()->sentence(),
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled']),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(['status' => 'scheduled', 'appointment_date' => now()->addDay()->format('Y-m-d')]);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed', 'appointment_date' => now()->subDay()->format('Y-m-d')]);
    }
}
