<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        $name = fake()->company() . ' Clinic';
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'phone' => fake()->numerify('##########'),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'India',
            'registration_number' => 'REG-' . fake()->numerify('######'),
            'invoice_prefix' => strtoupper(fake()->lexify('???')),
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'consultation_fee' => fake()->randomElement([300, 500, 750, 1000]),
            'status' => 'active',
        ];
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }
}
