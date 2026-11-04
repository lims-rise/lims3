@extends('layouts.app')
@section('title', $level ? 'Edit Level' : 'New Level')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">{{ $level ? 'Edit Level' : 'New Level' }}</h5>

            <form method="POST"
                  action="{{ $level ? route('userlevel.update', $level->id_user_level) : route('userlevel.store') }}"
                  style="max-width: 480px">
                @csrf
                @if ($level) @method('PUT') @endif

                <div class="mb-4">
                    <label for="nama_level" class="form-label">Level name</label>
                    <input type="text" id="nama_level" name="nama_level"
                           class="form-control @error('nama_level') is-invalid @enderror"
                           value="{{ old('nama_level', $level?->nama_level) }}" required>
                    @error('nama_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-sd-card me-1"></i> {{ $level ? 'Update' : 'Create' }}
                </button>
                <a href="{{ route('userlevel.index') }}" class="btn btn-warning">
                    <i class="fa fa-sign-out me-1"></i> Close
                </a>
            </form>
        </div>
    </div>
@endsection
