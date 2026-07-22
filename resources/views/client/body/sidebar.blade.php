@php
    $sidebarUser = auth()->user()->loadMissing('plan');
    $sidebarLimit = max(0, (int) $sidebarUser->word_limit);
    $sidebarUsed = max(0, (int) $sidebarUser->words_used + (int) $sidebarUser->words_reserved);
    $sidebarPercent = $sidebarLimit > 0 ? min(100, round(($sidebarUsed / $sidebarLimit) * 100)) : 0;
@endphp
<aside class="nk-sidebar nk-sidebar-fixed" id="sidebar">
    <div class="nk-sidebar-element nk-sidebar-head"><div class="nk-sidebar-brand"><a href="{{ route('user.dashboard') }}" class="logo-link" aria-label="ContentPilot dashboard"><div class="logo-wrap"><img class="logo-img logo-light" src="{{ asset('assets/images/contentpilot-logo.svg') }}" alt="ContentPilot"><img class="logo-img logo-icon" src="{{ asset('assets/images/contentpilot-icon.svg') }}" alt="ContentPilot"></div></a></div></div>
    <div class="nk-sidebar-element nk-sidebar-body"><div class="nk-sidebar-content h-100" data-simplebar>
        <div class="cp-sidebar-create"><a href="{{ route('user.templates.index') }}" class="btn btn-primary w-100"><em class="cp-icon cp-icon-spark"></em><span>Create a design</span></a></div>
        <nav class="nk-sidebar-menu" aria-label="Workspace navigation"><ul class="nk-menu">
            <li class="nk-menu-item"><a href="{{ route('user.dashboard') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-dashboard"></em></span><span class="nk-menu-text">Home</span></a></li>
            <li class="nk-menu-item"><a href="{{ route('user.templates.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-spark"></em></span><span class="nk-menu-text">Templates</span></a></li>
            <li class="nk-menu-item"><a href="{{ route('user.documents.index') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-document"></em></span><span class="nk-menu-text">Projects</span></a></li>
            <li class="nk-menu-heading"><h6 class="overline-title">Create</h6></li>
            <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-icon"><em class="cp-icon cp-icon-image"></em></span><span class="nk-menu-text">Images</span></a><ul class="nk-menu-sub"><li><a href="{{ route('user.images.create') }}" class="nk-menu-link">Generate image</a></li><li><a href="{{ route('user.images.index') }}" class="nk-menu-link">Image library</a></li></ul></li>
            <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span class="nk-menu-icon"><em class="cp-icon cp-icon-audio"></em></span><span class="nk-menu-text">Audio</span></a><ul class="nk-menu-sub"><li><a href="{{ route('user.audio.create') }}" class="nk-menu-link">Generate audio</a></li><li><a href="{{ route('user.audio.index') }}" class="nk-menu-link">Audio library</a></li></ul></li>
            <li class="nk-menu-heading"><h6 class="overline-title">Account</h6></li>
            <li class="nk-menu-item"><a href="{{ route('user.checkout') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-billing"></em></span><span class="nk-menu-text">Plan and billing</span></a></li>
            <li class="nk-menu-item"><a href="{{ route('user.profile') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-user"></em></span><span class="nk-menu-text">Profile</span></a></li>
            <li class="nk-menu-item"><a href="{{ route('user.password.edit') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-lock"></em></span><span class="nk-menu-text">Security</span></a></li>
        </ul></nav>
        <div class="cp-usage-card"><div class="cp-usage-card-head"><span><strong>{{ $sidebarUser->plan?->name ?? 'No plan' }}</strong><small>Current plan</small></span><a href="{{ route('user.checkout') }}">Upgrade</a></div><div class="progress" role="progressbar" aria-label="Monthly word usage" aria-valuenow="{{ $sidebarPercent }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-width-{{ $sidebarPercent }}"></div></div><div class="cp-usage-card-foot"><span>{{ number_format($sidebarUsed) }} used</span><span>{{ number_format($sidebarLimit) }} total</span></div></div>
        <div class="cp-sidebar-footer"><a href="{{ route('home') }}" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-home"></em></span><span class="nk-menu-text">View website</span></a><form method="POST" action="{{ route('user.logout') }}">@csrf<button type="submit" class="nk-menu-link"><span class="nk-menu-icon"><em class="cp-icon cp-icon-signout"></em></span><span class="nk-menu-text">Sign out</span></button></form></div>
    </div></div>
</aside>
