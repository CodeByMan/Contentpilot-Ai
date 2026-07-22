@extends('auth.layout')
@section('title', 'Confirm Password')
@section('heading', 'Confirm your password')
@section('intro', 'This action requires recent password confirmation.')
@section('content')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="password">Current password</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="current-password"
                class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Confirm password</button>
    </form>
@endsection
