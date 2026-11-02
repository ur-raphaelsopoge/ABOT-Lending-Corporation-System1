@extends('admin.layouts.app')

@section('title', 'Loan Portfolio')

@section('content')
    <section class="page-head">
        <div>
            <h1>Loan Portfolio</h1>
            <p>Review requests, adjust the approved amount, and approve or reject each application.</p>
        </div>
    </section>

    <section class="stats">
        <article class="stat">
            <p class="stat-label">All applications</p>
            <p class="stat-value tone-blue">{{ number_format($totalCount) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">Pending review</p>
            <p class="stat-value tone-red">{{ number_format($pendingCount) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">Approved loans</p>
            <p class="stat-value tone-amber">{{ number_format($approvedCount) }}</p>
        </article>
        <article class="stat">
            <p class="stat-label">Approved total payable</p>
            <p class="stat-value tone-green">₱{{ number_format((float) $approvedPayable, 2) }}</p>
        </article>
    </section>

    <nav class="filters" aria-label="Filter applications by status">
        <a href="{{ route('admin.loans.index') }}" class="filter {{ $status === null ? 'is-active' : '' }}">All ({{ $totalCount }})</a>
        @foreach ($statuses as $option)
            <a href="{{ route('admin.loans.index', ['status' => $option]) }}" class="filter {{ $status === $option ? 'is-active' : '' }}">{{ ucfirst($option) }}</a>
        @endforeach
    </nav>

    <section class="panel">
        <div class="table-scroll">
            <table class="table-wide">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Borrower</th>
                        <th>Loan type</th>
                        <th class="num">Requested</th>
                        <th>Term</th>
                        <th>Breakdown (8%)</th>
                        <th>Status</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        @php
                            $breakdown = $application->breakdown();
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $application->application_number }}</strong>
                                <span class="muted">{{ \Illuminate\Support\Str::limit($application->purpose, 46) }}</span>
                            </td>
                            <td>
                                {{ $application->user->name }}
                                <span class="muted">{{ $application->user->borrower_id }}</span>
                            </td>
                            <td>{{ $application->loanTypeLabel() }}</td>
                            <td class="num">₱{{ number_format((float) $application->amount, 2) }}</td>
                            <td>{{ $application->term_months }} mo.</td>
                            <td>
                                <div class="breakdown">
                                    <span>Principal <strong>₱{{ number_format($breakdown['principal'], 2) }}</strong></span>
                                    <span>Interest <strong>₱{{ number_format($breakdown['interest'], 2) }}</strong></span>
                                    <span>Total <strong>₱{{ number_format($breakdown['total'], 2) }}</strong></span>
                                    <span>Monthly <strong>₱{{ number_format($breakdown['monthly'], 2) }}</strong></span>
                                </div>
                            </td>
                            <td><span class="badge badge-{{ $application->status }}">{{ ucfirst($application->status) }}</span></td>
                            <td>
                                @if ($application->status === 'pending')
                                    <form method="POST" action="{{ route('admin.loans.update', $application) }}" class="review-form">
                                        @csrf
                                        @method('PATCH')
                                        <label class="review-label" for="approved-{{ $application->id }}">
                                            Approved amount
                                            <span class="amount-field">
                                                <span aria-hidden="true">₱</span>
                                                <input type="text" id="approved-{{ $application->id }}" name="approved_amount" value="{{ number_format((float) $application->amount, 2, '.', '') }}" inputmode="decimal" autocomplete="off">
                                            </span>
                                        </label>
                                        <input type="text" name="admin_remarks" placeholder="Remarks (optional)" maxlength="255" aria-label="Remarks for {{ $application->application_number }}">
                                        <div class="review-actions">
                                            <button type="submit" name="decision" value="approved" class="btn-approve">Approve</button>
                                            <button type="submit" name="decision" value="rejected" class="btn-reject">Reject</button>
                                        </div>
                                    </form>
                                @else
                                    <span class="muted">Approved amount: ₱{{ number_format((float) ($application->approved_amount ?? $application->amount), 2) }}</span>
                                    <span class="muted">Total payable: ₱{{ number_format((float) ($application->total_payable ?? $breakdown['total']), 2) }}</span>
                                    <span class="muted">{{ $application->admin_remarks ?: 'No remarks' }}</span>
                                    <span class="muted">{{ $application->reviewed_at?->format('M d, Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">No loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['items' => $applications])
    </section>
@endsection