@extends('layouts.app')
@section('title', 'Detail Pembayaran Kelas ' . $kelas->nama)
@section('page-title', 'Kelas ' . $kelas->nama . ' — Detail Pembayaran')

@php
$kategoriIcon = [
    'kas_kelas' => ['icon' => 'fa-wallet',        'color' => 'text-blue-500'],
    'buku_lks'  => ['icon' => 'fa-book',           'color' => 'text-purple-500'],
    'kegiatan'  => ['icon' => 'fa-calendar-check', 'color' => 'text-orange-500'],
    'seragam'   => ['icon' => 'fa-tshirt',         'color' => 'text-teal-500'],
    'lainnya'   => ['icon' => 'fa-tag',            'color' => 'text-gray-500'],
];
$statusBadge = [
    'belum_bayar' => 'bg-red-100 text-red-700',
    'cicilan'     => 'bg-yellow-100 text-yellow-700',
    'lunas'       => 'bg-green-100 text-green-700',
];
$metodeLabel = [
    'tunai'    => ['label' => 'Tunai',         'icon' => 'fa-money-bill-wave', 'color' => 'text-green-500'],
    'transfer' => ['label' => 'Transfer Bank', 'icon' => 'fa-university',      'color' => 'text-blue-500'],
    'qris'     => ['label' => 'QRIS',          'icon' => 'fa-qrcode',          'color' => 'text-purple-500'],
];
@endphp

