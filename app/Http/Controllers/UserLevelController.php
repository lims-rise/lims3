<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserLevelController extends Controller
{
    public function index()
    {
        $levels = DB::table('tbl_user_level as l')
            ->leftJoin('tbl_user as u', 'u.id_user_level', '=', 'l.id_user_level')
            ->select('l.id_user_level', 'l.nama_level', DB::raw('COUNT(u.id_users) as users'))
            ->groupBy('l.id_user_level', 'l.nama_level')
            ->orderBy('l.id_user_level')
            ->get();

        return view('admin.userlevels.index', compact('levels'));
    }

    public function create()
    {
        return view('admin.userlevels.form', ['level' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate($this->rules());

        DB::table('tbl_user_level')->insert(['nama_level' => trim($v['nama_level'])]);

        return redirect()->route('userlevel.index')->with('success', 'Level created.');
    }

    public function edit(int $id)
    {
        return view('admin.userlevels.form', ['level' => $this->find($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->find($id);
        $v = $request->validate($this->rules($id));

        DB::table('tbl_user_level')->where('id_user_level', $id)->update(['nama_level' => trim($v['nama_level'])]);

        return redirect()->route('userlevel.index')->with('success', 'Level updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->find($id);

        if (DB::table('tbl_user')->where('id_user_level', $id)->exists()) {
            return redirect()->route('userlevel.index')
                ->with('error', 'This level still has users. Move them to another level first.');
        }

        DB::transaction(function () use ($id) {
            DB::table('tbl_hak_akses')->where('id_user_level', $id)->delete();
            DB::table('tbl_user_level')->where('id_user_level', $id)->delete();
        });

        return redirect()->route('userlevel.index')->with('success', 'Level deleted.');
    }

    /** Access matrix: every tbl_menu row with a checkbox for this level. */
    public function access(int $id)
    {
        $level   = $this->find($id);
        $menus   = DB::table('tbl_menu')->orderBy('id_menu')->get();
        $granted = DB::table('tbl_hak_akses')->where('id_user_level', $id)->pluck('id_menu')->all();

        return view('admin.userlevels.access', compact('level', 'menus', 'granted'));
    }

    /** Grants or revokes one menu for a level (idempotent, called by the checkbox). */
    public function setAccess(Request $request, int $id, int $menu): JsonResponse
    {
        $this->find($id);
        abort_unless(DB::table('tbl_menu')->where('id_menu', $menu)->exists(), 404);

        $granted = $request->boolean('granted');
        $pair    = ['id_user_level' => $id, 'id_menu' => $menu];
        $exists  = DB::table('tbl_hak_akses')->where($pair)->exists();

        if ($granted && ! $exists) {
            DB::table('tbl_hak_akses')->insert($pair);
        } elseif (! $granted && $exists) {
            DB::table('tbl_hak_akses')->where($pair)->delete();
        }

        return response()->json(['ok' => true]);
    }

    // ------------------------------------------------------------------ helpers

    private function rules(?int $id = null): array
    {
        return [
            'nama_level' => ['required', 'string', 'max:100',
                Rule::unique('tbl_user_level', 'nama_level')->ignore($id, 'id_user_level')],
        ];
    }

    private function find(int $id): object
    {
        $level = DB::table('tbl_user_level')->where('id_user_level', $id)->first();
        abort_unless($level, 404);

        return $level;
    }
}
