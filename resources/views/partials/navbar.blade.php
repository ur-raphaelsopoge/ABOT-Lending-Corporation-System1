<header class="site-nav">
    <div class="site-nav__inner">
        <a class="site-nav__brand" href="{{ route('landing') }}">
            <span class="site-nav__logo">
                <img src="{{ asset('pictures/ccdi.png') }}" alt="">
            </span>
            <span class="site-nav__name">
                <strong>ABOT Lending</strong>
                <small>Corporation System</small>
            </span>
        </a>

        <button type="button" class="site-nav__toggle" data-nav-toggle aria-expanded="false" aria-controls="site-menu" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="site-nav__menu" id="site-menu" data-nav-menu aria-label="Main navigation">
            <ul class="site-nav__links">
                <li>
                    <a class="site-nav__link {{ request()->routeIs('landing') ? 'is-active' : '' }}" href="{{ route('landing') }}" @if (request()->routeIs('landing')) aria-current="page" @endif>Home</a>
                </li>
                <li>
                    <a class="site-nav__link" href="{{ route('landing') }}#about">About</a>
                </li>
                <li>
                    <a class="site-nav__link" href="{{ route('landing') }}#how-it-works">How it Works</a>
                </li>
                <li>
                    <a class="site-nav__link {{ request()->routeIs('docs') ? 'is-active' : '' }}" href="{{ route('docs') }}" @if (request()->routeIs('docs')) aria-current="page" @endif>Docs</a>
                </li>
                <li>
                    <a class="site-nav__link {{ request()->routeIs('policies') ? 'is-active' : '' }}" href="{{ route('policies') }}" @if (request()->routeIs('policies')) aria-current="page" @endif>Policies</a>
                </li>
                <li>
                    <a class="site-nav__link {{ request()->routeIs('get-in-touch') ? 'is-active' : '' }}" href="{{ route('get-in-touch') }}" @if (request()->routeIs('get-in-touch')) aria-current="page" @endif>Get in Touch</a>
                </li>
            </ul>

            <div class="site-nav__actions">
                @auth('admin')
                    <a class="site-nav__btn site-nav__btn--solid" href="{{ route('admin.dashboard') }}">Dashboard</a>
                @elseauth('web')
                    <div class="site-nav__socials" aria-label="Social media profiles">
                        <a href="https://www.facebook.com/Cracksy" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v2H6v4h3v5h4v-5h3l1-4h-4V9c0-.7.3-1 1-1Z"/></svg>
                        </a>
                        <a href="https://github.com/ur-raphaelsopoge" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 19c-4 1-4-2-6-2m12 4v-3.5c0-1 .1-1.5-.5-2 3-.3 6-1.5 6-6.5a5 5 0 0 0-1.4-3.5A4.6 4.6 0 0 0 19 2s-1.2-.4-4 1.5a13 13 0 0 0-6 0C6.2 1.6 5 2 5 2a4.6 4.6 0 0 0-.1 3.5A5 5 0 0 0 3.5 9c0 5 3 6.2 6 6.5-.6.5-.6 1.2-.5 2V21"/></svg>
                        </a>
                        <a href="https://www.instagram.com/crackshie_/?hl=en" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg>
                        </a>
                    </div>
                @else
                    <a class="site-nav__btn site-nav__btn--outline" href="{{ route('login') }}">Login</a>
                    <a class="site-nav__btn site-nav__btn--solid" href="{{ route('register') }}">Sign Up</a>
                @endauth
            </div>
        </nav>
    </div>
</header>

<script>
    (function () {
        var toggle = document.querySelector('[data-nav-toggle]');
        var menu = document.querySelector('[data-nav-menu]');

        if (!toggle || !menu) {
            return;
        }

        function setOpen(open) {
            menu.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            setOpen(!menu.classList.contains('is-open'));
        });

        menu.addEventListener('click', function (event) {
            if (event.target.closest('a')) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    })();
</script>
