<header class="nk-header-main nk-menu-main will-shrink is-transparent ignore-mask" data-public-header>
    <div class="container">
        <div class="nk-header-wrap">
            <div class="nk-header-logo">
                <a href="{{ route('home') }}" class="logo-link" aria-label="ContentPilot home">
                    <img class="logo-img" src="{{ asset('assets/images/contentpilot-logo.svg') }}" alt="ContentPilot">
                </a>
            </div>
            <div class="nk-header-toggle">
                <button type="button" class="btn btn-light btn-icon header-menu-toggle" aria-label="Toggle navigation">
                    <em class="cp-icon cp-icon-menu"></em>
                </button>
            </div>
            <nav class="nk-header-menu nk-menu" aria-label="Primary navigation">
                <ul class="nk-menu-list mx-auto">
                    <li class="nk-menu-item"><a href="{{ route('home') }}" class="nk-menu-link"><span class="nk-menu-text">Home</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('home') }}#about" class="nk-menu-link"><span class="nk-menu-text">About</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('usecase') }}" class="nk-menu-link"><span class="nk-menu-text">Use cases</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('features') }}" class="nk-menu-link"><span class="nk-menu-text">Features</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('pricing') }}" class="nk-menu-link"><span class="nk-menu-text">Pricing</span></a></li>
                    <li class="nk-menu-item"><a href="{{ route('contact') }}" class="nk-menu-link"><span class="nk-menu-text">Contact</span></a></li>
                </ul>
                <ul class="nk-menu-buttons flex-lg-row-reverse">
                    @auth
                        <li><a class="btn btn-primary" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}"><span>Open workspace</span><span aria-hidden="true">→</span></a></li>
                    @else
                        <li><a class="btn btn-primary" href="{{ route('register') }}">Start creating</a></li>
                        <li><a class="btn btn-ghost" href="{{ route('login') }}">Sign in</a></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </div>
</header>
