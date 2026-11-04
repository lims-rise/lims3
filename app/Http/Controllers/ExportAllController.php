<?php

namespace App\Http\Controllers;

use App\Support\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Builds the list of every export the signed-in user may download (modules, their extra exports,
 * and the Reports pages). The dashboard button then downloads them one by one from the same
 * endpoints as the normal export buttons and packs them into one ZIP in the browser.
 */
class ExportAllController extends Controller
{
    public function manifest(Request $request)
    {
        // menu urls this user's level may open (same rule as ModuleController / CheckMenuAccess)
        $allowed = DB::table('tbl_menu as m')
            ->join('tbl_hak_akses as h', 'h.id_menu', '=', 'm.id_menu')
            ->where('m.is_aktif', 'y')
            ->where('h.id_user_level', $request->user()->id_user_level)
            ->pluck('m.url')
            ->map(fn ($u) => strtolower((string) $u))
            ->flip();

        $files = [];

        // ---- modules (config/modules.php)
        foreach (config('modules', []) as $key => $def) {
            if (! is_array($def) || ($def['custom'] ?? false) || ! isset($def['table'])) {
                continue;   // pages with their own controller are covered by Reports below
            }
            if (! isset($allowed[strtolower($key)])) {
                continue;
            }

            $title = $def['title'] ?? $key;
            $files[] = [
                'folder' => 'modules/' . $key,
                'label'  => $title,
                'url'    => route('module.export', $key),
                'name'   => $key,
            ];
            foreach (array_keys($def['exports'] ?? []) as $variant) {
                $files[] = [
                    'folder' => 'modules/' . $key,
                    'label'  => $title . ' (' . $variant . ')',
                    'url'    => route('module.export.variant', [$key, $variant]),
                    'name'   => $key . '_' . $variant,
                ];
            }
        }

        // ---- Reports (config/reports.php): whole date range
        foreach (config('reports', []) as $key => $cfg) {
            if (! isset($allowed[strtolower($key)])) {
                continue;
            }
            $title = $cfg['title'] ?? $key;

            if (isset($cfg['sheets'])) {
                $files[] = [
                    'folder' => 'reports/' . $key,
                    'label'  => $title,
                    'url'    => url('m/' . $key . '/export'),
                    'name'   => $key,
                ];
            }
            foreach ($cfg['types'] ?? [] as $id => $type) {
                $files[] = [
                    'folder' => 'reports/' . $key,
                    'label'  => $title . ' - ' . $type['label'],
                    'url'    => url('m/' . $key . '/export') . '?' . http_build_query(['rep' => (string) $id]),
                    'name'   => $key . '_' . $id,
                ];
            }
        }

        return response()->json([
            'lab'   => (string) Lab::current() === '2' ? 'Fiji' : 'Indonesia',
            'date'  => date('Ymd'),
            'files' => $files,
        ]);
    }
}
