@extends('admin.layouts.app')

@section('title', 'Active Loans')

@section('content')
    <section class="page-head">
        <div>
            <h1>Active Loans</h1>
            <p>Approved loans that still have an outstanding balance to collect.</p>
        </div>
    </section>

    <section class="stats stats-compact">
        <article class="stat">
            <p class="stat-label">Active loans</p>
            <p class="stat-value tone-amber">{{ number_format($activeCount) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">Total outstanding</p>
            <p class="stat-value tone-purple">₱{{ number_format((float) $totalOutstanding, 2) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">Credits issued</p>
            <p class="stat-value tone-green">₱{{ number_format((float) $totalCreditsIssued, 2) }}</p>
        </article>
    </section>

    <section class="panel">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Borrower</th>
                        <th>Loan type</th>
                        <th>Term</th>
                        <th class="num">Credits</th>
                        <th class="num">Total payable</th>
                        <th class="num">Outstanding</th>
                        <th>Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td><strong>{{ $loan->application_number }}</strong></td>
                            <td>
                                {{ $loan->user->name }}
                                <span class="muted">{{ $loan->user->borrower_id }}</span>
                            </td>
                            <td>{{ $loan->loanTypeLabel() }}</td>
                            <td>{{ $loan->term_months }} mo.</td>
                            <td class="num">₱{{ number_format((float) $loan->approved_amount, 2) }}</td>
                            <td class="num">₱{{ number_format((float) $loan->total_payable, 2) }}</td>
                            <td class="num">₱{{ number_format((float) $loan->outstanding_balance, 2) }}</td>
                            <td>{{ $loan->reviewed_at?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">No active loans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['items' => $loans])
    </section>
@endsection
