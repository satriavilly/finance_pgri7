@extends('layouts.app')
@section('title', 'Dashboard Bendahara')
@section('page-title', 'Dashboard Bendahara')

@section('content')
<div class="space-y-5">

    {{-- Ringkasan Global --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total Tagihan</p>
            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($data['totalTagihan'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Terkumpul</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($data['totalTerbayar'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Tunggakan</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($data['totalTunggakan'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Pemasukan Bulan Ini</p>
            <p class="text-lg font-bold text-blue-700">Rp {{ number_format($data['pemasukanBulanIni'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $data['menungguVerifikasi'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Progress Global --}}
    @php
        $totalTagihan  = $data['totalTagihan'] ?? 0;
        $totalTerbayar = $data['totalTerbayar'] ?? 0;
        $globalPct     = $totalTagihan > 0 ? min(100, round($totalTerbayar / $totalTagihan * 100)) : 0;
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex justify-between items-center mb-2">
            <p class="text-sm font-medium text-gray-700">Progress Pembayaran Keseluruhan</p>
            <span class="text-sm font-bold text-blue-600">{{ $globalPct }}%</span>
        </div>
        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-3 rounded-full bg-gradient-to-r from-blue-500 to-green-500 transition-all"
                 style="width: {{ $globalPct }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1.5">
            <span>Terkumpul: Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</span>
            <span>Target: Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Breakdown Per Kelas --}}
    @if(!empty($data['perKelas']) && $data['perKelas']->isNotEmpty())
    <div class="space-y-3">
        <h2 class="text-sm font-semibold text-gray-600 px-1">Breakdown Per Kelas</h2>

        @foreach($data['perKelas'] as $row)
        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm overflow-hidden">

            {{-- Header kelas --}}
            <div class="flex items-center gap-3 px-4 py-3.5 cursor-pointer hover:bg-gray-50 select-none"
                 @click="open = !open">
                <div class="text-gray-400 w-4 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-90' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-700 font-bold text-sm">{{ $row['kelas']->tingkat }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">Kelas {{ $row['kelas']->nama }}</p>
                    <div class="flex items-center gap-3 mt-1">
                        <div class="w-24 h-1.5 bg-gray-200 rounded-full">
                            <div class="h-1.5 rounded-full {{ $row['pct'] >= 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                                 style="width: {{ $row['pct'] }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $row['pct'] }}%</span>
                        <span class="text-xs text-gray-400">·</span>
                        <span class="text-xs text-green-600 font-medium">{{ $row['lunas_count'] }} lunas</span>
                        @if($row['tunggakan_count'] > 0)
                        <span class="text-xs text-red-500 font-medium">{{ $row['tunggakan_count'] }} tunggakan</span>
                        @endif
                    </div>
                </div>
                <div class="hidden sm:block text-right flex-shrink-0">
                    <p class="text-xs font-semibold {{ $row['tunggakan'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        @if($row['tunggakan'] > 0)
                            Tunggakan Rp {{ number_format($row['tunggakan'], 0, ',', '.') }}
                        @else
                            Lunas Semua
                        @endif
                    </p>
                    <p class="text-xs text-gray-400">dari Rp {{ number_format($row['total'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Expand: ringkasan + tombol lihat siswa --}}
            <div x-show="open" x-cloak class="border-t border-gray-100 bg-gray-50 px-4 py-3 space-y-3">
                <div class="grid grid-cols-3 gap-3 text-xs">
                    <div class="bg-white rounded-lg border border-gray-100 p-3 text-center">
                        <p class="text-gray-400 mb-0.5">Total Tagihan</p>
                        <p class="font-semibold text-gray-700">Rp {{ number_format($row['total'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-100 p-3 text-center">
                        <p class="text-gray-400 mb-0.5">Terkumpul</p>
                        <p class="font-semibold text-green-600">Rp {{ number_format($row['terbayar'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-100 p-3 text-center">
                        <p class="text-gray-400 mb-0.5">Tunggakan</p>
                        <p class="font-semibold {{ $row['tunggakan'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            Rp {{ number_format($row['tunggakan'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400">
                        {{ $row['kelas']->siswa_count }} siswa ·
                        {{ $row['lunas_count'] }} lunas ·
                        {{ $row['tunggakan_count'] }} ada tunggakan
                    </p>
                    <a href="{{ route('bendahara.kelas.siswa', $row['kelas']->id) }}"
                       class="flex items-center gap-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg">
                        <i class="fas fa-users"></i> Lihat Per Siswa
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-400 text-sm">
        Belum ada data kelas untuk tahun ajaran aktif.
    </div>
    @endif

</div>
@endsection
