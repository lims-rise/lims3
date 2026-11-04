<?php

namespace App\Http\Controllers;

use App\Support\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * One controller for every page under Reports (rep_o3, rep_o2a, rep_o2b, rep_nhmrc, rep_frez, rep_se).
 * What each page shows and exports is defined in config/reports.php.
 *
 *  - 'sheets' reports: one Excel file with a sheet per query (date range)
 *  - 'types'  reports: one CSV for the chosen sample type (date range)
 *  - 'buttons': links to the export of the individual modules
 */
class ReportController extends Controller
{
    public function index(string $report)
    {
        $cfg = $this->cfg($report);

        $buttons = collect($cfg['buttons'] ?? [])
            ->filter(fn ($b) => config('modules.' . $b['module']) !== null)   // only modules that exist
            ->map(function ($b) {
                $b['url'] = isset($b['variant'])
                    ? route('module.export.variant', [$b['module'], $b['variant']])
                    : route('module.export', $b['module']);

                return $b;
            })->values();

        return view('reports.show', compact('cfg', 'report', 'buttons'));
    }

    /** Preview table (JSON, DataTables paginates and searches it in the browser). */
    public function rows(Request $request, string $report)
    {
        $cfg = $this->cfg($report);
        abort_unless(isset($cfg['preview']), 404);

        [$d1, $d2] = $this->dates($request);
        $sql  = $cfg['preview']['sql'];
        $vars = ['d1' => $d1, 'd2' => $d2];

        if (isset($cfg['types'])) {
            $rep = (string) $request->query('rep', '');
            if ($rep === '') {
                return response()->json(['data' => []]);
            }
            $type = $this->type($cfg, $rep);
            $sql  = str_replace('{type_where}', $type['preview_where'], $sql);
            $vars['rep'] = $rep;
        }

        return response()->json(['data' => $this->run($sql, $vars)]);
    }

    public function export(Request $request, string $report)
    {
        $cfg = $this->cfg($report);
        [$d1, $d2] = $this->dates($request);

        if (isset($cfg['sheets'])) {
            return $this->exportSheets($cfg, ['d1' => $d1, 'd2' => $d2]);
        }

        if (isset($cfg['types'])) {
            $rep = (string) $request->query('rep', '');
            abort_if($rep === '', 422, 'Select a sample type first.');

            return $this->exportType($cfg, $this->type($cfg, $rep), $rep, ['d1' => $d1, 'd2' => $d2, 'rep' => $rep]);
        }

        abort(404);
    }

    // ------------------------------------------------------------------ exports

    private function exportSheets(array $cfg, array $vars)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($cfg['sheets'] as $sheet) {
            $rows = $this->run($sheet['sql'], $vars);
            $ws   = $spreadsheet->createSheet();
            $ws->setTitle(mb_substr($sheet['name'], 0, 31));

            foreach ($sheet['columns'] as $c => $header) {
                $ws->setCellValue([$c + 1, 1], $header);
            }
            $r = 2;
            foreach ($rows as $row) {
                foreach ($sheet['columns'] as $c => $header) {
                    $this->put($ws, $c + 1, $r, $row->{$header} ?? null);
                }
                $r++;
            }
        }
        $spreadsheet->setActiveSheetIndex(0);

        $name = ($cfg['filename'] ?? 'Report') . '_' . date('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function exportType(array $cfg, array $type, string $rep, array $vars)
    {
        $query = $cfg['queries'][$type['query']];
        $sql   = str_replace('{where_rep}', $type['where_rep'] ?? '', $query['sql']);
        $rows  = $this->run($sql, $vars);

        $headers = [];
        $props   = [];
        foreach ($query['columns'] as [$header, $prop]) {
            $headers[] = $this->header($header, $rep);
            $props[]   = $prop;
        }

        $name = $type['filename'] . '_' . date('Ymd') . '.csv';

        return response()->streamDownload(function () use ($headers, $props, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers, ',', '"', '');
            foreach ($rows as $row) {
                $line = [];
                foreach ($props as $p) {
                    $line[] = $this->csvSafe($this->clean($row->{$p} ?? null));
                }
                fputcsv($out, $line, ',', '"', '');
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------ helpers

    private function cfg(string $report): array
    {
        $cfg = config('reports.' . $report);
        abort_unless(is_array($cfg), 404);

        return $cfg;
    }

    private function type(array $cfg, string $rep): array
    {
        abort_unless(isset($cfg['types'][$rep]), 422, 'Unknown sample type.');

        return $cfg['types'][$rep];
    }

    /** @return array{0:string,1:string} date from, date to (open range when empty) */
    private function dates(Request $request): array
    {
        $v = $request->validate([
            'date1' => ['nullable', 'date'],
            'date2' => ['nullable', 'date'],
            'rep'   => ['nullable', 'string', 'max:10'],
        ]);

        return [$v['date1'] ?? '1900-01-01', $v['date2'] ?? date('Y-m-d')];
    }

    /**
     * Runs a query written with {d1} {d2} {lab} {rep} placeholders; every placeholder becomes
     * a PDO binding (values are never concatenated into the SQL).
     */
    private function run(string $sql, array $vars): array
    {
        $bindings = [];
        $sql = preg_replace_callback('/\{(d1|d2|lab|rep)\}/', function ($m) use (&$bindings, $vars) {
            $bindings[] = $m[1] === 'lab' ? Lab::current() : $vars[$m[1]];

            return '?';
        }, $sql);

        return DB::select($sql, $bindings);
    }

    /** Header that depends on the lab (lab1) or on the report type (rep6x2); '*' is the default. */
    private function header($header, string $rep): string
    {
        if (! is_array($header)) {
            return (string) $header;
        }
        foreach ($header as $cond => $text) {
            if ($cond === 'lab1' && (string) Lab::current() === '1') {
                return $text;
            }
            if (str_starts_with($cond, 'rep') && substr($cond, 3) === $rep) {
                return $text;
            }
        }

        return (string) $header['*'];
    }

    /** Same as the old cleanEnter(): line breaks inside a cell become a space. */
    private function clean($value)
    {
        return is_string($value) ? preg_replace('/[\r\n]+/', ' ', $value) : $value;
    }

    /** Stops spreadsheet formulas coming from text (=, @, or +/- followed by text). */
    private function csvSafe($value)
    {
        if (is_string($value) && $value !== '' && strpbrk($value[0], "=+-@\t") !== false && ! is_numeric($value)) {
            return "'" . $value;
        }

        return $value;
    }

    private function put(Worksheet $ws, int $col, int $row, $value): void
    {
        if ($value === null) {
            return;
        }
        if (is_string($value) && str_starts_with($value, '=')) {
            $ws->setCellValueExplicit([$col, $row], $value, DataType::TYPE_STRING);

            return;
        }
        $ws->setCellValue([$col, $row], $value);
    }
}
