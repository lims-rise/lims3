@extends('layouts.app')
@section('title', 'Consumables - Report')

@push('styles')
    <!-- <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
    <link href="{{ asset('css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>#tbl th, #tbl td { white-space: nowrap; }</style>
@endpush

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Consumables - Report</h5>

            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-2">
                    <label for="date1" class="form-label mb-0 small">Date from</label>
                    <input type="date" id="date1" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label for="date2" class="form-label mb-0 small">Date to</label>
                    <input type="date" id="date2" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label for="objective" class="form-label mb-0 small">Objective</label>
                    <select id="objective" class="form-select form-select-sm">
                        <option value="all">All objectives</option>
                        @foreach ($objectives as $o)
                            <option value="{{ $o->id_objective }}">{{ $o->objective }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="stock" class="form-label mb-0 small">Product</label>
                    <select id="stock" class="form-select form-select-sm" disabled>
                        <option value="">All products</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button id="btn-show" class="btn btn-primary btn-sm"><i class="fa fa-search me-1"></i> Show</button>
                    <a id="btn-export" class="btn btn-success btn-sm" href="#"><i class="fa fa-download me-1"></i> Excel</a>
                </div>
            </div>

            <table id="tbl" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
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
            const rowsUrl     = @json(url('m/consumables_report/rows'));
            const productsUrl = @json(url('m/consumables_report/products'));
            const exportUrl   = @json(url('m/consumables_report/export'));

            function filters() {
                return {
                    date1: $('#date1').val(),
                    date2: $('#date2').val(),
                    objective: $('#objective').val(),
                    stock: $('#stock').val()
                };
            }

            const table = $('#tbl').DataTable({
                ajax: { url: rowsUrl, data: function () { return filters(); }, dataSrc: 'data' },
                columns: @json(array_map(fn ($k) => ['data' => $k, 'defaultContent' => ''], array_keys($columns))),
                pageLength: 10,
                scrollX: true,
                order: [],
                columnDefs: [{ targets: '_all', render: $.fn.dataTable.render.text() }]
            });

            // products depend on the objective
            $('#objective').on('change', function () {
                const $stock = $('#stock').empty().append('<option value="">All products</option>');
                const obj = $(this).val();
                if (obj === 'all') { $stock.prop('disabled', true); return; }
                $.getJSON(productsUrl, { objective: obj }, function (rows) {
                    rows.forEach(function (r) {
                        $stock.append($('<option></option>').val(r.id_stock).text(r.product_name));
                    });
                    $stock.prop('disabled', false);
                });
            });

            $('#btn-show').on('click', function () { table.ajax.reload(); });

            // the Excel file follows the current filters
            $('#btn-export').on('click', function () {
                this.href = exportUrl + '?' + new URLSearchParams(filters()).toString();
            });
        });
    </script>
@endpush
