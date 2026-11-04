<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Folder under public/ for profile pictures (same folder as the old LIMS). */
    public const PHOTO_DIR = 'assets/foto_profil';

    public function index()
    {
        $users = DB::table('tbl_user as u')
            ->leftJoin('tbl_user_level as l', 'l.id_user_level', '=', 'u.id_user_level')
            ->select('u.id_users', 'u.full_name', 'u.email', 'u.is_aktif', 'u.lab', 'l.nama_level')
            ->orderBy('u.full_name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', [
            'user'   => null,
            'levels' => $this->levels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate($this->rules());

        DB::table('tbl_user')->insert([
            'full_name'     => trim($v['full_name']),
            'email'         => trim($v['email']),
            'password'      => Hash::make($v['password']),
            'id_user_level' => $v['id_user_level'],
            'is_aktif'      => $v['is_aktif'],
            'lab'           => $v['lab'],
            'images'        => $this->storePhoto($request) ?? '',
        ]);

        return redirect()->route('user.index')->with('success', 'User created.');
    }

    public function edit(int $id)
    {
        return view('admin.users.form', [
            'user'   => $this->find($id),
            'levels' => $this->levels(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->find($id);
        $v = $request->validate($this->rules($id));

        // do not let someone lock themselves out
        if ($id === (int) $request->user()->id_users && $v['is_aktif'] !== 'y') {
            return back()->withInput()->withErrors(['is_aktif' => 'You cannot deactivate your own account.']);
        }

        $data = [
            'full_name'     => trim($v['full_name']),
            'email'         => trim($v['email']),
            'id_user_level' => $v['id_user_level'],
            'is_aktif'      => $v['is_aktif'],
            'lab'           => $v['lab'],
        ];
        if (! empty($v['password'])) {
            $data['password'] = Hash::make($v['password']);
        }
        if ($photo = $this->storePhoto($request)) {
            $data['images'] = $photo;
        }

        DB::table('tbl_user')->where('id_users', $id)->update($data);

        return redirect()->route('user.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->find($id);

        if ($id === (int) $request->user()->id_users) {
            return redirect()->route('user.index')->with('error', 'You cannot delete your own account.');
        }

        DB::table('tbl_user')->where('id_users', $id)->delete();

        return redirect()->route('user.index')->with('success', 'User deleted.');
    }

    // ------------------------------------------------------------------ helpers

    private function rules(?int $id = null): array
    {
        return [
            'full_name'     => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:150', Rule::unique('tbl_user', 'email')->ignore($id, 'id_users')],
            'password'      => [$id ? 'nullable' : 'required', 'string', 'min:8'],
            'id_user_level' => ['required', 'integer', Rule::exists('tbl_user_level', 'id_user_level')],
            'is_aktif'      => ['required', Rule::in(['y', 'n'])],
            'lab'           => ['required', Rule::in(['1', '2'])],
            'images'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ];
    }

    private function find(int $id): object
    {
        $user = DB::table('tbl_user')->where('id_users', $id)->first();
        abort_unless($user, 404);

        return $user;
    }

    private function levels()
    {
        return DB::table('tbl_user_level')->orderBy('nama_level')->get();
    }

    /** Saves the uploaded picture under a random name and returns the file name. */
    private function storePhoto(Request $request): ?string
    {
        if (! $request->hasFile('images')) {
            return null;
        }

        $file = $request->file('images');
        $dir  = public_path(self::PHOTO_DIR);
        File::ensureDirectoryExists($dir);

        $name = Str::random(24) . '.' . $file->extension();
        $file->move($dir, $name);

        return $name;
    }
}
