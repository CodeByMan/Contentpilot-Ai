<header class="nk-header nk-header-fixed cp-dashboard-topbar">
    <div class="container-fluid"><div class="nk-header-wrap">
        <div class="nk-header-logo"><button type="button" class="btn btn-icon sidebar-toggle" aria-label="Toggle navigation"><em class="cp-icon cp-icon-menu"></em></button><a href="{{ route('user.dashboard') }}" class="logo-link"><img class="logo-img logo-icon" src="{{ asset('assets/images/contentpilot-icon.svg') }}" alt="ContentPilot"></a></div>
        <div class="cp-topbar-search"><em class="cp-icon cp-icon-search"></em><span>Search your workspace</span><kbd>⌘ K</kbd></div>
        <div class="nk-header-tools"><a href="{{ route('user.profile') }}" class="cp-user-chip"><img src="{{ auth()->user()->photo_url ?: asset('assets/images/avatar-placeholder.svg') }}" data-avatar-fallback data-fallback-src="{{ asset('assets/images/avatar-placeholder.svg') }}" alt="{{ auth()->user()->name }}"><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->plan?->name ?? 'Content creator' }}</small></span></a></div>
    </div></div>
</header>
