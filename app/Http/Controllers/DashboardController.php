<?php

namespace App\Http\Controllers;

use App\Support\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const OBJECTIVES = ['Objective 3', 'Objective 2A', 'Objective 2B', 'NHMRC'];

    public function index(Request $request)
    {
        $lab = Lab::current();
        $key = 'dashboard:' . $lab;

        if ($request->boolean('refresh')) {
            Cache::forget($key);
        }

        $stats = Cache::remember($key, 600, fn () => $this->build($lab));

        return view('dashboard', ['stats' => $stats]);
    }

    private function build(string $lab): array
    {
        // last 12 months, oldest first: 'Y-m' => 'Nov 2025'
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $months[$m->format('Y-m')] = $m->format('M Y');
        }
        $from = now()->startOfMonth()->subMonths(11)->toDateString();

        $perObjective = [];
        $monthly      = [];
        $types        = [];
        $latest       = [];

        foreach (self::OBJECTIVES as $name) {
            $src = fn () => $this->source($name, $lab);   // a fresh query each time

            $perObjective[$name] = (int) $this->safe(
                fn () => DB::query()->fromSub($src(), 'x')->count(), 0
            );

            $byMonth = $this->safe(fn () => DB::query()->fromSub($src(), 'x')
                ->selectRaw("DATE_FORMAT(received_on, '%Y-%m') as ym, COUNT(*) as n")
                ->whereNotNull('received_on')
                ->where('received_on', '>=', $from)
                ->groupBy('ym')
                ->pluck('n', 'ym')->all(), []);

            $monthly[$name] = array_map(fn ($ym) => (int) ($byMonth[$ym] ?? 0), array_keys($months));

            $rows = $this->safe(fn () => DB::query()->fromSub($src(), 'x')
                ->selectRaw('sample_type, COUNT(*) as n')
                ->groupBy('sample_type')->get(), collect());

            foreach ($rows as $r) {
                $label = trim((string) $r->sample_type) ?: 'Unspecified';
                $types[$label] = ($types[$label] ?? 0) + (int) $r->n;
            }

            $rows = $this->safe(fn () => DB::query()->fromSub($src(), 'x')
                ->whereNotNull('received_on')
                ->orderByDesc('received_on')->orderByDesc('received_at')
                ->limit(8)->get(), collect());

            foreach ($rows as $r) {
                $latest[] = [
                    'barcode'   => $r->barcode,
                    'objective' => $name,
                    'type'      => trim((string) $r->sample_type) ?: '-',
                    'on'        => (string) $r->received_on,
                    'at'        => (string) $r->received_at,
                ];
            }
        }

        usort($latest, fn ($a, $b) => [$b['on'], $b['at']] <=> [$a['on'], $a['at']]);
        $latest = array_slice($latest, 0, 8);

        // top 12 sample types across all objectives, the rest grouped as "Other"
        arsort($types);
        $top   = array_slice($types, 0, 12, true);
        $other = array_sum(array_slice($types, 12, null, true));
        if ($other > 0) {
            $top['Other'] = $other;
        }

        $freezer = (int) $this->safe(fn () => DB::table('freezer_in')
            ->where('lab', $lab)->where('flag', 0)->where('out', 0)->count(), 0);

        $dnaTotal = (int) $this->safe(fn () => DB::table('dna_extraction')
            ->where('lab', $lab)->where('flag', 0)->count(), 0);
        $dnaPass = (int) $this->safe(fn () => DB::table('dna_extraction')
            ->where('lab', $lab)->where('flag', 0)->where('qc_status', 'Pass')->count(), 0);

        return [
            'generated_at' => now()->format('d M Y H:i'),
            'kpi' => [
                'total'         => array_sum($perObjective),
                'this_month'    => array_sum(array_column($monthly, 11)),
                'last_month'    => array_sum(array_column($monthly, 10)),
                'freezer'       => $freezer,
                'dna_total'     => $dnaTotal,
                'dna_pass_rate' => $dnaTotal > 0 ? round($dnaPass / $dnaTotal * 100, 1) : null,
            ],
            'objectives' => [
                'labels' => array_keys($perObjective),
                'values' => array_values($perObjective),
            ],
            'monthly' => [
                'labels'   => array_values($months),
                'datasets' => array_map(
                    fn ($n) => ['label' => $n, 'data' => $monthly[$n]],
                    self::OBJECTIVES
                ),
            ],
            'types'  => ['labels' => array_keys($top), 'values' => array_values($top)],
            'latest' => $latest,
        ];
    }

    // Every objective exposes the same four columns: barcode, sample_type, received_on, received_at
    private function source(string $name, string $lab)
    {
        return match ($name) {
            'Objective 3' => DB::table('obj3_sam_rec as a')
                ->leftJoin('ref_sampletype as s', 's.id_sampletype', '=', 'a.id_type')
                ->where('a.lab', $lab)->where('a.flag', 0)
                ->selectRaw('a.barcode_sample as barcode, s.sampletype as sample_type, a.date_receipt as received_on, a.time_receipt as received_at'),

            'Objective 2A' => DB::table('obj2a_receipt_det as d')
                ->join('obj2a_receipt as r', function ($j) {
                    $j->on('r.id_receipt', '=', 'd.id_receipt')->on('r.lab', '=', 'd.lab');
                })
                ->where('d.lab', $lab)->where('d.flag', 0)->where('r.flag', 0)
                ->selectRaw('d.barcode_sample as barcode, r.sample_type as sample_type, r.date_receipt as received_on, NULL as received_at'),

            'Objective 2B' => DB::table('obj2b_receipt as a')
                ->leftJoin('ref_sampletype as s', 's.id_sampletype', '=', 'a.id_type2b')
                ->where('a.lab', $lab)->where('a.flag', 0)
                ->selectRaw('a.barcode_sample as barcode, s.sampletype as sample_type, a.date_arrival as received_on, a.time_arrival as received_at'),

            'NHMRC' => DB::table('nhmrc_receipt as a')
                ->leftJoin('ref_sampletype as s', 's.id_sampletype', '=', 'a.id_type2b')
                ->where('a.lab', $lab)->where('a.flag', 0)
                ->selectRaw('a.barcode_sample as barcode, s.sampletype as sample_type, a.date_arrival as received_on, a.time_arrival as received_at'),
        };
    }

    // One failing query must not break the whole dashboard
    private function safe(callable $fn, mixed $default): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);
            return $default;
        }
    }
}