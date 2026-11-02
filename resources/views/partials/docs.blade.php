@extends('layouts.site')

@section('title', 'Docs | ABOT Lending Corporation System')

@section('content')
    <section class="page-head">
        <div class="container">
            <p class="eyebrow">Documentation</p>
            <h1>Borrower guide</h1>
            <p class="page-head__lead">Everything you need to know before you sign in.</p>
        </div>
    </section>

    <div class="container docs">
        <aside class="docs__toc">
            <nav aria-label="On this page">
                <ul>
                    <li><a href="#register">Creating an account</a></li>
                    <li><a href="#sign-in">Signing in</a></li>
                    <li><a href="#apply">Applying for a loan</a></li>
                    <li><a href="#status">Application status</a></li>
                </ul>
            </nav>
        </aside>

        <article class="docs__body">
            <section id="register">
                <h2>Creating an account</h2>
                <p>Open the sign up page and prepare the following details.</p>
                <ul>
                    <li>Your full name and date of birth. You must be at least 18 years old.</li>
                    <li>Your mobile number, for example 0917 123 4567.</li>
                    <li>Your email address.</li>
                    <li>Your residential address.</li>
                    <li>A valid ID and its number. Accepted IDs: {{ implode(', ', \App\Models\User::ID_TYPES) }}.</li>
                    <li>Your employment status and monthly income.</li>
                    <li>An emergency contact with a relationship and a mobile number different from your own.</li>
                    <li>Your preferred loan type.</li>
                    <li>A password with at least 8 characters, including letters and numbers.</li>
                </ul>
                <p class="note">Each email address and each ID can only be used for one account. Your Borrower ID, for example BRW-00001, is assigned automatically after you register.</p>
            </section>

            <section id="sign-in">
                <h2>Signing in</h2>
                <p>Sign in with your Borrower ID or your email address and your password. Choose Remember me to stay signed in on your device.</p>
                <p>If you forget your password, choose Forgot password, enter your email address, and open the reset link sent to you. The link is valid for 60 minutes.</p>
            </section>

            <section id="apply">
                <h2>Applying for a loan</h2>
                <p>After you sign in, your account page shows your profile and the loan application form.</p>
                <ul>
                    <li>Choose a loan type: {{ implode(', ', array_column(\App\Models\User::LOAN_TYPES, 'label')) }}.</li>
                    <li>Enter an amount from ₱1,000 up to ₱50,000.</li>
                    <li>Choose a payment term.</li>
                    <li>Briefly describe the purpose of the loan.</li>
                </ul>
            </section>

            <section id="status">
                <h2>Application status</h2>
                <p>Every application appears in My loan applications on your account page.</p>
                <ul>
                    <li><strong>Pending</strong>: your application is waiting for review.</li>
                    <li><strong>Approved</strong>: the administrator approved your application.</li>
                    <li><strong>Rejected</strong>: the administrator did not approve your application.</li>
                </ul>
                <p>Any remarks from the administrator are shown beside the application.</p>
            </section>

            <div class="cta">
                <h2>Ready to get started?</h2>
                <div class="cta__actions">
                    <a class="site-button site-button--light" href="{{ route('login') }}">Login</a>
                    <a class="site-button site-button--outline" href="{{ route('register') }}">Sign up</a>
                </div>
            </div>
        </article>
    </div>
@endsection
