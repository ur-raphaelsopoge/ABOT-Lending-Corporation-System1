<?php

namespace Database\Factories;

use App\Models\LoanApplication;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanPayment>
 */
class LoanPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_number' => 'PAY-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'loan_application_id' => LoanApplication::factory()->approved(),
            'user_id' => User::factory(),
            'reference_number' => 'REF-'.fake()->numerify('######'),
            'amount' => 1000,
            'payment_method' => 'gcash',
            'status' => 'pending',
        ];
    }
}
