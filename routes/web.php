<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BuktiBayarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WaliKelas\TagihanController as WaliTagihanController;
use App\Http\Controllers\WaliKelas\PembayaranController as WaliPembayaranController;
use App\Http\Controllers\Siswa\TagihanController as SiswaTagihanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Bendahara\SiswaController as BendaharaSiswaController;
use App\Http\Controllers\Bendahara\LaporanController as BendaharaLaporanController;
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

    // Wali Kelas
    Route::middleware('role:wali_kelas')->prefix('wali-kelas')->name('wali-kelas.')->group(function () {
        Route::resource('tagihan', WaliTagihanController::class)->only(['index', 'create', 'store']);
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
        Route::get('tagihan/{tagihan}', [SiswaTagihanController::class, 'show'])->name('tagihan.show');
        Route::get('tagihan/{tagihan}/upload', [SiswaTagihanController::class, 'formUpload'])->name('tagihan.upload');
        Route::post('tagihan/{tagihan}/upload', [SiswaTagihanController::class, 'uploadBukti'])->name('tagihan.upload.store');
    });

    // Bendahara
    Route::middleware('role:bendahara')->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('kelas/{kelas}', [BendaharaSiswaController::class, 'siswaKelas'])->name('kelas.siswa');
        Route::get('laporan/transaksi', [BendaharaLaporanController::class, 'transaksi'])->name('laporan.transaksi');
        Route::get('laporan/tagihan',   [BendaharaLaporanController::class, 'tagihan'])->name('laporan.tagihan');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('users/{user}/toggle-aktif', [UserController::class, 'toggleAktif'])->name('users.toggle-aktif');
    });
});
