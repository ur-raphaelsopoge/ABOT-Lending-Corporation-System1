<?php

namespace App\Http\Controllers;

use App\Models\AuditLedger;
use App\Models\LoanApplication;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserHomeController
{
    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();
        $requestedType = (string) $request->query('type', '');
        $activeLoan = $user->activeLoan();

        return view('user.home', [
            'user' => $user,
            'applications' => $user->loanApplications()->latest('id')->get(),
            'payments' => $user->loanPayments()->with('loanApplication')->latest('id')->get(),
            'activeLoan' => $activeLoan,
            'canApply' => ! $user->hasActiveLoan(),
            'loanTypes' => User::LOAN_TYPES,
            'employmentStatuses' => User::EMPLOYMENT_STATUSES,
            'selectedType' => array_key_exists($requestedType, User::LOAN_TYPES) ? $requestedType : null,
            'terms' => LoanApplication::TERMS,
            'minAmount' => LoanApplication::MIN_AMOUNT,
            'maxAmount' => LoanApplication::MAX_AMOUNT,
            'annualRate' => LoanApplication::ANNUAL_RATE,
            'paymentMethods' => LoanPayment::METHODS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if ($user->hasActiveLoan()) {
            return redirect()
                ->route('home')
                ->with('error', 'You already have an approved loan. Finish paying your remaining balance before applying for another one.');
        }

        $request->merge([
            'amount' => preg_replace('/[^\d.]/', '', (string) $request->input('amount')),
        ]);

        $data = $request->validate([
            'loan_type' => ['required', Rule::in(array_keys(User::LOAN_TYPES))],
            'amount' => [
                'required',
                'numeric',
                'min:'.LoanApplication::MIN_AMOUNT,
                'max:'.LoanApplication::MAX_AMOUNT,
            ],
            'term_months' => ['required', 'integer', Rule::in(LoanApplication::TERMS)],
            'purpose' => ['required', 'string', 'min:10', 'max:255'],
        ], [
            'loan_type.required' => 'Choose a loan type.',
            'loan_type.in' => 'Choose a valid loan type.',
            'amount.required' => 'Enter the amount you want to borrow.',
            'amount.numeric' => 'Enter the amount as a number.',
            'amount.min' => 'The minimum loan amount is ₱'.number_format(LoanApplication::MIN_AMOUNT).'.',
            'amount.max' => 'The maximum loan amount is ₱'.number_format(LoanApplication::MAX_AMOUNT).'.',
            'term_months.required' => 'Choose a payment term.',
            'term_months.in' => 'Choose a valid payment term.',
            'purpose.required' => 'Tell us what the loan is for.',
            'purpose.min' => 'Describe the purpose in at least 10 characters.',
        ]);

        $application = $user->loanApplications()->create($data);
        $application->refresh();

        AuditLedger::record(
            $user->id,
            'loan_application',
            $application->application_number.' submitted for ₱'.number_format((float) $application->amount, 2).'.'
        );

        return redirect()
            ->route('home')
            ->with('status', 'Your application '.$application->application_number.' has been submitted and is now pending review.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $request->merge([
            'monthly_income' => preg_replace('/[^\d.]/', '', (string) $request->input('monthly_income')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'phone_number' => ['required', 'string', 'min:10', 'max:20'],
            'address' => ['required', 'string', 'min:8', 'max:500'],
            'employment_status' => ['required', Rule::in(array_keys(User::EMPLOYMENT_STATUSES))],
            'monthly_income' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'loan_type' => ['required', Rule::in(array_keys(User::LOAN_TYPES))],
            'emergency_contact_name' => ['required', 'string', 'min:2', 'max:150'],
            'emergency_contact_relationship' => ['required', 'string', 'min:2', 'max:60'],
            'emergency_contact_phone' => ['required', 'string', 'min:10', 'max:20'],
        ]);

        $user->update($data);

        AuditLedger::record($user->id, 'profile_updated', 'Borrower updated their account profile.');

        return redirect()
            ->route('home')
            ->withFragment('profile')
            ->with('status', 'Your profile has been updated.');
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $activeLoan = $user->activeLoan();

        if (! $activeLoan) {
            return redirect()
                ->route('home')
                ->with('error', 'You do not have an active loan to pay.');
        }

        $request->merge([
            'amount' => preg_replace('/[^\d.]/', '', (string) $request->input('amount')),
        ]);

        $data = $request->validate([
            'reference_number' => ['required', 'string', 'min:4', 'max:80'],
            'amount' => ['required', 'numeric', 'min:1', 'max:'.(float) $activeLoan->outstanding_balance],
            'payment_method' => ['required', Rule::in(array_keys(LoanPayment::METHODS))],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'reference_number.required' => 'Enter your payment reference number.',
            'amount.required' => 'Enter the payment amount.',
            'amount.max' => 'Payment cannot exceed your outstanding balance of ₱'.number_format((float) $activeLoan->outstanding_balance, 2).'.',
        ]);

        $payment = LoanPayment::create([
            'loan_application_id' => $activeLoan->id,
            'user_id' => $user->id,
            'reference_number' => $data['reference_number'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        AuditLedger::record(
            $user->id,
            'payment_submitted',
            $payment->payment_number.' submitted for ₱'.number_format((float) $payment->amount, 2).' with reference '.$payment->reference_number.'.'
        );

        return redirect()
            ->route('home')
            ->withFragment('payments')
            ->with('status', 'Payment '.$payment->payment_number.' was submitted and is waiting for admin confirmation.');
    }
}
