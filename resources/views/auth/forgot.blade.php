@extends('layouts.auth')
@section('title', 'Forgot password | LIMS')

@section('card')
    <h3 class="fw-semibold mb-1">Forgot password?</h3>
    <p class="mb-4 opacity-75">Enter the email address of your account and we will send you a link to reset your password.</p>

    @if (session('status'))
        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Send reset link</button>
        <div class="text-center mt-3">
            <a href="{{ route('login') }}">Back to sign in</a>
        </div>
    </form>
@endsection