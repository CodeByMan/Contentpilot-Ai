<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Muhammad Ali Nawaz">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ContentPilot is a secure AI-assisted workspace for creating, organizing, and refining content.">
    <link rel="shortcut icon" href="{{ asset('assets/images/contentpilot-icon.svg') }}">
    <title>@yield('title', 'ContentPilot')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/contentpilot.css') }}">
</head>
<body class="nk-body cp-public-body" data-menu-collapse="lg">
    <div class="nk-app-root">
        @include('home.page.body.header')
        <main class="nk-pages">
            @if (session('message'))
                <div class="container cp-flash-wrap">
                    <div class="alert alert-{{ session('alert-type') === 'error' ? 'danger' : session('alert-type', 'info') }}">{{ session('message') }}</div>
                </div>
            @endif
            @if ($errors->any())
                <div class="container cp-flash-wrap">
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif
            @yield('page')
        </main>
        @include('home.body.footer')
    </div>
    <script nonce="{{ $cspNonce }}" src="{{ asset('assets/js/contentpilot.js') }}"></script>
</body>
</html>
