@extends('layouts.app')
@section('title', 'Tagihan ' . $siswa->nama)
@section('page-title', $siswa->nama)

@section('content')
@php
    $tagihanList  = $siswa->tagihanSiswa;
    $tagihanAktif = $tagihanList->whereIn('status', ['belum_bayar', 'cicilan']);
    $totalNominal = $tagihanList->sum('nominal_total');
    $totalBayar   = $tagihanList->sum('nominal_terbayar');
    $totalSisa    = $tagihanAktif->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);
@endphp
<div class="space-y-4">

    {{-- Back --}}
    <a href="{{ route('wali-kelas.siswa.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
        <i class="fas fa-arrow-left text-xs"></i> Kembali ke daftar siswa
    </a>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Profil siswa --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            @if($siswa->user?->foto_profil)
            <img src="{{ $siswa->user->fotoProfilUrl() }}" class="w-12 h-12 rounded-full object-cover">
            @else
            <span class="text-blue-700 font-bold text-lg">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
            @endif
        </div>
        <div class="flex-1">
            <p class="font-semibold text-gray-800 text-base">{{ $siswa->nama }}</p>
            <p class="text-sm text-gray-500">NIS {{ $siswa->nis }} · Kelas {{ $siswa->kelas?->nama }}</p>
        </div>
        @if($tagihanAktif->isEmpty())
        <span class="bg-green-100 text-green-700 text-sm font-medium px-3 py-1.5 rounded-full">
            <i class="fas fa-check mr-1"></i>Semua Lunas
        </span>
        @else
        <div class="text-right">
            <p class="text-base font-bold text-red-600">Rp {{ number_format($totalSisa, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">total tunggakan</p>
        </div>
        @endif
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Tagihan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalBayar, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Sudah Terbayar</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xl font-bold text-red-500">Rp {{ number_format($totalSisa, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Sisa Tunggakan</p>
        </div>
    </div>

    {{-- Daftar Tagihan --}}
    @if($tagihanList->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <i class="fas fa-file-invoice-dollar text-4xl mb-3 block"></i>
        <p>Belum ada tagihan untuk siswa ini.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($tagihanList as $tagihan)
        @php
            $pct = $tagihan->nominal_total > 0
                ? min(100, round($tagihan->nominal_terbayar / $tagihan->nominal_total * 100))
                : 0;
            $statusColor = [
                'belum_bayar' => 'bg-red-100 text-red-700',
                'cicilan'     => 'bg-yellow-100 text-yellow-700',
                'lunas'       => 'bg-green-100 text-green-700',
                'void'        => 'bg-gray-100 text-gray-400',
            ][$tagihan->status] ?? 'bg-gray-100 text-gray-500';
        @endphp

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">

            {{-- Header tagihan --}}
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ \App\Models\JenisTagihan::kategoriLabel()[$tagihan->jenisTagihan->kategori] ?? '-' }}
                            @if($tagihan->due_date)
                            · Jatuh tempo {{ $tagihan->due_date->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full flex-shrink-0 {{ $statusColor }}">
                        {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] ?? $tagihan->status }}
                    </span>
                </div>

                {{-- Nominal row --}}
                <div class="grid grid-cols-3 gap-3 mt-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Total</p>
                        <p class="font-medium text-gray-800">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Terbayar</p>
                        <p class="font-medium text-green-600">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Sisa</p>
                        <p class="font-medium {{ $tagihan->sisa_tagihan > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                {{-- Progress bar --}}
                @if($pct > 0)
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Progress</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full">
                        <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' }} transition-all"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endif

                {{-- Aksi --}}
                @if($tagihan->status !== 'lunas' && $tagihan->status !== 'void')
                <div class="mt-4">
                    <a href="{{ route('wali-kelas.pembayaran.form-tunai', $tagihan->id) }}"
                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        <i class="fas fa-cash-register"></i> Catat Pembayaran
                    </a>
                </div>
                @endif
            </div>

            {{-- Riwayat pembayaran --}}
            @if($tagihan->pembayaran->isNotEmpty())
            <div class="border-t border-gray-100" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-5 py-3 text-xs text-gray-500 hover:bg-gray-50">
                    <span><i class="fas fa-history mr-2 text-gray-400"></i>Riwayat Pembayaran ({{ $tagihan->pembayaran->count() }})</span>
                    <i class="fas fa-chevron-down text-gray-300 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak class="px-5 pb-4 space-y-3">
                    @foreach($tagihan->pembayaran as $bayar)
                    <div x-data="{ showVoidForm: false }" class="border border-gray-100 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 text-sm flex-wrap">
                                <span class="font-semibold text-gray-800">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</span>
                                <span class="text-gray-400">{{ \Carbon\Carbon::parse($bayar->tanggal_bayar ?? $bayar->created_at)->format('d M Y') }}</span>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                    {{ \App\Models\Pembayaran::metodeLabel()[$bayar->metode] ?? $bayar->metode }}
                                </span>
                                @if($bayar->bukti_bayar_path)
                                <a href="{{ route('bukti.show', $bayar->id) }}" target="_blank"
                                   class="text-xs text-blue-500 hover:underline">
                                    <i class="fas fa-image mr-1"></i>Lihat Bukti
                                </a>
                                @endif
                            </div>
                            @if(!$bayar->is_void)
                            <button type="button" @click="showVoidForm = !showVoidForm"
                                    class="text-xs text-red-400 hover:text-red-600 flex-shrink-0 ml-2">
                                <i class="fas fa-undo-alt mr-1"></i>Batalkan
                            </button>
                            @else
                            <span class="text-xs text-gray-400 italic flex-shrink-0 ml-2">Dibatalkan</span>
                            @endif
                        </div>

                        {{-- Form void --}}
                        <div x-show="showVoidForm" x-cloak class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                            <p class="text-xs text-red-700 font-medium mb-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Batalkan pembayaran Rp {{ number_format($bayar->nominal, 0, ',', '.') }}?
                            </p>
                            <form method="POST" action="{{ route('wali-kelas.pembayaran.void', $bayar->id) }}"
                                  class="flex gap-2">
                                @csrf
                                <input type="text" name="catatan_void" required
                                       placeholder="Alasan pembatalan (wajib)"
                                       class="flex-1 border border-red-300 rounded-lg px-3 py-1.5 text-xs outline-none focus:ring-1 focus:ring-red-400">
                                <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-1.5 rounded-lg flex-shrink-0">
                                    Konfirmasi
                                </button>
                                <button type="button" @click="showVoidForm = false"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs px-3 py-1.5 rounded-lg flex-shrink-0">
                                    Batal
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
