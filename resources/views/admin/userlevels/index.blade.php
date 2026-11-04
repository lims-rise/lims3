@extends('layouts.app')
@section('title', 'User Level')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
                @if (session($key))
                    <div class="alert alert-{{ $type }} alert-dismissible fade show">
                        {{ session($key) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">User Level</h5>
                <a href="{{ route('userlevel.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Add level
                </a>
            </div>

            <table class="table table-striped table-hover w-auto">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Level Name</th>
                        <th>Users</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($levels as $l)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $l->nama_level }}</td>
                            <td>{{ $l->users }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('userlevel.access', $l->id_user_level) }}" class="btn btn-warning btn-sm" title="Access">
                                    <i class="fa fa-key"></i>
                                </a>
                                <a href="{{ route('userlevel.edit', $l->id_user_level) }}" class="btn btn-info btn-sm" title="Edit">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <form method="POST" action="{{ route('userlevel.destroy', $l->id_user_level) }}"
                                      class="d-inline js-delete" data-name="{{ $l->nama_level }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-eraser"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.js-delete').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                if (!confirm('Delete level "' + f.dataset.name + '"?')) e.preventDefault();
            });
        });
    </script>
@endpush