@section('content')
<div class="space-y-4"
     x-data="{
        search: '',
        matches(nama, nis) {
            let q = this.search.toLowerCase();
            return nama.toLowerCase().includes(q) || nis.toLowerCase().includes(q);
        }
     }">

    @php
        $totalSiswa   = $siswa->count();
        $sudahLunas   = $siswa->filter(fn($s) => $s->tagihanSiswa->whereIn('status', ['belum_bayar','cicilan'])->isEmpty())->count();
        $adaTunggakan = $totalSiswa - $sudahLunas;
        $sisaTotal    = $siswa->sum(fn($s) => $s->tagihanSiswa->whereIn('status', ['belum_bayar','cicilan'])->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar));
    @endphp

    {{-- Back --}}
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-blue-600">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $totalSiswa }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Siswa</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $sudahLunas }}</p>
            <p class="text-xs text-gray-500 mt-1">Lunas Semua</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $adaTunggakan }}</p>
            <p class="text-xs text-gray-500 mt-1">Ada Tunggakan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($sisaTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Tunggakan</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm px-4 py-3">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" x-model="search" placeholder="Cari nama atau NIS siswa..."
                   class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <button x-show="search" @click="search=''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Tabel Siswa --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-700">Daftar Siswa Kelas {{ $kelas->nama }}</p>
            <p class="text-xs text-gray-500">TA {{ $kelas->tahunAjaran->nama }}</p>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($siswa as $s)
            @php
                $tagihanAktif = $s->tagihanSiswa->whereIn('status', ['belum_bayar', 'cicilan']);
                $sisaSiswa    = $tagihanAktif->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);
                $lunas        = $tagihanAktif->isEmpty();
            @endphp

            <div x-data="{ open: false }"
                 x-show="matches('{{ addslashes($s->nama) }}', '{{ $s->nis }}')">

                {{-- Baris utama --}}
                <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer select-none"
                     @click="open = !open">
                    <div class="text-gray-400 w-4 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-90' : ''">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $lunas ? 'bg-green-100' : 'bg-red-100' }}">
                        <span class="text-xs font-bold {{ $lunas ? 'text-green-700' : 'text-red-700' }}">
                            {{ strtoupper(substr($s->nama, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $s->nama }}</p>
                        <p class="text-xs text-gray-400">NIS {{ $s->nis }}</p>
                    </div>
                    <div class="hidden md:flex flex-wrap gap-1 justify-end flex-1">
                        @foreach($s->tagihanSiswa as $t)
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $statusBadge[$t->status] ?? '' }}">
                            <i class="fas {{ $kategoriIcon[$t->jenisTagihan->kategori]['icon'] ?? 'fa-tag' }} mr-1 {{ $kategoriIcon[$t->jenisTagihan->kategori]['color'] ?? '' }}"></i>
                            {{ \App\Models\JenisTagihan::kategoriLabel()[$t->jenisTagihan->kategori] }}
                        </span>
                        @endforeach
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        @if($lunas)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Lunas</span>
                        @else
                            <p class="text-xs font-semibold text-red-600">Rp {{ number_format($sisaSiswa, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-400">{{ $tagihanAktif->count() }} tagihan aktif</p>
                        @endif
                    </div>
                </div>

                {{-- Expand --}}
                <div x-show="open" x-cloak class="bg-gray-50 border-t border-gray-100 px-4 py-3 space-y-3">
                    @if($s->tagihanSiswa->isEmpty())
                        <p class="text-xs text-gray-500 text-center py-2">Belum ada tagihan untuk siswa ini.</p>
                    @else
                        @foreach($s->tagihanSiswa as $tagihan)
                        @php
                            $icon = $kategoriIcon[$tagihan->jenisTagihan->kategori] ?? ['icon'=>'fa-tag','color'=>'text-gray-400'];
                            $pct  = $tagihan->nominal_total > 0 ? min(100, round($tagihan->nominal_terbayar / $tagihan->nominal_total * 100)) : 0;
                        @endphp

                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">

                            {{-- Header tagihan --}}
                            <div class="flex items-center gap-3 px-3 py-2.5">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas {{ $icon['icon'] }} text-sm {{ $icon['color'] }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $tagihan->jenisTagihan->nama }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs {{ $statusBadge[$tagihan->status] ?? '' }} px-1.5 py-0.5 rounded-full">
                                            {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] }}
                                        </span>
                                        @if($tagihan->status !== 'lunas')
                                        <span class="text-xs text-gray-400">
                                            Sisa Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                            dari Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}
                                        </span>
                                        @else
                                        <span class="text-xs text-gray-400">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="w-16 flex-shrink-0">
                                    <div class="h-1.5 bg-gray-200 rounded-full">
                                        <div class="h-1.5 rounded-full {{ $tagihan->status === 'lunas' ? 'bg-green-500' : 'bg-blue-500' }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 text-right mt-0.5">{{ $pct }}%</p>
                                </div>
                                @if($tagihan->status === 'lunas')
                                <span class="flex-shrink-0 text-xs text-green-500 px-2">✓ Lunas</span>
                                @else
                                <span class="flex-shrink-0 text-xs text-red-500 px-2 font-medium">
                                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                </span>
                                @endif
                            </div>

                            {{-- Riwayat pembayaran --}}
                            @if($tagihan->pembayaran->isNotEmpty())
                            <div class="border-t border-gray-100 px-3 py-2 bg-gray-50">
                                <p class="text-xs text-gray-500 font-medium mb-1.5">Riwayat Pembayaran</p>
                                <div class="space-y-1.5">
                                    @foreach($tagihan->pembayaran as $bayar)
                                    @php $m = $metodeLabel[$bayar->metode] ?? ['label'=>$bayar->metode,'icon'=>'fa-money-bill','color'=>'text-gray-400']; @endphp
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <i class="fas {{ $m['icon'] }} {{ $m['color'] }}"></i>
                                            <span class="font-medium text-gray-700">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</span>
                                            <span class="text-gray-400">{{ \Carbon\Carbon::parse($bayar->created_at)->format('d M Y') }}</span>
                                            <span class="text-gray-400">· {{ $m['label'] }}</span>
                                            @if($bayar->status_verifikasi === 'pending')
                                            <span class="bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">Pending</span>
                                            @endif
                                        </div>
                                        @if($bayar->bukti_bayar_path)
                                        <a href="{{ route('bukti.show', $bayar->id) }}" target="_blank"
                                           class="text-blue-500 hover:text-blue-700 hover:underline flex items-center gap-1">
                                            <i class="fas fa-image"></i> Lihat Bukti
                                        </a>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endforeach

            <p class="text-center text-sm text-gray-500 py-6"
               x-show="search && !document.querySelector('#siswa-list [x-show*=matches]:not([style*=none])')">
                Siswa "<span x-text="search"></span>" tidak ditemukan.
            </p>
        </div>
    </div>
</div>
@endsection
