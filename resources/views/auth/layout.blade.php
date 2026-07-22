<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Muhammad Ali Nawaz">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Secure ContentPilot account access.">
    <link rel="icon" href="{{ asset('assets/images/contentpilot-icon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'Account') | ContentPilot</title>
    <link rel="stylesheet" href="{{ asset('assets/css/contentpilot.css') }}">
</head>
<body class="nk-body cp-auth-body">
    <main class="cp-auth-shell">
        <section class="cp-auth-showcase">
            <a href="{{ route('home') }}" class="cp-auth-logo" aria-label="ContentPilot home"><img src="{{ asset('assets/images/contentpilot-logo-light.svg') }}" alt="ContentPilot"></a>
            <div class="cp-auth-copy">
                <div class="cp-eyebrow"><span class="cp-eyebrow-dot"></span> Create without the clutter</div>
                <h2>One workspace for your next great idea.</h2>
                <p>Write, design, organize, and refine AI-assisted content with a workflow that feels effortless.</p>
                <div class="cp-auth-mini-canvas" aria-hidden="true">
                    <div class="cp-auth-art cp-auth-art-one"><span>MAKE</span><strong>ideas<br>move.</strong></div>
                    <div class="cp-auth-art cp-auth-art-two"><span>Campaign</span><strong>Fresh<br>thinking</strong></div>
                    <div class="cp-auth-float"><em class="cp-icon cp-icon-spark"></em><span><strong>12.4k</strong><small>words created</small></span></div>
                </div>
            </div>
            <div class="cp-auth-proof"><span><em class="cp-icon cp-icon-lock"></em> Secure account access</span><span>Private workspace</span></div>
        </section>
        <section class="cp-auth-panel">
            <div class="cp-auth-panel-inner">
                <a href="{{ route('home') }}" class="cp-auth-mobile-logo"><img src="{{ asset('assets/images/contentpilot-logo.svg') }}" alt="ContentPilot"></a>
                <div class="cp-auth-heading"><h1>@yield('heading')</h1>@hasSection('intro')<p>@yield('intro')</p>@endif</div>
                @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="alert alert-danger" role="alert"><strong>Please correct the highlighted fields.</strong></div>@endif
                <div class="cp-auth-form">@yield('content')</div>
                <p class="cp-auth-footer">© {{ now()->year }} ContentPilot · Muhammad Ali Nawaz</p>
            </div>
        </section>
    </main>
    <script nonce="{{ $cspNonce }}" src="{{ asset('assets/js/contentpilot.js') }}"></script>
</body>
</html>
