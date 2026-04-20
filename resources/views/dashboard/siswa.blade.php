@extends('layouts.app')
@section('title', 'Dashboard Siswa')
@section('page-title', 'Tagihan Saya')

@php
$kategoriIcon = [
    'kas_kelas' => ['icon' => 'fa-wallet',        'color' => 'text-blue-500',   'bg' => 'bg-blue-100'],
    'buku_lks'  => ['icon' => 'fa-book',           'color' => 'text-purple-500', 'bg' => 'bg-purple-100'],
    'kegiatan'  => ['icon' => 'fa-calendar-check', 'color' => 'text-orange-500', 'bg' => 'bg-orange-100'],
    'seragam'   => ['icon' => 'fa-tshirt',         'color' => 'text-teal-500',   'bg' => 'bg-teal-100'],
    'lainnya'   => ['icon' => 'fa-tag',            'color' => 'text-gray-500',   'bg' => 'bg-gray-100'],
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
<div class="space-y-4">
    @if(!$siswa)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
            <p class="text-yellow-700">Data siswa belum terdaftar. Hubungi wali kelas atau admin.</p>
        </div>
    @else

    @php
        $semuaTagihan  = $siswa->tagihanSiswa;
        $tagihanAktif  = $semuaTagihan->whereIn('status', ['belum_bayar', 'cicilan']);
        $tagihanLunas  = $semuaTagihan->where('status', 'lunas');
        $sisaTotal     = $tagihanAktif->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);
    @endphp

    {{-- Info Siswa --}}
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-blue-700 font-bold text-lg">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-gray-800">{{ $siswa->nama }}</p>
            <p class="text-sm text-gray-500">NIS: {{ $siswa->nis }} &middot; Kelas {{ $siswa->kelas?->nama }}</p>
        </div>
        @if($tagihanAktif->isNotEmpty())
        <div class="text-right flex-shrink-0">
            <p class="text-xs text-gray-400">Total tunggakan</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($sisaTotal, 0, ',', '.') }}</p>
        </div>
        @else
        <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-medium flex-shrink-0">
            <i class="fas fa-check-circle mr-1"></i>Lunas Semua
        </span>
        @endif
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $semuaTagihan->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Tagihan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $tagihanAktif->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Belum Lunas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $tagihanLunas->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Sudah Lunas</p>
        </div>
    </div>

    {{-- Daftar Tagihan --}}
    @if($semuaTagihan->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <i class="fas fa-receipt text-3xl mb-2"></i>
        <p class="text-sm">Belum ada tagihan untuk tahun ajaran ini.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($semuaTagihan as $tagihan)
        @php
            $icon = $kategoriIcon[$tagihan->jenisTagihan->kategori] ?? ['icon'=>'fa-tag','color'=>'text-gray-500','bg'=>'bg-gray-100'];
            $pct  = $tagihan->nominal_total > 0 ? min(100, round($tagihan->nominal_terbayar / $tagihan->nominal_total * 100)) : 0;
            $riwayat = $tagihan->pembayaran;
        @endphp

        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm overflow-hidden">

            {{-- Header tagihan --}}
            <div class="flex items-center gap-3 px-4 py-3.5 cursor-pointer hover:bg-gray-50 select-none"
                 @click="open = !open">
                <div class="text-gray-400 w-4 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-90' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>

                <div class="w-9 h-9 rounded-full {{ $icon['bg'] }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $icon['icon'] }} text-sm {{ $icon['color'] }}"></i>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs px-1.5 py-0.5 rounded-full {{ $statusBadge[$tagihan->status] ?? '' }}">
                            {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ \App\Models\JenisTagihan::kategoriLabel()[$tagihan->jenisTagihan->kategori] }}
                        </span>
                    </div>
                </div>

                {{-- Progress + nominal --}}
                <div class="hidden sm:flex flex-col items-end gap-1 flex-shrink-0 w-28">
                    <div class="w-full h-1.5 bg-gray-200 rounded-full">
                        <div class="h-1.5 rounded-full {{ $tagihan->status === 'lunas' ? 'bg-green-500' : 'bg-blue-500' }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400">{{ $pct }}%</p>
                </div>

                <div class="text-right flex-shrink-0 ml-2">
                    @if($tagihan->status === 'lunas')
                        <span class="text-xs text-green-500">✓ Lunas</span>
                    @else
                        <p class="text-xs font-semibold text-red-600">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400">dari Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>

            {{-- Detail expand --}}
            <div x-show="open" x-cloak class="border-t border-gray-100 bg-gray-50 px-4 py-3 space-y-3">

                {{-- Ringkasan nominal --}}
                <div class="grid grid-cols-3 gap-3 text-xs bg-white rounded-lg border border-gray-100 p-3">
                    <div class="text-center">
                        <p class="text-gray-400 mb-0.5">Total</p>
                        <p class="font-semibold text-gray-700">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center border-x border-gray-100">
                        <p class="text-gray-400 mb-0.5">Terbayar</p>
                        <p class="font-semibold text-green-600">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-400 mb-0.5">Sisa</p>
                        <p class="font-semibold {{ $tagihan->status === 'lunas' ? 'text-gray-400' : 'text-red-600' }}">
                            Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                {{-- Tombol upload --}}
                @if($tagihan->status !== 'lunas')
                <a href="{{ route('siswa.tagihan.upload', $tagihan->id) }}"
                   class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg">
                    <i class="fas fa-upload"></i> Upload Bukti Bayar
                </a>
                @endif

                {{-- Riwayat pembayaran --}}
                @if($riwayat->isNotEmpty())
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-2">Riwayat Pembayaran</p>
                    <div class="space-y-1.5">
                        @foreach($riwayat as $bayar)
                        @php $m = $metodeLabel[$bayar->metode] ?? ['label'=>$bayar->metode,'icon'=>'fa-money-bill','color'=>'text-gray-400']; @endphp
                        <div class="flex items-center justify-between text-xs bg-white rounded-lg border border-gray-100 px-3 py-2">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $m['icon'] }} {{ $m['color'] }}"></i>
                                <span class="font-medium text-gray-700">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</span>
                                <span class="text-gray-400">{{ \Carbon\Carbon::parse($bayar->created_at)->format('d M Y') }}</span>
                                <span class="text-gray-400">&middot; {{ $m['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($bayar->status_verifikasi === 'pending')
                                    <span class="bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">Menunggu verifikasi</span>
                                @elseif($bayar->status_verifikasi === 'rejected')
                                    <span class="bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">Ditolak</span>
                                @endif
                                @if($bayar->bukti_bayar_path)
                                <a href="{{ route('bukti.show', $bayar->id) }}" target="_blank"
                                   class="text-blue-500 hover:text-blue-700 hover:underline flex items-center gap-1">
                                    <i class="fas fa-image"></i> Lihat Bukti
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-xs text-gray-400 text-center py-1">Belum ada riwayat pembayaran.</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @endif
</div>
@endsection
