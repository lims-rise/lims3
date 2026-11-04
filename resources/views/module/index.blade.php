@extends('layouts.app')
@section('title', $def['title'])

@push('styles')
    <!-- <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
    <link href="{{ asset('css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        #tbl th, #tbl td { white-space: nowrap; }
        .col-trunc { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }
        td.dt-control { cursor: pointer; }
        td.dt-control::before { font-family: FontAwesome; content: "\f055"; color: #198754; margin-right: .5rem; }
        tr.shown td.dt-control::before { content: "\f056"; color: #dc3545; }
    </style>
@endpush

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">{{ $def['title'] }}</h5>
                <div class="d-flex gap-2">
                    @foreach ($extraExports as $x)
                        <a class="btn btn-outline-success btn-sm" href="{{ $x['url'] }}">
                            <i class="fa fa-download me-1"></i> {{ $x['label'] }}
                        </a>
                    @endforeach
                    <a id="btn-export" class="btn btn-success btn-sm" href="{{ route('module.export', $module) }}">
                        <i class="fa fa-download me-1"></i> {{ $exportLabel }}
                    </a>
                </div>
            </div>

            <table id="tbl" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        @if ($offset) <th></th> @endif
                        @foreach ($columns as $col)
                            <th>{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
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
            const exportUrl   = @json(route('module.export', $module));
            const detailUrl   = @json($detailUrl);
            const detailIdIdx = @json($detailIdIdx);

            const table = $('#tbl').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('module.data', $module)),
                columns: @json($jsColumns),
                order: @json($initialOrder),
                pageLength: 10,
                scrollX: true,
                columnDefs: [{
                    targets: '_all',
                    defaultContent: '',
                    render: $.fn.dataTable.render.text()   // escapes HTML from the database
                }],
                // show the full text on hover for truncated cells
                createdRow: function (row) {
                    $('td.col-trunc', row).each(function () { this.title = this.textContent; });
                }
            });

            // Export follows the current search and sort
            $('#btn-export').on('click', function () {
                this.href = exportUrl + '?' + new URLSearchParams({
                    search: table.search(),
                    order: JSON.stringify(table.order())
                });
            });

            // Child rows (only for modules that define "detail")
            if (detailUrl) {
                $('#tbl tbody').on('click', 'td.dt-control', function () {
                    const tr  = $(this).closest('tr');
                    const row = table.row(tr);

                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown');
                        return;
                    }

                    const id = row.data()[detailIdIdx];
                    $.getJSON(detailUrl.replace('__ID__', encodeURIComponent(id)), function (res) {
                        const $wrap = $('<div class="p-2"></div>');

                        res.sections.forEach(function (sec) {
                            if (sec.title) {
                                $wrap.append($('<div class="fw-semibold text-uppercase small text-muted mt-2 mb-1"></div>').text(sec.title));
                            }

                            const $head = $('<tr></tr>');
                            sec.labels.forEach(function (l) { $head.append($('<th></th>').text(l)); });

                            const $body = $('<tbody></tbody>');
                            if (!sec.rows.length) {
                                $body.append($('<tr></tr>').append(
                                    $('<td class="text-muted"></td>').attr('colspan', sec.labels.length).text('No data')
                                ));
                            }
                            sec.rows.forEach(function (r) {
                                const $r = $('<tr></tr>');
                                r.forEach(function (v) { $r.append($('<td></td>').text(v ?? '')); });
                                $body.append($r);
                            });

                            $wrap.append($('<div class="table-responsive"></div>').append(
                                $('<table class="table table-sm table-bordered mb-0 w-auto"></table>')
                                    .append($('<thead class="table-light"></thead>').append($head))
                                    .append($body)
                            ));
                        });

                        row.child($wrap).show();
                        tr.addClass('shown');
                    });

                });
            }
        });
    </script>
@endpush