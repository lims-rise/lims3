<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
{
    View::composer('layouts.app', function ($view) {
    $user = Auth::user();
    if (! $user) {
        return;
    }

    $allowed = DB::table('tbl_hak_akses')
        ->where('id_user_level', $user->id_user_level)
        ->pluck('id_menu');

    $menus = DB::table('tbl_menu')
        ->where('is_aktif', 'y')
        ->whereIn('id_menu', $allowed)
        ->orderBy('id_menu')
        ->get();

    $ready = config('modules', []);

    $children = $menus->where('is_main_menu', '!=', 0)
        ->filter(fn ($m) => $m->url === '#' || array_key_exists($m->url, $ready))
        ->groupBy('is_main_menu')
        ->map(function ($items) {
            $out = collect();
            foreach ($items as $m) {
                // skip leading or consecutive separators
                if ($m->url === '#' && ($out->isEmpty() || $out->last()->url === '#')) {
                    continue;
                }
                $out->push($m);
            }
            while ($out->isNotEmpty() && $out->last()->url === '#') {
                $out->pop();
            }
            return $out;
        });

    $parents = $menus->where('is_main_menu', 0)
        ->filter(fn ($p) => $children->has($p->id_menu) && $children[$p->id_menu]->isNotEmpty());

    $view->with(['parentMenus' => $parents, 'childMenus' => $children]);
    });
}
}
