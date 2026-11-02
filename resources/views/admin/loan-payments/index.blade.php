@extends('admin.layouts.app')

@section('title', 'Loan Payments')

@section('content')
    <section class="page-head">
        <div>
            <h1>Loan Payments</h1>
            <p>Confirm borrower payment references so outstanding balances are reduced.</p>
        </div>
    </section>

    <section class="stats stats-compact">
        <article class="stat">
            <p class="stat-label">Pending confirmation</p>
            <p class="stat-value tone-red">{{ number_format($pendingCount) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">Confirmed total</p>
            <p class="stat-value tone-green">₱{{ number_format((float) $confirmedTotal, 2) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">All payment records</p>
            <p class="stat-value tone-blue">{{ number_format($paymentCount) }}</p>
        </article>
    </section>

    <nav class="filters" aria-label="Filter payments by status">
        <a href="{{ route('admin.loan-payments.index') }}" class="filter {{ $status === null ? 'is-active' : '' }}">All</a>
        @foreach ($statuses as $option)
            <a href="{{ route('admin.loan-payments.index', ['status' => $option]) }}" class="filter {{ $status === $option ? 'is-active' : '' }}">{{ ucfirst($option) }}</a>
        @endforeach
    </nav>

    <section class="panel">
        <div class="table-scroll">
            <table class="table-wide">
                <thead>
                    <tr>
                        <th>Payment</th>
                        <th>Borrower</th>
                        <th>Loan</th>
                        <th>Reference</th>
                        <th class="num">Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>
                                <strong>{{ $payment->payment_number }}</strong>
                                <span class="muted">{{ $payment->created_at->format('M d, Y g:i A') }}</span>
                            </td>
                            <td>
                                {{ $payment->user->name }}
                                <span class="muted">{{ $payment->user->borrower_id }}</span>
                            </td>
                            <td>{{ $payment->loanApplication->application_number }}</td>
                            <td>{{ $payment->reference_number }}</td>
                            <td class="num">₱{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->methodLabel() }}</td>
                            <td><span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>
                                @if ($payment->status === 'pending')
                                    <form method="POST" action="{{ route('admin.loan-payments.update', $payment) }}" class="review-form">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="notes" placeholder="Notes (optional)" maxlength="255" aria-label="Notes for {{ $payment->payment_number }}">
                                        <div class="review-actions">
                                            <button type="submit" name="decision" value="confirmed" class="btn-approve">Confirm</button>
                                            <button type="submit" name="decision" value="rejected" class="btn-reject">Reject</button>
                                        </div>
                                    </form>
                                @else
                                    <span class="muted">{{ $payment->notes ?: 'No notes' }}</span>
                                    <span class="muted">{{ $payment->confirmed_at?->format('M d, Y g:i A') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">No payment references found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['items' => $payments])
    </section>
@endsection
