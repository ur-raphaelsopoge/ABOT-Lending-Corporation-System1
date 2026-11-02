@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-dashboard">
        <section class="dashboard-hero">
            <div>
                <p class="dashboard-eyebrow">Admin overview</p>
                <h1>Welcome back, {{ auth('admin')->user()->name }}</h1>
                <p>Monitor credits, active loan balances, and payment confirmations.</p>
            </div>
            <a class="dashboard-hero-link" href="{{ route('admin.loans.index') }}">Review loan portfolio <span aria-hidden="true">→</span></a>
        </section>

    <section class="stats dashboard-stats">
        <article class="stat dashboard-stat dashboard-stat--blue">
            <p class="stat-label">Total Members</p>
            <p class="stat-value tone-blue">{{ number_format($totalMembers) }}</p>
        </article>
        <article class="stat dashboard-stat dashboard-stat--green">
            <p class="stat-label">Credits Issued</p>
            <p class="stat-value tone-green">₱{{ number_format((float) $totalCredits, 2) }}</p>
        </article>
        <article class="stat dashboard-stat dashboard-stat--purple">
            <p class="stat-label">Balance Owed</p>
            <p class="stat-value tone-purple">₱{{ number_format((float) $totalBalanceOwed, 2) }}</p>
        </article>
        <article class="stat dashboard-stat dashboard-stat--amber">
            <p class="stat-label">Active Loans</p>
            <p class="stat-value tone-amber">{{ number_format($activeLoans) }}</p>
        </article>
        <article class="stat dashboard-stat dashboard-stat--red">
            <p class="stat-label">Pending Applications</p>
            <p class="stat-value tone-red">{{ number_format($pendingApplications) }}</p>
        </article>
    </section>

    <section class="panel panel-spaced dashboard-panel">
        <div class="panel-head">
            <h3>Latest loan applications</h3>
            <a class="text-link" href="{{ route('admin.loans.index') }}">Open Loan Portfolio</a>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Borrower</th>
                        <th>Loan type</th>
                        <th class="num">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentApplications as $application)
                        <tr>
                            <td>
                                <strong>{{ $application->application_number }}</strong>
                                <span class="muted">{{ $application->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                {{ $application->user->name }}
                                <span class="muted">{{ $application->user->borrower_id }}</span>
                            </td>
                            <td>{{ $application->loanTypeLabel() }}</td>
                            <td class="num">₱{{ number_format((float) $application->amount, 2) }}</td>
                            <td><span class="badge badge-{{ $application->status }}">{{ ucfirst($application->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No loan applications have been submitted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="split">
        <article class="panel dashboard-panel">
            <div class="panel-head">
                <h3>Recent registrations</h3>
                <a class="text-link" href="{{ route('admin.members.index') }}">View all</a>
            </div>
            <ul class="people">
                @forelse ($recentMembers as $member)
                    <li>
                        <span class="avatar">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                        <div class="people-meta">
                            <strong>{{ $member->name }}</strong>
                            <span>{{ $member->borrower_id }}</span>
                        </div>
                        <time class="people-time" datetime="{{ $member->created_at->toIso8601String() }}">{{ $member->created_at->diffForHumans() }}</time>
                    </li>
                @empty
                    <li class="empty">No borrowers have registered yet.</li>
                @endforelse
            </ul>
        </article>

        <article class="panel dashboard-panel">
            <div class="panel-head">
                <h3>Recent payment references</h3>
                <a class="text-link" href="{{ route('admin.loan-payments.index') }}">View all</a>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Borrower</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td>
                                    <strong>{{ $payment->payment_number }}</strong>
                                    <span class="muted">{{ ucfirst($payment->status) }} · {{ $payment->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>{{ $payment->user->name }}</td>
                                <td class="num">₱{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">No payment references yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pendingPayments > 0)
                <p class="muted dashboard-note">{{ number_format($pendingPayments) }} payment{{ $pendingPayments === 1 ? '' : 's' }} waiting for confirmation.</p>
            @endif
        </article>
    </section>

    <section class="panel panel-spaced dashboard-panel">
        <div class="panel-head">
            <h3>Active loans</h3>
            <a class="text-link" href="{{ route('admin.active-loans.index') }}">View all</a>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Loan</th>
                        <th>Borrower</th>
                        <th class="num">Credits</th>
                        <th class="num">Outstanding</th>
                        <th>Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentActiveLoans as $loan)
                        <tr>
                            <td><strong>{{ $loan->application_number }}</strong></td>
                            <td>
                                {{ $loan->user->name }}
                                <span class="muted">{{ $loan->user->borrower_id }}</span>
                            </td>
                            <td class="num">₱{{ number_format((float) $loan->approved_amount, 2) }}</td>
                            <td class="num">₱{{ number_format((float) $loan->outstanding_balance, 2) }}</td>
                            <td>{{ $loan->reviewed_at?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No active loans right now.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    </div>
@endsection
