<?php

namespace App\Http\Controllers\Admin;

use App\Models\LoanApplication;
use Illuminate\Contracts\View\View;

class ActiveLoanController
{
    public function index(): View
    {
        $loans = LoanApplication::with('user')
            ->where('repayment_status', 'active')
            ->latest('reviewed_at')
            ->latest('id')
            ->simplePaginate(10);

        return view('admin.active-loans.index', [
            'loans' => $loans,
            'activeCount' => LoanApplication::where('repayment_status', 'active')->count(),
            'totalOutstanding' => LoanApplication::where('repayment_status', 'active')->sum('outstanding_balance'),
            'totalCreditsIssued' => LoanApplication::where('status', 'approved')->sum('approved_amount'),
        ]);
    }
}
