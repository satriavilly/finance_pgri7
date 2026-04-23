@extends('layouts.app')
@section('title', 'Input Pembayaran')
@section('page-title', 'Input Pembayaran' . ($kelas ? ' — Kelas ' . $kelas->nama : ''))

@section('content')
<div class="space-y-4"
     x-data="{
         search: '',
         matches(nama, nis) {
             let q = this.search.toLowerCase();
             return !q || nama.toLowerCase().includes(q) || nis.toLowerCase().includes(q);
         }
     }">

    @if(!$kelas)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
        <p class="text-yellow-700">Anda belum ditugaskan sebagai wali kelas pada tahun ajaran aktif.</p>
    </div>
    @elseif($siswa->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
        <p>Belum ada siswa di Kelas {{ $kelas->nama }}.</p>
    </div>
    @else
    @php
        $totalSiswa   = $siswa->count();
        $sudahLunas   = $siswa->filter(fn($s) => $s->tagihanSiswa->whereIn('status', ['belum_bayar','cicilan'])->isEmpty())->count();
        $adaTunggakan = $totalSiswa - $sudahLunas;
    @endphp

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-4">
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
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm px-4 py-3">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" x-model="search" placeholder="Cari nama atau NIS siswa..."
                   class="w-full pl-9 pr-9 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            <button x-show="search" @click="search=''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Daftar Siswa --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-700">Kelas {{ $kelas->nama }}</p>
            <p class="text-xs text-gray-500">TA {{ $kelas->tahunAjaran->nama }}</p>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($siswa as $s)
            @php
                $tagihanAktif = $s->tagihanSiswa->whereIn('status', ['belum_bayar', 'cicilan']);
                $sisaTotal    = $tagihanAktif->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);
                $lunas        = $tagihanAktif->isEmpty();
            @endphp

            <a href="{{ route('wali-kelas.siswa.tagihan', $s->id) }}"
               x-show="matches('{{ addslashes($s->nama) }}', '{{ $s->nis }}')"
               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">

                {{-- Avatar --}}
                @if($s->user?->foto_profil)
                <img src="{{ $s->user->fotoProfilUrl() }}" alt="{{ $s->nama }}"
                     class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                @else
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                            {{ $lunas ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ strtoupper(substr($s->nama, 0, 1)) }}
                </div>
                @endif

                {{-- Nama & NIS --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $s->nama }}</p>
                    <p class="text-xs text-gray-400">NIS {{ $s->nis }}</p>
                </div>

                {{-- Status --}}
                @if($lunas)
                <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full flex-shrink-0">
                    <i class="fas fa-check mr-1"></i>Lunas
                </span>
                @else
                <div class="text-right flex-shrink-0">
                    <p class="text-xs font-semibold text-red-600">Rp {{ number_format($sisaTotal, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">{{ $tagihanAktif->count() }} belum lunas</p>
                </div>
                @endif

                <i class="fas fa-chevron-right text-gray-300 text-xs flex-shrink-0"></i>
            </a>
            @endforeach

            {{-- Empty search --}}
            <p class="text-center text-sm text-gray-400 py-6 hidden"
               id="empty-search-msg"
               x-show="search !== '' && document.querySelectorAll('[x-show].divide-y > a:not([style*=\'display: none\'])').length === 0">
                Siswa "<span x-text="search"></span>" tidak ditemukan.
            </p>
        </div>
    </div>
    @endif
</div>
@endsection
