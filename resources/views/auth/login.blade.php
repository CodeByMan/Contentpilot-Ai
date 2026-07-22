@extends('auth.layout')
@section('title', 'Sign In')
@section('heading', 'Welcome back')
@section('intro', 'Sign in to continue creating and managing your private content workspace.')
@section('content')
<form method="POST" action="{{ route('login') }}" novalidate>@csrf
    <div class="mb-3"><label class="form-label" for="email">Email address</label><div class="cp-input-wrap"><em class="cp-icon cp-icon-user"></em><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" maxlength="180" placeholder="you@example.com" class="form-control @error('email') is-invalid @enderror"></div>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label" for="password">Password</label><div class="cp-input-wrap"><em class="cp-icon cp-icon-lock"></em><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="form-control @error('password') is-invalid @enderror"></div>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input id="remember" type="checkbox" name="remember" value="1" class="form-check-input" @checked(old('remember'))><label for="remember" class="form-check-label">Remember me</label></div><a href="{{ route('password.request') }}">Forgot password?</a></div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Sign in <span aria-hidden="true">→</span></button>
</form>
<div class="cp-auth-divider"><span>New to ContentPilot?</span></div><a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg w-100">Create an account</a>
@endsection
