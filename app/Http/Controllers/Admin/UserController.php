<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $search = $request->input('search');

        $users = User::with('roles', 'kelasWali', 'siswa.kelas', 'anak')
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        $roles = Role::all();
        $kelasList = Kelas::with('tahunAjaran')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();
        $siswaList = Siswa::with('kelas')->orderBy('nama')->get();
        return view('admin.users.create', compact('roles', 'kelasList', 'siswaList'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $kelasId     = $data['kelas_id'] ?? null;
        $anakSiswaId = $data['anak_siswa_id'] ?? null;
        $nis         = $data['nis'] ?? null;
        unset($data['kelas_id'], $data['anak_siswa_id'], $data['nis']);

        $user = User::create($data);
        $user->assignRole($request->role);

        if ($kelasId && $request->role === 'wali_kelas') {
            Kelas::where('id', $kelasId)->update(['wali_kelas_id' => $user->id]);
        }

        if ($request->role === 'siswa' && $nis) {
            Siswa::create([
                'user_id'  => $user->id,
                'nis'      => $nis,
                'nama'     => $user->name,
                'kelas_id' => $kelasId ?: null,
            ]);
        }

        if ($request->role === 'ortu') {
            Siswa::where('ortu_user_id', $user->id)->update(['ortu_user_id' => null]);
            if ($anakSiswaId) {
                Siswa::where('id', $anakSiswaId)->update(['ortu_user_id' => $user->id]);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $roles = Role::all();
        $kelasList = Kelas::with('tahunAjaran')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();
        $siswaList = Siswa::with('kelas')->orderBy('nama')->get();
        return view('admin.users.edit', compact('user', 'roles', 'kelasList', 'siswaList'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $kelasId     = $data['kelas_id'] ?? null;
        $anakSiswaId = $data['anak_siswa_id'] ?? null;
        $nis         = $data['nis'] ?? null;
        unset($data['kelas_id'], $data['anak_siswa_id'], $data['nis']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        if ($request->role === 'wali_kelas') {
            Kelas::where('wali_kelas_id', $user->id)->update(['wali_kelas_id' => null]);
            if ($kelasId) {
                Kelas::where('id', $kelasId)->update(['wali_kelas_id' => $user->id]);
            }
        }

        if ($request->role === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
            if ($siswa) {
                if ($kelasId) $siswa->update(['kelas_id' => $kelasId]);
            } elseif ($nis) {
                Siswa::create([
                    'user_id'  => $user->id,
                    'nis'      => $nis,
                    'nama'     => $user->name,
                    'kelas_id' => $kelasId ?: null,
                ]);
            }
        }

        if ($request->role === 'ortu') {
            Siswa::where('ortu_user_id', $user->id)->update(['ortu_user_id' => null]);
            if ($anakSiswaId) {
                Siswa::where('id', $anakSiswaId)->update(['ortu_user_id' => $user->id]);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleAktif(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User berhasil {$status}.");
    }
}
