<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLedger;
use App\Models\LoanPayment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanPaymentController
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        if (! in_array($status, LoanPayment::STATUSES, true)) {
            $status = null;
        }

        $payments = LoanPayment::with(['user', 'loanApplication'])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest('id')
            ->simplePaginate(10)
            ->withQueryString();

        return view('admin.loan-payments.index', [
            'payments' => $payments,
            'status' => $status,
            'statuses' => LoanPayment::STATUSES,
            'pendingCount' => LoanPayment::where('status', 'pending')->count(),
            'confirmedTotal' => LoanPayment::where('status', 'confirmed')->sum('amount'),
            'paymentCount' => LoanPayment::count(),
        ]);
    }

    public function update(Request $request, LoanPayment $payment): RedirectResponse
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Payment '.$payment->payment_number.' has already been reviewed.');
        }

        $data = $request->validate([
            'decision' => ['required', Rule::in(['confirmed', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $adminId = Auth::guard('admin')->id();
        $decision = $data['decision'];

        DB::transaction(function () use ($payment, $data, $decision, $adminId): void {
            $payment = LoanPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status !== 'pending') {
                return;
            }

            $loan = $payment->loanApplication()->lockForUpdate()->firstOrFail();
            $user = $payment->user()->lockForUpdate()->firstOrFail();

            $payment->status = $decision;
            $payment->notes = $data['notes'] ?? $payment->notes;
            $payment->confirmed_by = $adminId;
            $payment->confirmed_at = now();
            $payment->save();

            if ($decision === 'confirmed') {
                $applied = min((float) $payment->amount, (float) $loan->outstanding_balance);
                $loan->outstanding_balance = round(max(0, (float) $loan->outstanding_balance - $applied), 2);
                $user->balance_owed = round(max(0, (float) $user->balance_owed - $applied), 2);

                if ((float) $loan->outstanding_balance <= 0) {
                    $loan->outstanding_balance = 0;
                    $loan->repayment_status = 'paid';
                }

                $loan->save();
                $user->save();

                $description = sprintf(
                    '%s confirmed for ₱%s. Remaining balance on %s is ₱%s.',
                    $payment->payment_number,
                    number_format($applied, 2),
                    $loan->application_number,
                    number_format((float) $loan->outstanding_balance, 2)
                );
            } else {
                $description = $payment->payment_number.' was rejected.';
            }

            AuditLedger::record($payment->user_id, 'payment_'.$decision, $description, $adminId);
        });

        return back()->with('status', 'Payment '.$payment->payment_number.' was '.$decision.'.');
    }
}
