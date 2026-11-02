<?php

namespace Database\Factories;

use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanApplication>
 */
class LoanApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_number' => 'LA-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'loan_type' => 'personal',
            'amount' => 20000,
            'term_months' => 6,
            'purpose' => 'Need funds for personal expenses and school payments.',
            'status' => 'pending',
            'interest_rate' => LoanApplication::ANNUAL_RATE,
        ];
    }

    public function approved(float $amount = 20000): static
    {
        return $this->state(function () use ($amount) {
            $breakdown = LoanApplication::calculate($amount, 6);

            return [
                'status' => 'approved',
                'amount' => $amount,
                'approved_amount' => $amount,
                'interest_rate' => LoanApplication::ANNUAL_RATE,
                'total_interest' => $breakdown['interest'],
                'total_payable' => $breakdown['total'],
                'monthly_amortization' => $breakdown['monthly'],
                'outstanding_balance' => $breakdown['total'],
                'repayment_status' => 'active',
                'reviewed_at' => now(),
            ];
        });
    }
}
