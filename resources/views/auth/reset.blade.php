@extends('layouts.auth')
@section('title', 'Reset password | LIMS')

@section('card')
    <h3 class="fw-semibold mb-1">Reset password</h3>
    <p class="mb-4 opacity-75">Choose a new password: at least 8 characters, with letters and numbers.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label" for="password">New password</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror" required autofocus>
            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm new password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Reset password</button>
    </form>
@endsection