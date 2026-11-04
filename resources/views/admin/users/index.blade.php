@extends('layouts.app')
@section('title', 'User Settings')

@push('styles')
    <!-- <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
    <link href="{{ asset('css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endpush

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
                <h5 class="mb-0">User Settings</h5>
                <a href="{{ route('user.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> New User
                </a>
            </div>

            <table id="tbl" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>Lab</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $u->full_name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->nama_level }}</td>
                            <td>{{ $u->lab == '1' ? 'Indonesia' : ($u->lab == '2' ? 'Fiji' : '-') }}</td>
                            <td>
                                @if ($u->is_aktif === 'y')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('user.edit', $u->id_users) }}" class="btn btn-info btn-sm" title="Edit">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                @if ($u->id_users != auth()->user()->id_users)
                                    <form method="POST" action="{{ route('user.destroy', $u->id_users) }}"
                                          class="d-inline js-delete" data-name="{{ $u->full_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-eraser"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <!-- <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script> -->
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <!-- <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script> -->
    <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        $(function () {
            $('#tbl').DataTable({
                order: [[1, 'asc']],
                columnDefs: [{ orderable: false, targets: -1 }]
            });
            $(document).on('submit', '.js-delete', function () {
                return confirm('Delete user "' + $(this).data('name') + '"? This cannot be undone.');
            });
        });
    </script>
@endpush
