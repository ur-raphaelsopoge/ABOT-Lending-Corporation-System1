@extends('layouts.site')

@section('title', 'Policies | ABOT Lending Corporation System')

@section('content')
    <section class="page-head">
        <div class="container">
            <p class="eyebrow">Policies</p>
            <h1>Lending policies</h1>
            <p class="page-head__lead">The rules that apply to every loan application at ABOT Lending Corporation.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="card-grid">
                <article class="card">
                    <h3>Eligibility</h3>
                    <p>Applicants must be at least 18 years old and provide a valid government ID. Each ID and each email address can be used for only one borrower account.</p>
                </article>
                <article class="card">
                    <h3>Loan amount</h3>
                    <p>Each application may request from ₱{{ number_format(\App\Models\LoanApplication::MIN_AMOUNT) }} up to ₱{{ number_format(\App\Models\LoanApplication::MAX_AMOUNT) }}. Requests outside this range cannot be submitted.</p>
                </article>
                <article class="card">
                    <h3>Payment terms</h3>
                    <p>Available terms are {{ implode(', ', \App\Models\LoanApplication::TERMS) }} months. Choose the term that fits your monthly income.</p>
                </article>
                <article class="card">
                    <h3>Application review</h3>
                    <p>Every application starts as Pending. An administrator reviews it and marks it Approved or Rejected, and may add remarks that you can see on your account.</p>
                </article>
                <article class="card">
                    <h3>Submitted applications</h3>
                    <p>Once submitted, an application cannot be edited. Check the loan type, amount, term, and purpose before you submit.</p>
                </article>
                <article class="card">
                    <h3>Loan purpose</h3>
                    <p>Describe the purpose of the loan truthfully and for a lawful use. False or incomplete information can result in the rejection of your application.</p>
                </article>
                <article class="card">
                    <h3>Emergency contact</h3>
                    <p>Provide a contact person who can be reached if we need to verify your information. The contact number must be different from your own.</p>
                </article>
                <article class="card">
                    <h3>Privacy</h3>
                    <p>Your personal and financial information is used only to evaluate your loan application and manage your account. Access to borrower records is limited to authorized administrators.</p>
                </article>
                <article class="card">
                    <h3>Account security</h3>
                    <p>Keep your password private. If you forget it, use Forgot password to reset it, and report any unauthorized activity on your account right away.</p>
                </article>
            </div>
        </div>
    </section>
@endsection
