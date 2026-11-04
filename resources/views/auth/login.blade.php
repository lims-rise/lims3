@extends('layouts.auth')
@section('title', 'Sign in | LIMS')

@section('card')
    <h3 class="fw-semibold mb-1">LIMS 3.0</h3>
    <p class="mb-4 opacity-75">Laboratory Information Management System</p>

    @if (session('status'))
        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
        <div class="text-center mt-3">
            <a href="{{ route('password.request') }}">Forgot password?</a>
        </div>
    </form>
@endsection