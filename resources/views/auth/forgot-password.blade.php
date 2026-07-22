@extends('auth.layout')
@section('title', 'Forgot Password')
@section('heading', 'Reset your password')
@section('intro', 'Enter your account email. If it exists, we will send a secure reset link.')
@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                autocomplete="email" maxlength="180" class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    </form>
    <p class="text-center small mt-4 mb-0"><a href="{{ route('login') }}">Return to sign in</a></p>
@endsection
