<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Same rule as ModuleController::definition(): the page is allowed only when the
 * user's level has a row in tbl_hak_akses for an active tbl_menu entry whose url
 * equals the given menu url. Usage: ->middleware(CheckMenuAccess::class . ':user')
 */
class CheckMenuAccess
{
    public function handle(Request $request, Closure $next, string $menuUrl): Response
    {
        $allowed = DB::table('tbl_menu as m')
            ->join('tbl_hak_akses as h', 'h.id_menu', '=', 'm.id_menu')
            ->where('m.url', $menuUrl)
            ->where('m.is_aktif', 'y')
            ->where('h.id_user_level', $request->user()->id_user_level)
            ->exists();

        abort_unless($allowed, 403);

        return $next($request);
    }
}
