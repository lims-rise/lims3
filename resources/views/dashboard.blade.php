@extends('layouts.app')
@section('title', \App\Support\Lab::name() . ' Laboratory Data')

@php
    $colors = ['Objective 3' => '#4f8ef7', 'Objective 2A' => '#6f5bd9', 'Objective 2B' => '#35c27d', 'NHMRC' => '#f08a4b'];
    $k = $stats['kpi'];
@endphp

@push('styles')
    <style>
        .hero { background: linear-gradient(120deg, #14213d 0%, #2a4d8f 60%, #3b82c4 100%); color: #fff;
                border-radius: 16px; padding: 1.75rem 2rem; position: relative; overflow: hidden; }
        .hero::after { content: ''; position: absolute; right: -60px; top: -60px; width: 260px; height: 260px;
                       border-radius: 50%; background: rgba(255, 255, 255, .07); }
        .kpi { border: 0; border-radius: 16px; color: #fff; position: relative; overflow: hidden;
               box-shadow: 0 6px 18px rgba(20, 33, 61, .12); }
        .kpi .icon { position: absolute; right: 1rem; top: .8rem; font-size: 2.6rem; opacity: .25; }
        .kpi .value { font-size: 2.1rem; font-weight: 700; line-height: 1.1; }
        .kpi .label { opacity: .92; font-size: .9rem; }
        .kpi .sub { opacity: .8; font-size: .8rem; }
        .k1 { background: linear-gradient(135deg, #4f8ef7, #2d5fd0); }
        .k2 { background: linear-gradient(135deg, #35c27d, #1f9a5e); }
        .k3 { background: linear-gradient(135deg, #38bdf8, #0e7fb8); }
        .k4 { background: linear-gradient(135deg, #a78bfa, #6d4fd6); }
        .panel { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(20, 33, 61, .07); }
        .panel .card-header { background: transparent; border: 0; font-weight: 600; padding: 1.1rem 1.25rem .25rem; }
        .chart-box { position: relative; height: 320px; }
    </style>
@endpush

@section('content')
    {{-- Banner --}}
    <div class="hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index:1">
            <div>
                <div class="text-uppercase small opacity-75">RISE | LIMS3.0</div>
                <h3 class="fw-semibold mb-1">{{ \App\Support\Lab::name() }} Laboratory Data</h3>
                <div class="opacity-75">
                    Welcome back, {{ auth()->user()->full_name }}. Use the country selector on the left to switch laboratory.
                </div>
            </div>
            <div class="text-md-end small">
                <div class="opacity-75">Updated {{ $stats['generated_at'] }}</div>
                <a href="{{ route('dashboard', ['refresh' => 1]) }}" class="btn btn-sm btn-light mt-2">
                    <i class="fa fa-refresh me-1"></i> Refresh
                </a>
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card kpi k1"><div class="card-body">
                <i class="fa fa-flask icon"></i>
                <div class="value" data-count="{{ $k['total'] }}">{{ number_format($k['total']) }}</div>
                <div class="label">Samples received</div>
                <div class="sub">All objectives</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card kpi k2"><div class="card-body">
                <i class="fa fa-calendar icon"></i>
                <div class="value" data-count="{{ $k['this_month'] }}">{{ number_format($k['this_month']) }}</div>
                <div class="label">Received this month</div>
                <div class="sub">Last month: {{ number_format($k['last_month']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card kpi k3"><div class="card-body">
                <i class="fa fa-snowflake-o icon"></i>
                <div class="value" data-count="{{ $k['freezer'] }}">{{ number_format($k['freezer']) }}</div>
                <div class="label">Samples in freezer</div>
                <div class="sub">Currently stored</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card kpi k4"><div class="card-body">
                <i class="fa fa-check-circle icon"></i>
                @if ($k['dna_pass_rate'] !== null)
                    <div class="value" data-count="{{ $k['dna_pass_rate'] }}" data-decimals="1" data-suffix="%">{{ $k['dna_pass_rate'] }}%</div>
                @else
                    <div class="value">-</div>
                @endif
                <div class="label">DNA extraction QC pass</div>
                <div class="sub">{{ number_format($k['dna_total']) }} extractions</div>
            </div></div>
        </div>
    </div>

    {{-- Charts row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card panel h-100">
                <div class="card-header">Samples per objective</div>
                <div class="card-body"><div class="chart-box"><canvas id="chObjectives"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card panel h-100">
                <div class="card-header">Samples received per month (last 12 months)</div>
                <div class="card-body"><div class="chart-box"><canvas id="chMonthly"></canvas></div></div>
            </div>
        </div>
    </div>

    {{-- Charts row 2 --}}
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card panel h-100">
                <div class="card-header">Top sample types (all objectives)</div>
                <div class="card-body"><div class="chart-box"><canvas id="chTypes"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card panel h-100">
                <div class="card-header">Latest receptions</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Barcode</th><th>Objective</th><th>Type</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($stats['latest'] as $row)
                                    <tr>
                                        <td class="text-nowrap fw-semibold">{{ $row['barcode'] }}</td>
                                        <td>
                                            <span class="badge" style="background: {{ $colors[$row['objective']] ?? '#6c757d' }}">{{ $row['objective'] }}</span>
                                        </td>
                                        <td class="text-nowrap">{{ $row['type'] }}</td>
                                        <td class="text-nowrap">{{ $row['on'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No receptions yet for this laboratory.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @include('partials.export-all')
    </div>
@endsection

@push('scripts')
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script> -->
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            const S = @json($stats);
            const COLORS = @json($colors);

            // Animated counters
            document.querySelectorAll('[data-count]').forEach(function (el) {
                const target = parseFloat(el.dataset.count) || 0;
                const dec = parseInt(el.dataset.decimals || '0', 10);
                const suffix = el.dataset.suffix || '';
                const start = performance.now(), duration = 900;
                (function tick(now) {
                    const p = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = (target * eased).toLocaleString(undefined,
                        { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                })(start);
            });

            if (!window.Chart) { return; }   // the chart library could not be loaded

            Chart.defaults.font.family = "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
            Chart.defaults.color = '#5b6478';

            const total = S.objectives.values.reduce(function (a, b) { return a + b; }, 0);

            // Total in the middle of the doughnut
            const centerText = {
                id: 'centerText',
                afterDraw: function (chart) {
                    const a = chart.chartArea, ctx = chart.ctx;
                    const x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#14213d';
                    ctx.font = '700 30px system-ui, sans-serif';
                    ctx.fillText(total.toLocaleString(), x, y - 8);
                    ctx.fillStyle = '#8a93a6';
                    ctx.font = '500 13px system-ui, sans-serif';
                    ctx.fillText('samples', x, y + 18);
                    ctx.restore();
                }
            };

            new Chart(document.getElementById('chObjectives'), {
                type: 'doughnut',
                data: {
                    labels: S.objectives.labels,
                    datasets: [{
                        data: S.objectives.values,
                        backgroundColor: S.objectives.labels.map(function (l) { return COLORS[l]; }),
                        borderWidth: 3, borderColor: '#fff', hoverOffset: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } },
                        tooltip: { callbacks: { label: function (c) {
                            const pct = total ? (c.parsed / total * 100).toFixed(1) : 0;
                            return ' ' + c.label + ': ' + c.parsed.toLocaleString() + ' (' + pct + '%)';
                        } } }
                    }
                },
                plugins: [centerText]
            });

            new Chart(document.getElementById('chMonthly'), {
                type: 'bar',
                data: {
                    labels: S.monthly.labels,
                    datasets: S.monthly.datasets.map(function (d) {
                        return { label: d.label, data: d.data, backgroundColor: COLORS[d.label], borderRadius: 4, borderSkipped: false };
                    })
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f6' } }
                    },
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } }
                }
            });

            new Chart(document.getElementById('chTypes'), {
                type: 'bar',
                data: {
                    labels: S.types.labels,
                    datasets: [{ data: S.types.values, backgroundColor: '#4f8ef7', borderRadius: 6, barThickness: 16 }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f6' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        })();

    </script>
@endpush