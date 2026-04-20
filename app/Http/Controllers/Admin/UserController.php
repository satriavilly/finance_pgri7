<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles', 'kelasWali', 'siswa.kelas')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::all();
        $kelasList = Kelas::with('tahunAjaran')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();
        return view('admin.users.create', compact('roles', 'kelasList'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $kelasId = $data['kelas_id'] ?? null;
        unset($data['kelas_id']);

        $user = User::create($data);
        $user->assignRole($request->role);

        if ($kelasId && $request->role === 'wali_kelas') {
            Kelas::where('id', $kelasId)->update(['wali_kelas_id' => $user->id]);
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
        return view('admin.users.edit', compact('user', 'roles', 'kelasList'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $kelasId = $data['kelas_id'] ?? null;
        unset($data['kelas_id']);

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

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleAktif(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User berhasil {$status}.");
    }
}
