@extends('auth.layout')
@section('title', 'Create Account')
@section('heading', 'Create your account')
@section('intro', 'Start with a secure workspace and the active free plan available at registration.')
@section('content')
<form method="POST" action="{{ route('register') }}" novalidate>@csrf
    <div class="mb-3"><label class="form-label" for="name">Full name</label><div class="cp-input-wrap"><em class="cp-icon cp-icon-user"></em><input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="120" placeholder="Your full name" class="form-control @error('name') is-invalid @enderror"></div>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label" for="email">Email address</label><div class="cp-input-wrap"><em class="cp-icon cp-icon-chat"></em><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" maxlength="180" placeholder="you@example.com" class="form-control @error('email') is-invalid @enderror"></div>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label" for="password">Password</label><div class="cp-input-wrap"><em class="cp-icon cp-icon-lock"></em><input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a strong password" class="form-control @error('password') is-invalid @enderror"></div><small class="form-note">At least 12 characters with uppercase, lowercase, a number, and a symbol.</small>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-4"><label class="form-label" for="password_confirmation">Confirm password</label><div class="cp-input-wrap"><em class="cp-icon cp-icon-lock"></em><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your password" class="form-control"></div></div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Create free account <span aria-hidden="true">→</span></button>
</form>
<div class="cp-auth-divider"><span>Already registered?</span></div><a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg w-100">Sign in instead</a>
@endsection
