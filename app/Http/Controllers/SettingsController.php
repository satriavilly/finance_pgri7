<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', ['user' => auth()->user()]);
    }

    public function updateFoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto_profil' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto_profil.required' => 'Pilih foto terlebih dahulu.',
            'foto_profil.image'    => 'File harus berupa gambar.',
            'foto_profil.mimes'    => 'Format foto harus JPG, PNG, atau WebP.',
            'foto_profil.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $user = auth()->user();

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $path = $request->file('foto_profil')->store('foto_profil', 'public');
        $user->update(['foto_profil' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function hapusFoto(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
            $user->update(['foto_profil' => null]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama'  => ['required', 'string'],
            'password_baru'  => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.min'       => 'Password baru minimal 8 karakter.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
