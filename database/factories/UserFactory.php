<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'borrower_id' => 'BRW-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'date_of_birth' => fake()->date('Y-m-d', '-21 years'),
            'phone_number' => '09'.fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'id_type' => 'philsys',
            'id_number' => 'PSN-'.fake()->unique()->numerify('####-####'),
            'employment_status' => 'employed',
            'monthly_income' => 25000,
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_relationship' => 'Sibling',
            'emergency_contact_phone' => '09'.fake()->numerify('#########'),
            'loan_type' => 'personal',
            'credits' => 0,
            'balance_owed' => 0,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
