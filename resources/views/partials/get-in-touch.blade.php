@extends('layouts.site')

@section('title', 'Get in Touch | ABOT Lending Corporation System')

@section('content')
    <section class="page-head">
        <div class="container">
            <p class="eyebrow">Contact</p>
            <h1>Get in Touch</h1>
            <p class="page-head__lead">Have a question or want to get in touch? Send us a message and we’ll get back to you.</p>
        </div>
    </section>

    <section class="section">
        <div class="container contact-layout">
            <div class="contact-intro">
                <h2 class="section__title">Let’s connect</h2>
                <p class="section__text">Send your inquiry through the form, or reach out directly by email or social media.</p>

                <a class="contact-email" href="mailto:johnraphaelabino@gmail.com">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                    <span>johnraphaelabino@gmail.com</span>
                </a>

                <div class="contact-socials" aria-label="Social media profiles">
                    <a href="https://www.facebook.com/Cracksy" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v2H6v4h3v5h4v-5h3l1-4h-4V9c0-.7.3-1 1-1Z"/></svg>
                    </a>
                    <a href="https://github.com/ur-raphaelsopoge" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 19c-4 1-4-2-6-2m12 4v-3.5c0-1 .1-1.5-.5-2 3-.3 6-1.5 6-6.5a5 5 0 0 0-1.4-3.5A4.6 4.6 0 0 0 19 2s-1.2-.4-4 1.5a13 13 0 0 0-6 0C6.2 1.6 5 2 5 2a4.6 4.6 0 0 0-.1 3.5A5 5 0 0 0 3.5 9c0 5 3 6.2 6 6.5-.6.5-.6 1.2-.5 2V21"/></svg>
                    </a>
                    <a href="https://www.instagram.com/crackshie_/?hl=en" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@ur.raphaelabino" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <svg class="contact-socials__tiktok" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h3c.2 1.5 1.1 2.9 2.5 3.7 1 .6 2 .9 3.1 1v3.2a10 10 0 0 1-6-2v6.6a6.5 6.5 0 1 1-6.5-6.5c.5 0 1 .1 1.5.2v3.5a3 3 0 1 0 2 2.8V3Z"/></svg>
                    </a>
                </div>
            </div>

            <div class="card contact-card">
                <form class="contact-form" data-contact-form>
                    <div class="contact-form__names">
                        <div class="contact-field">
                            <label for="first_name">First name</label>
                            <input id="first_name" name="first_name" type="text" autocomplete="given-name" maxlength="100" required>
                        </div>
                        <div class="contact-field">
                            <label for="last_name">Last name</label>
                            <input id="last_name" name="last_name" type="text" autocomplete="family-name" maxlength="100" required>
                        </div>
                    </div>
                    <div class="contact-field">
                        <label for="email">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" maxlength="255" required>
                    </div>
                    <div class="contact-field">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" maxlength="5000" required></textarea>
                    </div>
                    <button type="submit" class="site-button site-button--primary contact-submit">Open email app</button>
                </form>
                <p class="contact-email-note" role="status" data-contact-note hidden>Your email app will open with your message ready to send.</p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelector('[data-contact-form]').addEventListener('submit', function (event) {
            event.preventDefault();

            var formData = new FormData(event.currentTarget);
            var fullName = formData.get('first_name') + ' ' + formData.get('last_name');
            var subject = encodeURIComponent('Inquiry from ' + fullName);
            var body = encodeURIComponent(
                'Name: ' + fullName + '\nEmail: ' + formData.get('email') + '\n\nMessage:\n' + formData.get('message')
            );

            window.location.href = 'mailto:johnraphaelabino@gmail.com?subject=' + subject + '&body=' + body;
            document.querySelector('[data-contact-note]').hidden = false;
        });
    </script>
@endpush
