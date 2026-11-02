<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LoanApplication;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanManagementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_loan_adds_credits_and_balance(): void
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();
        $application = LoanApplication::factory()->for($user)->create([
            'amount' => 20000,
            'term_months' => 6,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.loans.update', $application), [
                'decision' => 'approved',
                'approved_amount' => '20000',
            ])
            ->assertRedirect();

        $user->refresh();
        $application->refresh();
        $breakdown = LoanApplication::calculate(20000, 6);

        $this->assertSame('approved', $application->status);
        $this->assertSame('active', $application->repayment_status);
        $this->assertEquals(20000.0, (float) $user->credits);
        $this->assertEquals($breakdown['total'], (float) $user->balance_owed);
        $this->assertEquals($breakdown['total'], (float) $application->outstanding_balance);
    }

    public function test_borrower_with_active_loan_cannot_apply_again(): void
    {
        $user = User::factory()->create([
            'credits' => 20000,
            'balance_owed' => 20800,
        ]);

        LoanApplication::factory()->for($user)->approved(20000)->create();

        $this->actingAs($user)
            ->post(route('loan-applications.store'), [
                'loan_type' => 'personal',
                'amount' => '10000',
                'term_months' => 6,
                'purpose' => 'Trying to apply while still owing money.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');

        $this->assertSame(1, $user->loanApplications()->count());
    }

    public function test_borrower_payment_reference_is_confirmed_and_reduces_balance(): void
    {
        $user = User::factory()->create([
            'credits' => 20000,
            'balance_owed' => 20800,
        ]);
        $admin = Admin::factory()->create();
        $loan = LoanApplication::factory()->for($user)->approved(20000)->create([
            'reviewed_by' => $admin->id,
        ]);

        $this->actingAs($user)
            ->post(route('loan-payments.store'), [
                'reference_number' => 'GCASH-998877',
                'amount' => '800',
                'payment_method' => 'gcash',
            ])
            ->assertRedirect(route('home').'#payments');

        $payment = LoanPayment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.loan-payments.update', $payment), [
                'decision' => 'confirmed',
            ])
            ->assertRedirect();

        $user->refresh();
        $loan->refresh();
        $payment->refresh();

        $this->assertSame('confirmed', $payment->status);
        $this->assertEquals(20000.0, (float) $loan->outstanding_balance);
        $this->assertEquals(20000.0, (float) $user->balance_owed);
    }

    public function test_borrower_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'phone_number' => '09171234567',
                'address' => '123 Updated Street, Quezon City',
                'employment_status' => 'employed',
                'monthly_income' => '32000',
                'loan_type' => 'business',
                'emergency_contact_name' => 'Contact Person',
                'emergency_contact_relationship' => 'Spouse',
                'emergency_contact_phone' => '09181234567',
            ])
            ->assertRedirect(route('home').'#profile');

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('business', $user->fresh()->loan_type);
    }
}
