<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BuktiBayarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WaliKelas\TagihanController as WaliTagihanController;
use App\Http\Controllers\WaliKelas\PembayaranController as WaliPembayaranController;
use App\Http\Controllers\AdminTu\TagihanController as AdminTuTagihanController;
use App\Http\Controllers\AdminTu\KategoriTagihanController as AdminTuKategoriController;
use App\Http\Controllers\Siswa\TagihanController as SiswaTagihanController;
use App\Http\Controllers\Ortu\TagihanController as OrtuTagihanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\SiswaImportExportController;
use App\Http\Controllers\Admin\WaliKelasController;
use App\Http\Controllers\Bendahara\SiswaController as BendaharaSiswaController;
use App\Http\Controllers\Bendahara\LaporanController as BendaharaLaporanController;
use App\Http\Controllers\Bendahara\SppController as BendaharaSppController;
use App\Http\Controllers\Bendahara\BeasiswaController as BendaharaBeasiswaController;
use App\Http\Controllers\Bendahara\TunggakanController as BendaharaTunggakanController;
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
        Route::get('tagihan/{tagihan}/penerima', [AdminTuTagihanController::class, 'penerima'])->name('tagihan.penerima');
        Route::get('tagihan/{tagihan}/edit', [AdminTuTagihanController::class, 'edit'])->name('tagihan.edit');
        Route::put('tagihan/{tagihan}', [AdminTuTagihanController::class, 'update'])->name('tagihan.update');
        Route::post('tagihan/distribusi-semua', [AdminTuTagihanController::class, 'distribusiSemua'])->name('tagihan.distribusi-semua');
        Route::post('tagihan/{tagihan}/distribusi-ulang', [AdminTuTagihanController::class, 'distribusiUlang'])->name('tagihan.distribusi-ulang');
        Route::delete('tagihan/{tagihan}', [AdminTuTagihanController::class, 'destroy'])->name('tagihan.destroy');

        Route::get('kategori',                     [AdminTuKategoriController::class, 'index'])->name('kategori.index');
        Route::post('kategori',                    [AdminTuKategoriController::class, 'store'])->name('kategori.store');
        Route::patch('kategori/{kategori}',        [AdminTuKategoriController::class, 'update'])->name('kategori.update');
        Route::delete('kategori/{kategori}',       [AdminTuKategoriController::class, 'destroy'])->name('kategori.destroy');
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

        Route::get('tunggakan',               [BendaharaTunggakanController::class, 'index'])->name('tunggakan.index');

        Route::get('beasiswa',                [BendaharaBeasiswaController::class, 'index'])->name('beasiswa.index');
        Route::post('beasiswa',               [BendaharaBeasiswaController::class, 'store'])->name('beasiswa.store');
        Route::get('beasiswa/export',         [BendaharaBeasiswaController::class, 'export'])->name('beasiswa.export');
        Route::post('beasiswa/import',        [BendaharaBeasiswaController::class, 'import'])->name('beasiswa.import');
        Route::post('beasiswa/{siswa}/void',  [BendaharaBeasiswaController::class, 'void'])->name('beasiswa.void');
    });

    // Ortu
    Route::middleware('role:ortu')->prefix('ortu')->name('ortu.')->group(function () {
        Route::get('tagihan/pdf', [OrtuTagihanController::class, 'downloadPdf'])->name('tagihan.pdf');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('users/{user}/toggle-aktif', [UserController::class, 'toggleAktif'])->name('users.toggle-aktif');

        Route::resource('tahun-ajaran', TahunAjaranController::class)
            ->except(['show'])
            ->parameters(['tahun-ajaran' => 'tahunAjaran']);
        Route::post('tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');

        Route::get('siswa-import', [SiswaImportExportController::class, 'index'])->name('siswa-import.index');
        Route::get('siswa-import/template', [SiswaImportExportController::class, 'downloadTemplate'])->name('siswa-import.template');
        Route::get('siswa-import/export', [SiswaImportExportController::class, 'export'])->name('siswa-import.export');
        Route::post('siswa-import/import', [SiswaImportExportController::class, 'import'])->name('siswa-import.import');

        Route::get('wali-kelas', [WaliKelasController::class, 'index'])->name('wali-kelas.index');
        Route::patch('wali-kelas/{kelas}', [WaliKelasController::class, 'update'])->name('wali-kelas.update');
    });
});
