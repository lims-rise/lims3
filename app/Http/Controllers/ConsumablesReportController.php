<?php

namespace App\Http\Controllers;

use App\Support\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Lab Consumables - Report: stock-take rows filtered by collection date, objective and product.
 * The page loads the matching rows as JSON and DataTables pages/searches them in the browser.
 */
class ConsumablesReportController extends Controller
{
    /** key => column, in the order shown in the table and in the Excel file */
    private const COLUMNS = [
        'objective'         => 'Objective',
        'product_name'      => 'Product Name',
        'quantity_take'     => 'Quantity Used',
        'closed_container'  => 'Closed Container',
        'unit_measure_lab'  => 'Unit Measure Lab',
        'quantity_per_unit' => 'Quantity Per Unit',
        'loose_items'       => 'Loose Items',
        'total_quantity'    => 'Total Quantity',
        'unit_of_measure'   => 'Unit of Measure',
        'date_collected'    => 'Date Collected',
        'expired_date'      => 'Expired Date',
        'comments'          => 'Comments',
    ];

    public function index()
    {
        $objectives = DB::table('ref_objective')
            ->where('lab', Lab::current())->where('flag', 0)
            ->orderBy('objective')->get(['id_objective', 'objective']);

        return view('consumables.report', ['objectives' => $objectives, 'columns' => self::COLUMNS]);
    }

    /** Products that belong to an objective (dependent dropdown). */
    public function products(Request $request)
    {
        $objective = (string) $request->query('objective', '');

        $rows = DB::table('ref_consumables as rc')
            ->join('consumables_stock as cs', function ($j) {
                $j->on('cs.id_stock', '=', 'rc.id_stock')->on('cs.lab', '=', 'rc.lab');
            })
            ->where('rc.lab', Lab::current())
            ->where('rc.flag', 0)
            ->where('cs.flag', 0)
            ->where('rc.id_objective', $objective)
            ->orderBy('cs.product_name')
            ->get(['cs.id_stock', 'cs.product_name']);

        return response()->json($rows);
    }

    public function rows(Request $request)
    {
        $rows = $this->query($request)->get()->map(fn ($r) => (array) $r)->all();

        return response()->json(['data' => $rows]);
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock Take Report');

        $col = 1;
        foreach (array_keys(self::COLUMNS) as $key) {
            $sheet->setCellValue([$col++, 1], $key);   // same header names as the old export
        }
        $r = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach (array_keys(self::COLUMNS) as $key) {
                $sheet->setCellValue([$col++, $r], $this->safe($row->{$key}));
            }
            $r++;
        }

        $name = 'Consumables_report_' . date('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ------------------------------------------------------------------ helpers

    private function query(Request $request)
    {
        $v = $request->validate([
            'date1'     => ['nullable', 'date'],
            'date2'     => ['nullable', 'date'],
            'objective' => ['nullable', 'string', 'max:50'],
            'stock'     => ['nullable', 'string', 'max:50'],
        ]);

        $q = DB::table('consumables_in_stock as cis')
            ->leftJoin('ref_objective as ro', function ($j) {
                $j->on('ro.id_objective', '=', 'cis.id_objective')->on('ro.lab', '=', 'cis.lab');
            })
            ->leftJoin('consumables_stock as cs', function ($j) {
                $j->on('cs.id_stock', '=', 'cis.id_stock')->on('cs.lab', '=', 'cis.lab');
            })
            ->where('cis.lab', Lab::current())
            ->where('cis.flag', 0)
            ->select([
                'ro.objective', 'cs.product_name', 'cis.quantity_take', 'cis.closed_container',
                'cis.unit_measure_lab', 'cis.quantity_per_unit', 'cis.loose_items', 'cis.total_quantity',
                'cis.unit_of_measure', 'cis.date_collected', 'cis.expired_date', 'cis.comments',
            ])
            ->orderBy('cis.date_collected')
            ->orderBy('cis.id_objective');

        if (! empty($v['date1'])) {
            $q->where('cis.date_collected', '>=', $v['date1']);
        }
        if (! empty($v['date2'])) {
            $q->where('cis.date_collected', '<=', $v['date2']);
        }
        if (isset($v['objective']) && $v['objective'] !== '' && $v['objective'] !== 'all') {
            $q->where('cis.id_objective', $v['objective']);
            if (! empty($v['stock'])) {
                $q->where('cis.id_stock', $v['stock']);
            }
        }

        return $q;
    }

    /** Stops spreadsheet formulas coming from text cells (=, +, -, @). */
    private function safe($value)
    {
        if (is_string($value) && $value !== '' && strpbrk($value[0], "=+-@\t\r") !== false) {
            return "'" . $value;
        }

        return $value;
    }
}
