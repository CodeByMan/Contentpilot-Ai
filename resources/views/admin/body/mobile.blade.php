<header class="nk-header nk-header-fixed cp-dashboard-topbar">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-header-logo">
                <button type="button" class="btn btn-icon sidebar-toggle" aria-label="Toggle navigation"><em class="cp-icon cp-icon-menu"></em></button>
                <a href="{{ route('admin.dashboard') }}" class="logo-link"><img class="logo-img logo-icon" src="{{ asset('assets/images/contentpilot-icon.svg') }}" alt="ContentPilot"></a>
            </div>
            <div class="cp-topbar-search"><em class="cp-icon cp-icon-search"></em><span>Search your workspace</span><kbd>⌘ K</kbd></div>
            <div class="nk-header-tools">
                <details class="cp-user-menu">
                    <summary class="cp-user-chip" aria-label="Open administrator menu">
                        <img
                            src="{{ auth()->user()->photo_url ?: asset('assets/images/avatar-placeholder.svg') }}"
                            data-avatar-fallback
                            data-fallback-src="{{ asset('assets/images/avatar-placeholder.svg') }}"
                            alt="{{ auth()->user()->name }}"
                        >
                        <span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span>
                        <em class="cp-icon cp-icon-chevron-down cp-user-menu-chevron"></em>
                    </summary>
                    <div class="cp-user-dropdown" role="menu">
                        <a href="{{ route('admin.profile') }}" role="menuitem"><em class="cp-icon cp-icon-user"></em><span>Profile</span></a>
                        <a href="{{ route('admin.password.edit') }}" role="menuitem"><em class="cp-icon cp-icon-settings"></em><span>Settings</span></a>
                        <a href="{{ route('home') }}" role="menuitem"><em class="cp-icon cp-icon-external"></em><span>View website</span></a>
                        <div class="cp-user-dropdown-separator"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" role="menuitem"><em class="cp-icon cp-icon-signout"></em><span>Sign out</span></button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>
