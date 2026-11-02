@extends('layouts.site')

@section('title', 'ABOT Lending Corporation System')

@section('content')
    <section class="hero" id="top" style="--hero-image: url('{{ asset('pictures/laptop.jpg') }}');">
        <div class="container hero__inner">
            <div class="hero__copy">
                <p class="eyebrow">Welcome</p>
                <h1 class="hero__title">Your trusted partner in every step of growth</h1>
                <p class="hero__lead">Build, apply, review, approve, disburse, and analyze loan accounts and borrower requests in one secure institutional workspace.</p>
                <div class="hero__actions">
                    <a class="site-button site-button--primary" href="{{ route('docs') }}">Read the docs</a>
                    <a class="site-button site-button--ghost" href="{{ route('register') }}">Sign up</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="about">
        <div class="container">
            <h2 class="section__title">About</h2>
            <p class="section__text">ABOT Lending Corporation System is a cooperative loan management system. Borrowers register, apply for a loan, and follow every application from one secure account, while administrators review each request and keep the records in order.</p>

            <div class="card-grid">
                @foreach (\App\Models\User::LOAN_TYPES as $type)
                    <article class="card">
                        <h3>{{ $type['label'] }}</h3>
                        <p>{{ $type['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--tint" id="how-it-works">
        <div class="container">
            <h2 class="section__title">How it Works</h2>

            <ol class="steps">
                <li class="step">
                    <span class="step__number">1</span>
                    <h3>Read the docs</h3>
                    <p>Learn what you need before you register.</p>
                </li>
                <li class="step">
                    <span class="step__number">2</span>
                    <h3>Create your account</h3>
                    <p>Register and receive your Borrower ID automatically.</p>
                </li>
                <li class="step">
                    <span class="step__number">3</span>
                    <h3>Apply for a loan</h3>
                    <p>Choose a loan type, amount, payment term, and purpose.</p>
                </li>
                <li class="step">
                    <span class="step__number">4</span>
                    <h3>Track your application</h3>
                    <p>Follow it from Pending to Approved or Rejected, with remarks from the administrator.</p>
                </li>
            </ol>
        </div>
    </section>
@endsection