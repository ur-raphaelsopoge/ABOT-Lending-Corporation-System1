<?php

namespace App\Http\Controllers\Admin;

use App\Models\LoanApplication;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalMembers' => User::count(),
            'totalCredits' => User::sum('credits'),
            'totalBalanceOwed' => User::sum('balance_owed'),
            'activeLoans' => LoanApplication::where('repayment_status', 'active')->count(),
            'pendingApplications' => LoanApplication::where('status', 'pending')->count(),
            'pendingPayments' => LoanPayment::where('status', 'pending')->count(),
            'recentMembers' => User::latest()->limit(5)->get(),
            'recentPayments' => LoanPayment::with(['user', 'loanApplication'])->latest('id')->limit(5)->get(),
            'recentActiveLoans' => LoanApplication::with('user')
                ->where('repayment_status', 'active')
                ->latest('reviewed_at')
                ->limit(5)
                ->get(),
            'recentApplications' => LoanApplication::with('user')->latest('id')->limit(5)->get(),
        ]);
    }
}
