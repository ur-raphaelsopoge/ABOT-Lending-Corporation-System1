<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLedger;
use App\Models\LoanApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        if (! in_array($status, LoanApplication::STATUSES, true)) {
            $status = null;
        }

        $applications = LoanApplication::with('user')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest('id')
            ->simplePaginate(10)
            ->withQueryString();

        return view('admin.loans.index', [
            'applications' => $applications,
            'status' => $status,
            'statuses' => LoanApplication::STATUSES,
            'totalCount' => LoanApplication::count(),
            'pendingCount' => LoanApplication::where('status', 'pending')->count(),
            'approvedCount' => LoanApplication::where('status', 'approved')->count(),
            'approvedPayable' => LoanApplication::where('status', 'approved')->sum('total_payable'),
        ]);
    }

    public function update(Request $request, LoanApplication $application): RedirectResponse
    {
        if ($application->status !== 'pending') {
            return back()->with('error', 'Application '.$application->application_number.' has already been reviewed.');
        }

        $request->merge([
            'approved_amount' => preg_replace('/[^\d.]/', '', (string) $request->input('approved_amount')),
        ]);

        $maxAmount = min(LoanApplication::MAX_AMOUNT, (float) $application->amount);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'approved_amount' => [
                'exclude_unless:decision,approved',
                'required',
                'numeric',
                'min:'.LoanApplication::MIN_AMOUNT,
                'max:'.$maxAmount,
            ],
            'admin_remarks' => ['nullable', 'string', 'max:255'],
        ], [
            'approved_amount.required' => 'Enter the amount to approve.',
            'approved_amount.numeric' => 'Enter the approved amount as a number.',
            'approved_amount.min' => 'The approved amount must be at least ₱'.number_format(LoanApplication::MIN_AMOUNT).'.',
            'approved_amount.max' => 'The approved amount cannot be higher than the requested amount of ₱'.number_format($maxAmount, 2).'.',
        ]);

        $adminId = Auth::guard('admin')->id();
        $decision = $data['decision'];

        if ($decision === 'approved' && $application->user->hasActiveLoan()) {
            return back()->with('error', 'This borrower still has an unpaid active loan and cannot receive another approval yet.');
        }

        DB::transaction(function () use ($application, $data, $decision, $adminId): void {
            $application->status = $decision;
            $application->admin_remarks = $data['admin_remarks'] ?? null;
            $application->reviewed_by = $adminId;
            $application->reviewed_at = now();

            if ($decision === 'approved') {
                $application->approved_amount = $data['approved_amount'];
                $application->applyBreakdown();
                $application->outstanding_balance = $application->total_payable;
                $application->repayment_status = 'active';
                $application->save();

                $user = $application->user()->lockForUpdate()->first();
                $user->credits = round((float) $user->credits + (float) $application->approved_amount, 2);
                $user->balance_owed = round((float) $user->balance_owed + (float) $application->total_payable, 2);
                $user->save();

                $description = sprintf(
                    '%s was approved at ₱%s (requested ₱%s). Credits +₱%s. Balance owed ₱%s.',
                    $application->application_number,
                    number_format((float) $application->approved_amount, 2),
                    number_format((float) $application->amount, 2),
                    number_format((float) $application->approved_amount, 2),
                    number_format((float) $application->total_payable, 2)
                );
            } else {
                $application->save();
                $description = $application->application_number.' was rejected.';
            }

            AuditLedger::record($application->user_id, 'loan_'.$decision, $description, $adminId);
        });

        return back()->with('status', 'Application '.$application->application_number.' was '.$decision.'.');
    }
}
