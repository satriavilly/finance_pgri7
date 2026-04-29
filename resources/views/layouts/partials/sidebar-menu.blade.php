@php $role = auth()->user()->getRoleNames()->first(); @endphp

@php
if (!function_exists('sidebarLink')) {
    function sidebarLink($url, $icon, $label, $active = false) {
        $baseClass = 'flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2';
        $activeClass = $active ? ' bg-blue-700' : '';
        return $baseClass . $activeClass;
    }
}
@endphp

{{-- Dashboard --}}
<a href="{{ route('dashboard') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-home w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Dashboard</span>
</a>

@role('admin_tu')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Tata Usaha</div>

<a href="{{ route('admin-tu.tagihan.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin-tu.tagihan.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-file-invoice-dollar w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Kelola Tagihan</span>
</a>

<a href="{{ route('admin-tu.kategori.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin-tu.kategori.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-tags w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Kategori Tagihan</span>
</a>
@endrole

@role('wali_kelas')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Kelas Saya</div>

<a href="{{ route('wali-kelas.siswa.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('wali-kelas.siswa.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-money-bill-wave w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Input Pembayaran</span>
</a>

<a href="{{ route('wali-kelas.pembayaran.verifikasi') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('wali-kelas.pembayaran.verifikasi') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-check-double w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Verifikasi Bukti Bayar</span>
</a>
@endrole

@role('bendahara')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Keuangan</div>

<a href="{{ route('dashboard') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-chart-pie w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Dashboard</span>
</a>

<a href="{{ route('bendahara.laporan.transaksi') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('bendahara.laporan.transaksi') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-receipt w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Laporan Transaksi</span>
</a>

<a href="{{ route('bendahara.laporan.tagihan') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('bendahara.laporan.tagihan') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-file-invoice-dollar w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Rekap Tagihan</span>
</a>

<a href="{{ route('bendahara.spp.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('bendahara.spp.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-calendar-alt w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Kelola SPP</span>
</a>
@endrole

@role('siswa')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Keuangan Saya</div>

<a href="{{ route('siswa.tagihan.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('siswa.tagihan.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-receipt w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Tagihan Saya</span>
</a>
@endrole

{{-- Settings (semua role) --}}
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Akun</div>
<a href="{{ route('settings') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('settings') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-cog w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Pengaturan Akun</span>
</a>

@role('admin')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Administrasi</div>

<a href="{{ route('admin.users.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin.users.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-users w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Manajemen User</span>
</a>

<a href="{{ route('admin.tahun-ajaran.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin.tahun-ajaran.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-calendar-alt w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Tahun Ajaran</span>
</a>

<a href="{{ route('admin.siswa-import.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin.siswa-import.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-file-import w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Import & Export Siswa</span>
</a>

<a href="{{ route('admin.wali-kelas.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin.wali-kelas.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-chalkboard-teacher w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Penugasan Wali Kelas</span>
</a>
@endrole
