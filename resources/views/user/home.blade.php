@extends('layouts.site')

@section('title', 'My Account | ABOT Lending Corporation System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endpush

@section('content')
    @php
        $loanIcons = [
            'personal' => '<path d="M4 8h16v11H4z"/><path d="M9 8V5.5h6V8"/><path d="M4 13h16"/>',
            'business' => '<path d="M3 9h18v10H3z"/><path d="M8 9V6h8v3"/><path d="M3 14h18"/>',
            'emergency' => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5l3 2"/>',
        ];
        $initials = collect(explode(' ', $user->name))->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    @endphp

    <section class="account-hero">
        <div class="container">
            <p class="eyebrow">My account</p>
            <h1>Welcome back, {{ strtok($user->name, ' ') }}!</h1>
            <p class="account-hero__lead">Track credits, settle your balance, apply for loans, and keep your profile up to date.</p>
            <div class="account-hero__meta">
                <span class="id-chip">{{ $user->borrower_id }}</span>
                <span>{{ $user->email }}</span>
            </div>
            <div class="account-stats">
                <article>
                    <span>Credits received</span>
                    <strong>₱{{ number_format((float) $user->credits, 2) }}</strong>
                </article>
                <article>
                    <span>Balance owed</span>
                    <strong>₱{{ number_format((float) $user->balance_owed, 2) }}</strong>
                </article>
                <article>
                    <span>Loan status</span>
                    <strong>{{ $activeLoan ? 'Active term' : 'Eligible to apply' }}</strong>
                </article>
            </div>
        </div>
    </section>

    <nav class="account-tabs" aria-label="Account sections">
        <div class="container account-tabs__inner">
            <a href="#loans" class="account-tab is-active">Loans</a>
            <a href="#applications" class="account-tab">My Applications</a>
            <a href="#payments" class="account-tab">Payments</a>
            <a href="#profile" class="account-tab">Profile</a>
            <a href="{{ route('docs') }}" class="account-tab">FAQs</a>
            <form method="POST" action="{{ route('logout') }}" class="account-tabs__logout">
                @csrf
                <button type="submit" class="account-tab account-tab--logout">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container account-body">
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif

        <section id="loans" class="account-section">
            <h2 class="section-title">Available loans</h2>
            <div class="loan-list">
                @foreach ($loanTypes as $value => $type)
                    <article class="loan-card">
                        <div class="loan-card__art">
                            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $loanIcons[$value] !!}</svg>
                        </div>
                        <div class="loan-card__body">
                            <h3>{{ $type['label'] }}</h3>
                            <p>{{ $type['description'] }}</p>
                        </div>
                        @if ($canApply)
                            <a class="btn-offer" href="{{ route('home', ['type' => $value]) }}#apply">View offers</a>
                        @else
                            <span class="btn-offer btn-offer--disabled">Locked</span>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section id="apply" class="account-section panel">
            <h2>Apply for a loan</h2>
            @if (! $canApply)
                <p class="panel-note">You currently have an approved loan with an outstanding balance of ₱{{ number_format((float) $user->balance_owed, 2) }}. Finish your term payments before applying for another loan.</p>
            @else
                <p class="panel-note">You can borrow from ₱{{ number_format($minAmount) }} up to ₱{{ number_format($maxAmount) }} per application. Interest is {{ $annualRate }}% per year, computed as flat interest for the term you choose.</p>

                <form method="POST" action="{{ route('loan-applications.store') }}" novalidate>
                    @csrf

                    <fieldset class="field">
                        <legend>Loan type</legend>
                        <div class="choice-list">
                            @foreach ($loanTypes as $value => $type)
                                <label class="choice">
                                    <input type="radio" name="loan_type" value="{{ $value }}" {{ old('loan_type', $selectedType ?? $user->loan_type) === $value ? 'checked' : '' }}>
                                    <span>{{ $type['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('loan_type')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="form-grid">
                        <div class="field">
                            <label for="amount">Loan amount</label>
                            <div class="input-prefix">
                                <span aria-hidden="true">₱</span>
                                <input type="text" id="amount" name="amount" value="{{ old('amount') }}" placeholder="10,000" inputmode="decimal" autocomplete="off" class="{{ $errors->has('amount') ? 'is-invalid' : '' }}">
                            </div>
                            @error('amount')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="term_months">Payment term</label>
                            <select id="term_months" name="term_months" class="{{ $errors->has('term_months') ? 'is-invalid' : '' }}">
                                @foreach ($terms as $term)
                                    <option value="{{ $term }}" {{ (int) old('term_months', 6) === $term ? 'selected' : '' }}>{{ $term }} {{ $term === 1 ? 'month' : 'months' }}</option>
                                @endforeach
                            </select>
                            @error('term_months')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="loan-preview" id="loan-preview" hidden>
                        <div>
                            <span>Principal</span>
                            <strong data-field="principal">₱0.00</strong>
                        </div>
                        <div>
                            <span>Interest ({{ $annualRate }}%)</span>
                            <strong data-field="interest">₱0.00</strong>
                        </div>
                        <div>
                            <span>Total payable</span>
                            <strong data-field="total">₱0.00</strong>
                        </div>
                        <div>
                            <span>Monthly payment</span>
                            <strong data-field="monthly">₱0.00</strong>
                        </div>
                    </div>

                    <div class="field">
                        <label for="purpose">Purpose of the loan</label>
                        <textarea id="purpose" name="purpose" rows="3" placeholder="Briefly tell us what you will use the loan for" class="{{ $errors->has('purpose') ? 'is-invalid' : '' }}">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-submit">Submit application</button>
                </form>
            @endif
        </section>

        <section id="applications" class="account-section panel">
            <h2>My applications</h2>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Loan type</th>
                            <th class="num">Requested</th>
                            <th>Term</th>
                            <th>Breakdown ({{ $annualRate }}%)</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            @php
                                $breakdown = $application->breakdown();
                            @endphp
                            <tr>
                                <td><strong>{{ $application->application_number }}</strong></td>
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
                                <td>
                                    <span class="badge badge-{{ $application->status }}">{{ ucfirst($application->status) }}</span>
                                    @if ($application->repayment_status)
                                        <span class="muted">Repayment: {{ ucfirst($application->repayment_status) }}</span>
                                    @endif
                                    @if ($application->status === 'approved' && $application->outstanding_balance !== null)
                                        <span class="muted">Owed: ₱{{ number_format((float) $application->outstanding_balance, 2) }}</span>
                                    @endif
                                </td>
                                <td>{{ $application->created_at->format('M d, Y') }}</td>
                                <td>{{ $application->admin_remarks ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty">You have not submitted any loan applications yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="payments" class="account-section panel">
            <h2>Loan payments</h2>
            @if ($activeLoan)
                <p class="panel-note">Submit a payment reference for {{ $activeLoan->application_number }}. An admin will confirm it before your balance goes down.</p>

                <form method="POST" action="{{ route('loan-payments.store') }}" class="payment-form" novalidate>
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="reference_number">Payment reference</label>
                            <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. GCASH-123456" class="{{ $errors->has('reference_number') ? 'is-invalid' : '' }}">
                            @error('reference_number')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="payment_amount">Amount</label>
                            <div class="input-prefix">
                                <span aria-hidden="true">₱</span>
                                <input type="text" id="payment_amount" name="amount" value="{{ old('amount') }}" placeholder="{{ number_format((float) $activeLoan->monthly_amortization, 2, '.', '') }}" inputmode="decimal" class="{{ $errors->has('amount') ? 'is-invalid' : '' }}">
                            </div>
                            @error('amount')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="payment_method">Payment method</label>
                            <select id="payment_method" name="payment_method" class="{{ $errors->has('payment_method') ? 'is-invalid' : '' }}">
                                @foreach ($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method', 'gcash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="payment_notes">Notes (optional)</label>
                            <input type="text" id="payment_notes" name="notes" value="{{ old('notes') }}" maxlength="255">
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Submit payment reference</button>
                </form>
            @else
                <p class="panel-note">No active loan yet. Approved loans will appear here so you can submit payment references.</p>
            @endif

            <div class="table-scroll payment-history">
                <table>
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Loan</th>
                            <th>Reference</th>
                            <th class="num">Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td><strong>{{ $payment->payment_number }}</strong></td>
                                <td>{{ $payment->loanApplication->application_number }}</td>
                                <td>{{ $payment->reference_number }}</td>
                                <td class="num">₱{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->methodLabel() }}</td>
                                <td><span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty">No payment references submitted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="profile" class="account-section">
            <article class="profile-card">
                <div class="profile-card__banner" aria-hidden="true"></div>
                <div class="profile-card__head">
                    <div class="profile-card__identity">
                        <span class="profile-card__avatar">{{ $initials }}</span>
                        <div>
                            <h2>{{ $user->name }}</h2>
                            <p>{{ $user->email }}</p>
                            <p class="muted">{{ $user->borrower_id }} · {{ $user->idTypeLabel() }} {{ $user->maskedIdNumber() }}</p>
                        </div>
                    </div>
                    <button type="submit" form="profile-form" class="btn-submit profile-card__save">Save profile</button>
                </div>

                <form id="profile-form" method="POST" action="{{ route('profile.update') }}" class="profile-card__form" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="form-grid">
                        <div class="field">
                            <label for="profile_name">Full name</label>
                            <input type="text" id="profile_name" name="name" value="{{ old('name', $user->name) }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="profile_phone">Phone number</label>
                            <input type="text" id="profile_phone" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="{{ $errors->has('phone_number') ? 'is-invalid' : '' }}">
                            @error('phone_number')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="profile_employment">Employment status</label>
                            <select id="profile_employment" name="employment_status" class="{{ $errors->has('employment_status') ? 'is-invalid' : '' }}">
                                @foreach ($employmentStatuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('employment_status', $user->employment_status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('employment_status')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="profile_income">Monthly income</label>
                            <div class="input-prefix">
                                <span aria-hidden="true">₱</span>
                                <input type="text" id="profile_income" name="monthly_income" value="{{ old('monthly_income', number_format((float) $user->monthly_income, 2, '.', '')) }}" inputmode="decimal" class="{{ $errors->has('monthly_income') ? 'is-invalid' : '' }}">
                            </div>
                            @error('monthly_income')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="profile_loan_type">Preferred loan type</label>
                            <select id="profile_loan_type" name="loan_type" class="{{ $errors->has('loan_type') ? 'is-invalid' : '' }}">
                                @foreach ($loanTypes as $value => $type)
                                    <option value="{{ $value }}" {{ old('loan_type', $user->loan_type) === $value ? 'selected' : '' }}>{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                            @error('loan_type')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label>Date of birth</label>
                            <input type="text" value="{{ $user->date_of_birth->format('F j, Y') }}" disabled>
                        </div>
                    </div>

                    <div class="field">
                        <label for="profile_address">Residential address</label>
                        <textarea id="profile_address" name="address" rows="3" class="{{ $errors->has('address') ? 'is-invalid' : '' }}">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <h3 class="profile-card__subtitle">Emergency contact</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label for="profile_emergency_name">Full name</label>
                            <input type="text" id="profile_emergency_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" class="{{ $errors->has('emergency_contact_name') ? 'is-invalid' : '' }}">
                            @error('emergency_contact_name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="profile_emergency_relationship">Relationship</label>
                            <input type="text" id="profile_emergency_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}" class="{{ $errors->has('emergency_contact_relationship') ? 'is-invalid' : '' }}">
                            @error('emergency_contact_relationship')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="field">
                        <label for="profile_emergency_phone">Phone number</label>
                        <input type="text" id="profile_emergency_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}" class="{{ $errors->has('emergency_contact_phone') ? 'is-invalid' : '' }}">
                        @error('emergency_contact_phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profile-card__email">
                        <h3 class="profile-card__subtitle">My email address</h3>
                        <div class="profile-card__email-row">
                            <span class="profile-card__email-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 7 9-7"/></svg>
                            </span>
                            <div>
                                <strong>{{ $user->email }}</strong>
                                <span class="muted">Primary login email</span>
                            </div>
                        </div>
                    </div>
                </form>
            </article>
        </section>
    </div>

    @if ($canApply)
        <script>
            (function () {
                var amount = document.getElementById('amount');
                var term = document.getElementById('term_months');
                var preview = document.getElementById('loan-preview');

                if (!amount || !term || !preview) {
                    return;
                }

                var rate = {{ $annualRate }};
                var minimum = {{ $minAmount }};

                function round2(value) {
                    return Math.round(value * 100) / 100;
                }

                function peso(value) {
                    return '₱' + value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function render() {
                    var principal = parseFloat(amount.value.replace(/[^\d.]/g, ''));
                    var months = parseInt(term.value, 10);

                    if (!principal || principal < minimum || !months) {
                        preview.hidden = true;
                        return;
                    }

                    var interest = round2(principal * rate / 100 * months / 12);
                    var total = round2(principal + interest);
                    var monthly = round2(total / months);

                    preview.querySelector('[data-field="principal"]').textContent = peso(round2(principal));
                    preview.querySelector('[data-field="interest"]').textContent = peso(interest);
                    preview.querySelector('[data-field="total"]').textContent = peso(total);
                    preview.querySelector('[data-field="monthly"]').textContent = peso(monthly);
                    preview.hidden = false;
                }

                amount.addEventListener('input', render);
                term.addEventListener('change', render);
                render();
            })();
        </script>
    @endif
@endsection
