@extends('layouts.app')
@section('title', $user ? 'Edit User' : 'New User')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">{{ $user ? 'Edit User' : 'New User' }}</h5>

            <form method="POST"
                  action="{{ $user ? route('user.update', $user->id_users) : route('user.store') }}"
                  enctype="multipart/form-data" style="max-width: 640px">
                @csrf
                @if ($user) @method('PUT') @endif

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name', $user?->full_name) }}" required>
                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email (used to log in)</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user?->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="{{ $user ? 'Leave blank to keep the current password' : 'At least 8 characters' }}"
                           {{ $user ? '' : 'required' }}>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_user_level" class="form-label">User Level</label>
                        <select id="id_user_level" name="id_user_level"
                                class="form-select @error('id_user_level') is-invalid @enderror" required>
                            <option value="">- select -</option>
                            @foreach ($levels as $l)
                                <option value="{{ $l->id_user_level }}"
                                    @selected(old('id_user_level', $user?->id_user_level) == $l->id_user_level)>
                                    {{ $l->nama_level }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_user_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="lab" class="form-label">Lab</label>
                        <select id="lab" name="lab" class="form-select @error('lab') is-invalid @enderror" required>
                            <option value="1" @selected(old('lab', $user?->lab ?? '1') == '1')>Indonesia</option>
                            <option value="2" @selected(old('lab', $user?->lab ?? '1') == '2')>Fiji</option>
                        </select>
                        @error('lab') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="is_aktif" class="form-label">Status</label>
                        <select id="is_aktif" name="is_aktif" class="form-select @error('is_aktif') is-invalid @enderror" required>
                            <option value="y" @selected(old('is_aktif', $user?->is_aktif ?? 'y') === 'y')>Active</option>
                            <option value="n" @selected(old('is_aktif', $user?->is_aktif ?? 'y') === 'n')>Inactive</option>
                        </select>
                        @error('is_aktif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="images" class="form-label">Profile Picture</label>
                    <input type="file" id="images" name="images" accept="image/png,image/jpeg,image/gif"
                           class="form-control @error('images') is-invalid @enderror">
                    @if ($user && $user->images)
                        <div class="form-text">Current file: {{ $user->images }} (choose a new file to replace it)</div>
                    @endif
                    @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-sd-card me-1"></i> {{ $user ? 'Update' : 'Create' }}
                </button>
                <a href="{{ route('user.index') }}" class="btn btn-warning">
                    <i class="fa fa-sign-out me-1"></i> Close
                </a>
            </form>
        </div>
    </div>
@endsection
