@php $role = auth()->user()->getRoleNames()->first(); @endphp

@php
function sidebarLink($url, $icon, $label, $active = false) {
    $baseClass = 'flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2';
    $activeClass = $active ? ' bg-blue-700' : '';
    return $baseClass . $activeClass;
}
@endphp

{{-- Dashboard --}}
<a href="{{ route('dashboard') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-home w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Dashboard</span>
</a>

@role('wali_kelas')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Kelas Saya</div>

<a href="{{ route('wali-kelas.tagihan.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('wali-kelas.tagihan.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-file-invoice-dollar w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Kelola Tagihan</span>
</a>

<a href="{{ route('wali-kelas.pembayaran.verifikasi') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('wali-kelas.pembayaran.verifikasi') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-check-double w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Verifikasi Bukti Bayar</span>
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

@role('admin')
<div class="px-4 py-2 text-xs text-blue-400 uppercase tracking-wide mt-3" x-show="sidebarOpen" x-cloak>Administrasi</div>

<a href="{{ route('admin.users.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors rounded mx-2 {{ request()->routeIs('admin.users.*') ? 'bg-blue-700' : '' }}">
    <i class="fas fa-users w-5 flex-shrink-0"></i>
    <span x-show="sidebarOpen" x-cloak>Manajemen User</span>
</a>
@endrole
