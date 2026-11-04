<?php

namespace App\Http\Controllers;

use App\Support\Lab;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    public function index(string $module)
    {
        $def     = $this->definition($module);
        $columns = $this->tableColumns($def);
        $keys    = array_keys($columns);
        $offset  = $this->offset($def);

        // DataTables column options; "data" = position in the row array sent by data()
        $jsColumns = [];
        foreach (array_values($columns) as $p => $c) {
            $jsColumns[] = (object) array_filter([
                'data'      => $p,
                'width'     => $c['width'] ?? null,
                'className' => $c['class'] ?? null,
            ], fn ($v) => $v !== null);
        }
        // the "+" icon goes inside the column that holds the parent id
        if (isset($def['detail'])) {
            $idPos = array_search($def['detail']['id_column'], $keys, true);
            if ($idPos !== false) {
                $jsColumns[$idPos]->className = trim(($jsColumns[$idPos]->className ?? '') . ' dt-control');
            }
        }

        $initialOrder = [];
        foreach ($this->defaultOrder($def) as [$key, $dir]) {
            $i = array_search($key, $keys, true);
            if ($i !== false) {
                $initialOrder[] = [$i + $offset, $dir];
            }
        }

        return view('module.index', [
            'module'       => $module,
            'def'          => $def,
            'columns'      => $columns,
            'jsColumns'    => $jsColumns,
            'initialOrder' => $initialOrder,
            'offset'       => $offset,
            'detailUrl'    => isset($def['detail']) ? route('module.detail', [$module, '__ID__']) : null,
            'detailIdIdx'  => isset($def['detail']) ? array_search($def['detail']['id_column'], $keys, true) : null,
            'exportLabel'  => ($def['export_format'] ?? 'csv') === 'xlsx' ? 'Export Excel' : 'Export CSV',
            'extraExports' => collect($def['exports'] ?? [])->map(fn ($e, $k) => [
                'label' => $e['button'] ?? $k,
                'url'   => route('module.export.variant', [$module, $k]),
            ])->values()->all(),
        ]);
    }

    public function data(Request $request, string $module)
    {
        $def     = $this->definition($module);
        $columns = $this->tableColumns($def);

        $total = $this->applyScope(DB::table($def['table'] . ' as t'), $def)->count();

        $query = $this->baseQuery($def, $columns);
        $this->applySearch($query, $columns, (string) $request->input('search.value', ''));
        $filtered = (clone $query)->count();

        $requested = $this->shiftOrder((array) $request->input('order', []), $this->offset($def));
        $this->applyOrder($query, $def, $columns, $requested);

        $length = min(max((int) $request->input('length', 25), 1), 500);
        $rows   = $query->offset(max(0, (int) $request->input('start', 0)))->limit($length)->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows->map(fn ($r) => array_values((array) $r))->all(),
        ]);
    }

    // Child rows: one or more sections (e.g. Endetec and IDEXX) under a parent row
    public function detail(string $module, string $id)
    {
        $def = $this->definition($module);
        abort_unless(isset($def['detail']), 404);

        $sections = [];
        foreach ($def['detail']['sections'] as $s) {
            $sections[] = isset($s['pivot'])
                ? $this->pivotSection($def, $s, $id)
                : $this->joinSection($def, $s, $id);
        }

        return response()->json(['sections' => $sections]);
    }


    private function joinSection(array $def, array $s, string $id): array
    {
        $q = DB::table($def['table'] . ' as t')
            ->join($s['table'] . ' as d', $s['key'], '=', $s['parent'])
            ->where($s['match'] ?? $s['parent'], $id);
 
        $this->applyScope($q, $def);   // the parent must be in the selected lab and not deleted
 
        // the child too: ids/barcodes can repeat across labs
        $labCol  = array_key_exists('lab_column', $s) ? $s['lab_column'] : 'd.lab';
        $flagCol = array_key_exists('flag_column', $s) ? $s['flag_column'] : 'd.flag';
        if ($labCol) {
            $q->where($labCol, Lab::current());
        }
        if ($flagCol) {
            $q->where($flagCol, 0);
        }
 
        $selects = [];
        foreach ($s['columns'] as $alias => $col) {
            $selects[] = DB::raw($col['sql'] . ' as `' . $alias . '`');
        }
        $q->select($selects)->distinct();   // a barcode can have several parent rows
 
        if (! empty($s['order'])) {
            $q->orderByRaw($s['order']);
        }
 
        return [
            'title'  => $s['title'] ?? null,
            'labels' => array_values(array_column($s['columns'], 'label')),
            'rows'   => $q->get()->map(fn ($r) => array_values((array) $r))->all(),
        ];
    }


    // Turns species columns (e.g. aedes_aegypt_male / aedes_aegypt_female) into rows: name, male, female, total
    private function pivotSection(array $def, array $s, string $id): array
    {
        $cols = [];
        foreach ($s['pivot'] as [, $male, $female]) {
            $cols[] = 't.' . $male;
            if ($female) {
                $cols[] = 't.' . $female;
            }
        }

        $q = DB::table($def['table'] . ' as t')->where($s['key'], $id);
        $this->applyScope($q, $def);
        $row = $q->select(array_values(array_unique($cols)))->first();

        $rows = [];
        if ($row) {
            foreach ($s['pivot'] as [$label, $male, $female]) {
                $m = (int) ($row->$male ?? 0);
                if ($female) {
                    $f = (int) ($row->$female ?? 0);
                    if ($m + $f > 0) {                 // skip species that were not found
                        $rows[] = [$label, $m, $f, $m + $f];
                    }
                } elseif ($m > 0) {                    // single counts such as larvae
                    $rows[] = [$label, '-', '-', $m];
                }
            }
        }

        return [
            'title'  => $s['title'] ?? null,
            'labels' => $s['labels'] ?? ['Mosquito Name', 'Male', 'Female', 'Total'],
            'rows'   => $rows,
        ];
    }

    public function export(Request $request, string $module, ?string $variant = null)
    {
        $def = $this->definition($module);

        if (($def['export_format'] ?? 'csv') === 'xlsx') {
            return $this->exportXlsx($module, $def);
        }

        // an extra export button (e.g. "conversion") has its own definition under 'exports'
        $exp = $variant ? ($def['exports'][$variant] ?? null) : ($def['export'] ?? null);
        abort_if($variant !== null && $exp === null, 404);

        $override = $exp !== null;                  // a different source for the CSV
        $src      = array_merge($def, $exp ?? []);
        $visible  = $this->tableColumns($src);      // search and sort follow what the table shows
        $all      = $src['columns'];                // the CSV also includes export_only columns

        $requested = [];
        foreach ((array) (json_decode((string) $request->query('order', '[]'), true) ?: []) as $o) {
            if (is_array($o)) {
                $requested[] = ['column' => (int) ($o[0] ?? -1), 'dir' => $o[1] ?? 'asc'];
            }
        }
        $requested = $this->shiftOrder($requested, $this->offset($def));

        $query = $this->baseQuery($src, $all);
        $this->applySearch($query, $visible, (string) $request->query('search', ''));
        $this->applyOrder($query, $src, $visible, $override ? [] : $requested);

        $base     = $variant ? ($exp['filename'] ?? $module . '_' . $variant) : $module;
        $filename = $base . '_' . Lab::name() . '_' . now()->format('Ymd') . '.csv';
        $headers  = array_map(fn ($c) => $c['csv'] ?? $c['label'], array_values($all));

        return response()->streamDownload(function () use ($query, $headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads it correctly
            fputcsv($out, $headers, ',', '"', '');

            foreach ($query->cursor() as $row) {
                fputcsv($out, array_map([$this, 'csvSafe'], array_values((array) $row)), ',', '"', '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Excel export with one worksheet per entry of $def['sheets'] (each sheet has its own table, joins and columns)
    private function exportXlsx(string $module, array $def)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($def['sheets'] as $sheet) {
            $columns = $this->normalize($sheet['columns'], 't');
            $src     = array_merge($def, $sheet, ['columns' => $columns]);

            $query = $this->baseQuery($src, $columns);
            $this->applyOrder($query, $src, $columns, []);

            $ws = $spreadsheet->createSheet();
            $ws->setTitle(substr($sheet['name'], 0, 31));
            $ws->fromArray([array_map(fn ($c) => $c['csv'] ?? $c['label'], array_values($columns))], null, 'A1');

            $r = 2;
            foreach ($query->cursor() as $row) {
                $ws->fromArray([array_map([$this, 'csvSafe'], array_values((array) $row))], null, 'A' . $r++);
            }
        }
        $spreadsheet->setActiveSheetIndex(0);

        $filename = $module . '_' . Lab::name() . '_' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ---------------------------------------------------------------- helpers

    private function definition(string $module): array
    {
        $def = config("modules.$module");
        abort_unless($def, 404);

        // Access is checked on the server, not just by hiding the sidebar link
        $allowed = DB::table('tbl_menu as m')
            ->join('tbl_hak_akses as h', 'h.id_menu', '=', 'm.id_menu')
            ->where('m.url', $module)
            ->where('m.is_aktif', 'y')
            ->where('h.id_user_level', auth()->user()->id_user_level)
            ->exists();
        abort_unless($allowed, 403);

        // "sql" is optional: it defaults to <alias>.<column name>
        $def['columns'] = $this->normalize($def['columns'], 't');
        foreach ($def['exports'] ?? [] as $k => $e) {
            $def['exports'][$k]['columns'] = $this->normalize($e['columns'], 't');
        }
        if (isset($def['export']['columns'])) {
           $def['export']['columns'] = $this->normalize($def['export']['columns'], 't');
}
        if (isset($def['detail'])) {
            $d        = $def['detail'];
            $sections = $d['sections'] ?? [$d];   // a single detail is just one section
            foreach ($sections as $i => $s) {
                if (isset($s['columns'])) {
                    $sections[$i]['columns'] = $this->normalize($s['columns'], 'd');
                }
            }
            $def['detail'] = ['id_column' => $d['id_column'], 'sections' => $sections];
        }
        return $def;
    }

    private function normalize(array $columns, string $alias): array
    {
        $lab = Lab::current();

        foreach ($columns as $key => $col) {
            // {lab} is replaced by the selected lab (1 or 2), so one SQL text can serve both labs
            $sql = $col['sql'] ?? "$alias.$key";
            $columns[$key]['sql'] = str_replace('{lab}', (string) (int) $lab, $sql);

            // some headers depend on the selected laboratory
            if (isset($col['labels'][$lab])) {
                $columns[$key]['label'] = $col['labels'][$lab];
            }
            if (isset($col['csvs'][$lab])) {
                $columns[$key]['csv'] = $col['csvs'][$lab];
            }
        }
        return $columns;
    }

    // modules with child rows get an extra "+" column at position 0
    private function offset(array $def): int
    {
        return 0;
    }

    private function tableColumns(array $def): array
    {
        return array_filter($def['columns'], fn ($c) => empty($c['export_only']));
    }

    // Global rule for every module: lab = selected country AND flag = 0
    private function applyScope(Builder $q, array $def): Builder
    {
        $labCol  = array_key_exists('lab_column', $def) ? $def['lab_column'] : 't.lab';
        $flagCol = array_key_exists('flag_column', $def) ? $def['flag_column'] : 't.flag';

        if ($labCol) {
            $q->where($labCol, Lab::current());
        }
        if ($flagCol) {
            $q->where($flagCol, 0);
        }

        return $q;
    }

    private function baseQuery(array $def, array $columns): Builder
    {
        $q = DB::table($def['table'] . ' as t');

        foreach ($def['joins'] ?? [] as $j) {
            [$table, $left, $right] = $j;
            $extra = $j[3] ?? [];   // optional extra ON conditions, e.g. ['d.lab = t.lab']

            // a table that starts with "(" is a raw derived table; {lab} becomes the selected lab (1 or 2)
            if (str_starts_with($table, '(')) {
                $table = DB::raw(str_replace('{lab}', (string) (int) Lab::current(), $table));
            }

            $q->leftJoin($table, function ($join) use ($left, $right, $extra) {
                $join->on($left, '=', $right);
                foreach ($extra as $cond) {
                    $join->whereRaw($cond);
                }
            });
        }

        $this->applyScope($q, $def);

        foreach ($def['where'] ?? [] as $cond) {
            $q->whereRaw($cond);   // extra fixed conditions from the module config
        }

        $selects = [];
        foreach ($columns as $alias => $col) {
            $selects[] = DB::raw($col['sql'] . ' as `' . $alias . '`');
        }

        return $q->select($selects);
    }

    private function applySearch(Builder $q, array $columns, string $term): void
    {
        $term = trim($term);
        if ($term === '') {
            return;
        }
        $like = '%' . addcslashes($term, '%_\\') . '%';

        $q->where(function ($w) use ($columns, $like) {
            foreach ($columns as $col) {
                $w->orWhereRaw($col['sql'] . ' LIKE ?', [$like]);
            }
        });
    }

    // DataTables sends column positions that include the "+" column; convert them to our positions
    private function shiftOrder(array $order, int $offset): array
    {
        $out = [];
        foreach ($order as $o) {
            if (! is_array($o)) {
                continue;
            }
            $col = (int) ($o['column'] ?? -1) - $offset;
            if ($col >= 0) {
                $out[] = ['column' => $col, 'dir' => $o['dir'] ?? 'asc'];
            }
        }
        return $out;
    }

    // Order requested by the user first, then the module's default order as tie-breakers
    private function applyOrder(Builder $q, array $def, array $columns, array $requested): void
    {
        $keys = array_keys($columns);
        $used = [];

        foreach ($requested as $o) {
            $key = $keys[(int) ($o['column'] ?? -1)] ?? null;
            if ($key === null || isset($used[$key])) {
                continue;
            }
            $used[$key] = true;
            $dir = strtolower((string) ($o['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $q->orderByRaw($columns[$key]['sql'] . ' ' . $dir);
        }

        foreach ($this->defaultOrder($def) as [$key, $dir]) {
            if (! isset($used[$key])) {
                $q->orderByRaw($def['columns'][$key]['sql'] . ' ' . $dir);
            }
        }
    }

    private function defaultOrder(array $def): array
    {
        $order = $def['order'] ?? [];
        if ($order && ! is_array($order[0])) {
            $order = [$order];   // allows the short form ['date_receipt', 'desc']
        }

        $out = [];
        foreach ($order as $o) {
            $key = $o[0] ?? null;
            if ($key && isset($def['columns'][$key])) {
                $out[] = [$key, ($o[1] ?? 'asc') === 'desc' ? 'desc' : 'asc'];
            }
        }
        return $out;
    }

    // Prevent spreadsheet formula injection when the CSV is opened in Excel
    private function csvSafe(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && ! is_numeric($value) && str_contains("=+-@\t\r", $value[0])) {
            return "'" . $value;
        }
        return $value;
    }
}