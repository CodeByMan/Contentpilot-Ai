@extends('auth.layout')
@section('title', 'Verify Email')
@section('heading', 'Verify your email address')
@section('intro', 'Open the verification link sent to your email before accessing protected application features.')
@section('content')
    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success" role="status">A new verification link has been sent.</div>
    @endif
    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary w-100">Resend verification email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">Sign out</button>
    </form>
@endsection
