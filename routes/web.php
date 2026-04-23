<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BuktiBayarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WaliKelas\TagihanController as WaliTagihanController;
use App\Http\Controllers\WaliKelas\PembayaranController as WaliPembayaranController;
use App\Http\Controllers\AdminTu\TagihanController as AdminTuTagihanController;
use App\Http\Controllers\Siswa\TagihanController as SiswaTagihanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Bendahara\SiswaController as BendaharaSiswaController;
use App\Http\Controllers\Bendahara\LaporanController as BendaharaLaporanController;
use App\Http\Controllers\Bendahara\SppController as BendaharaSppController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/bukti/{pembayaran}', [BuktiBayarController::class, 'show'])->name('bukti.show');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/foto', [SettingsController::class, 'updateFoto'])->name('settings.foto');
    Route::delete('/settings/foto', [SettingsController::class, 'hapusFoto'])->name('settings.foto.hapus');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Admin TU
    Route::middleware('role:admin_tu')->prefix('admin-tu')->name('admin-tu.')->group(function () {
        Route::get('tagihan', [AdminTuTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('tagihan/buat', [AdminTuTagihanController::class, 'create'])->name('tagihan.create');
        Route::post('tagihan', [AdminTuTagihanController::class, 'store'])->name('tagihan.store');
        Route::get('tagihan/{tagihan}/edit', [AdminTuTagihanController::class, 'edit'])->name('tagihan.edit');
        Route::put('tagihan/{tagihan}', [AdminTuTagihanController::class, 'update'])->name('tagihan.update');
        Route::post('tagihan/{tagihan}/distribusi-ulang', [AdminTuTagihanController::class, 'distribusiUlang'])->name('tagihan.distribusi-ulang');
        Route::delete('tagihan/{tagihan}', [AdminTuTagihanController::class, 'destroy'])->name('tagihan.destroy');
    });

    // Wali Kelas
    Route::middleware('role:wali_kelas')->prefix('wali-kelas')->name('wali-kelas.')->group(function () {
        Route::get('siswa', [WaliPembayaranController::class, 'daftarSiswa'])->name('siswa.index');
        Route::get('siswa/{siswa}/tagihan', [WaliPembayaranController::class, 'siswaDaftarTagihan'])->name('siswa.tagihan');
        Route::get('pembayaran/verifikasi', [WaliPembayaranController::class, 'verifikasiBuktiBayar'])->name('pembayaran.verifikasi');
        Route::get('tagihan-siswa/{tagihan}/bayar', [WaliPembayaranController::class, 'formBayarTunai'])->name('pembayaran.form-tunai');
        Route::post('tagihan-siswa/{tagihan}/bayar', [WaliPembayaranController::class, 'bayarTunai'])->name('pembayaran.bayar-tunai');
        Route::post('tagihan-siswa/{tagihan}/upload-bukti', [WaliPembayaranController::class, 'uploadBuktiWaliKelas'])->name('pembayaran.upload-bukti');
        Route::post('pembayaran/{pembayaran}/approve', [WaliPembayaranController::class, 'approve'])->name('pembayaran.approve');
        Route::post('pembayaran/{pembayaran}/reject', [WaliPembayaranController::class, 'reject'])->name('pembayaran.reject');
        Route::post('pembayaran/{pembayaran}/void', [WaliPembayaranController::class, 'void'])->name('pembayaran.void');
    });

    // Siswa
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('tagihan', [SiswaTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('tagihan/pdf', [SiswaTagihanController::class, 'downloadPdf'])->name('tagihan.pdf');
        Route::get('tagihan/{tagihan}', [SiswaTagihanController::class, 'show'])->name('tagihan.show');
    });

    // Bendahara
    Route::middleware('role:bendahara')->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('kelas/{kelas}', [BendaharaSiswaController::class, 'siswaKelas'])->name('kelas.siswa');
        Route::get('laporan/transaksi', [BendaharaLaporanController::class, 'transaksi'])->name('laporan.transaksi');
        Route::get('laporan/tagihan',   [BendaharaLaporanController::class, 'tagihan'])->name('laporan.tagihan');
        Route::get('spp',                               [BendaharaSppController::class, 'index'])->name('spp.index');
        Route::get('spp/buat',                          [BendaharaSppController::class, 'create'])->name('spp.create');
        Route::post('spp',                              [BendaharaSppController::class, 'store'])->name('spp.store');
        Route::post('spp/bayar/{tagihanSiswa}',         [BendaharaSppController::class, 'bayar'])->name('spp.bayar');
        Route::post('spp/{periode}/distribusi-ulang',   [BendaharaSppController::class, 'distribusiUlang'])->name('spp.distribusi-ulang');
        Route::get('spp/{periode}/edit',                [BendaharaSppController::class, 'edit'])->name('spp.edit');
        Route::put('spp/{periode}',                     [BendaharaSppController::class, 'update'])->name('spp.update');
        Route::get('spp/{periode}',                     [BendaharaSppController::class, 'show'])->name('spp.show');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('users/{user}/toggle-aktif', [UserController::class, 'toggleAktif'])->name('users.toggle-aktif');
    });
});
