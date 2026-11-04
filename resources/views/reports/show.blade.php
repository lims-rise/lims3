@extends('layouts.app')
@section('title', $cfg['title'])

@push('styles')
    <!-- <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
    <link href="{{ asset('css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>#tbl th, #tbl td { white-space: nowrap; }</style>
@endpush

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">{{ $cfg['title'] }}</h5>

            @if ($buttons->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach ($buttons as $b)
                        <a class="btn btn-success btn-sm" href="{{ $b['url'] }}">
                            <i class="fa-solid fa-download me-1"></i> {{ $b['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            @isset($cfg['preview'])
                <hr>

                @isset($cfg['types'])
                    <div class="row mb-3">
                        <label for="rep" class="col-sm-3 col-form-label">{{ $cfg['type_label'] ?? 'Report for sample type' }}</label>
                        <div class="col-sm-4">
                            <select id="rep" class="form-select form-select-sm">
                                <option value="">-- Select sample type --</option>
                                @foreach ($cfg['types'] as $id => $t)
                                    <option value="{{ $id }}">{{ $t['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endisset

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-2">
                        <label for="date1" class="form-label mb-0 small">Date start</label>
                        <input type="date" id="date1" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label for="date2" class="form-label mb-0 small">Date end</label>
                        <input type="date" id="date2" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button id="btn-refresh" class="btn btn-primary btn-sm"><i class="fa fa-refresh me-1"></i> Refresh</button>
                        <a id="btn-export" class="btn btn-success btn-sm" href="#">
                            <i class="fa-regular fa-file-excel me-1"></i> {{ isset($cfg['types']) ? 'Export To CSV' : 'Export To Excel (all sheets)' }}
                        </a>
                    </div>
                </div>

                <table id="tbl" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            @foreach ($cfg['preview']['columns'] as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                </table>
            @endisset
        </div>
    </div>
@endsection

@isset($cfg['preview'])
    @push('scripts')
        <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
        <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
        <!-- <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script> -->
        <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
        <!-- <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script> -->
        <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
        <script>
            $(function () {
                const rowsUrl   = @json(url('m/' . $report . '/rows'));
                const exportUrl = @json(url('m/' . $report . '/export'));
                const hasTypes  = @json(isset($cfg['types']));
                let table = null;

                function filters() {
                    const f = { date1: $('#date1').val(), date2: $('#date2').val() };
                    if (hasTypes) f.rep = $('#rep').val();
                    return f;
                }

                // keep the date range consistent (start <= end)
                $('#date1').on('change', function () {
                    if ($('#date2').val() && $('#date1').val() > $('#date2').val()) $('#date2').val($('#date1').val());
                });
                $('#date2').on('change', function () {
                    if ($('#date1').val() && $('#date2').val() < $('#date1').val()) $('#date1').val($('#date2').val());
                });

                $('#btn-refresh').on('click', function () {
                    if (hasTypes && !$('#rep').val()) { alert('Select a sample type first.'); return; }
                    if (table) { table.ajax.reload(); return; }
                    table = $('#tbl').DataTable({
                        ajax: { url: rowsUrl, data: function () { return filters(); }, dataSrc: 'data' },
                        columns: @json(array_map(fn ($k) => ['data' => $k, 'defaultContent' => ''], array_keys($cfg['preview']['columns']))),
                        pageLength: 10,
                        scrollX: true,
                        order: [],
                        columnDefs: [{ targets: '_all', render: $.fn.dataTable.render.text() }]
                    });
                });

                $('#btn-export').on('click', function (e) {
                    if (hasTypes && !$('#rep').val()) { e.preventDefault(); alert('Select a sample type first.'); return; }
                    this.href = exportUrl + '?' + new URLSearchParams(filters()).toString();
                });
            });
        </script>
    @endpush
@endisset
