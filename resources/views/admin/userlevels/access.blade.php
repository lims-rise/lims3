@extends('layouts.app')
@section('title', 'Access: ' . $level->nama_level)

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Access granted for level: <b>{{ $level->nama_level }}</b></h5>
                <a href="{{ route('userlevel.index') }}" class="btn btn-warning btn-sm">
                    <i class="fa fa-times me-1"></i> Close
                </a>
            </div>

            <div class="alert alert-info py-2">
                Tick a row to grant access. Each change is saved immediately.
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover w-auto">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Module Name</th>
                            <th>URL</th>
                            <th class="text-center">Access</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menus as $m)
                            @php
                                $isParent = (int) $m->is_main_menu === 0;
                                $title    = trim(strip_tags(str_ireplace('<br>', ' ', (string) $m->title)));
                            @endphp
                            <tr class="{{ $m->is_aktif === 'y' ? '' : 'text-muted' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="{{ $isParent ? 'fw-bold' : 'ps-4' }}">
                                    {{ $title !== '' ? $title : '(separator)' }}
                                    @if ($m->is_aktif !== 'y') <span class="badge bg-secondary">inactive</span> @endif
                                </td>
                                <td>{{ $m->url }}</td>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input js-access"
                                           data-menu="{{ $m->id_menu }}" @checked(in_array($m->id_menu, $granted))>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('userlevel.index') }}" class="btn btn-warning btn-sm">
                <i class="fa fa-times me-1"></i> Close
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const base = @json(url('m/userlevel/' . $level->id_user_level . '/access'));
            const csrf = @json(csrf_token());

            document.querySelectorAll('.js-access').forEach(function (box) {
                box.addEventListener('change', function () {
                    const wanted = box.checked;
                    box.disabled = true;
                    fetch(base + '/' + box.dataset.menu, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ granted: wanted })
                    }).then(function (r) {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                    }).catch(function () {
                        box.checked = !wanted;   // put it back, the change was not saved
                        alert('The change could not be saved. Please try again.');
                    }).finally(function () {
                        box.disabled = false;
                    });
                });
            });
        })();
    </script>
@endpush
