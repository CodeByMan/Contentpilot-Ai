<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Muhammad Ali Nawaz">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/images/contentpilot-icon.svg') }}">
    <title>@yield('title', 'Administration') · ContentPilot</title>
    <link rel="stylesheet" href="{{ asset('assets/css/contentpilot.css') }}">
    @stack('styles')
</head>
<body class="nk-body cp-dashboard-body">
<div class="nk-app-root" data-sidebar-collapse="lg"><div class="nk-main">
    @include('admin.body.sidebar')
    <div class="nk-wrap">
        @include('admin.body.mobile')
        <main class="nk-content">
            <div class="container-fluid cp-dashboard-container">
                @if ($errors->any())<div class="alert alert-danger" role="alert"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                @yield('admin')
            </div>
        </main>
        @include('admin.body.footer')
    <x-dashboard-toast />
    </div>
</div></div>
<script nonce="{{ $cspNonce }}" src="{{ asset('assets/js/contentpilot.js') }}"></script>
@stack('scripts')
</body>
</html>
