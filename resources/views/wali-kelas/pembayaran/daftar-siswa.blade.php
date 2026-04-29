@extends('layouts.app')
@section('title', 'Input Pembayaran')
@section('page-title', 'Input Pembayaran' . ($kelas ? ' — Kelas ' . $kelas->nama : ''))

@section('content')
<div class="space-y-5"
     x-data="{
         search: '',
         filter: 'semua',
         matches(nama, nis, status) {
             let q = this.search.toLowerCase();
             let textOk = !q || nama.toLowerCase().includes(q) || nis.toLowerCase().includes(q);
             let filterOk = this.filter === 'semua'
                          || (this.filter === 'lunas' && status === 'lunas')
                          || (this.filter === 'nunggak' && status === 'nunggak');
             return textOk && filterOk;
         }
     }">

    {{-- Tahun Ajaran --}}
    <div class="flex items-center justify-between">
        @include('layouts.partials.tahun-ajaran-select', [
            'allTahunAjaran' => $allTahunAjaran,
            'selectedTa'     => $selectedTa,
            'taRoute'        => 'wali-kelas.siswa.index',
        ])
    </div>

    @if(!$kelas)
    <div class="bg-yellow-50 border border-yellow-300 rounded-2xl p-6 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
        <p class="text-yellow-800 text-sm">Anda belum ditugaskan sebagai wali kelas pada tahun ajaran aktif.</p>
    </div>
    @elseif($siswa->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
        <i class="fas fa-users text-5xl mb-4 block text-gray-200"></i>
        <p class="text-base font-medium">Belum ada siswa di Kelas {{ $kelas->nama }}.</p>
    </div>
    @else
    @php
        $totalSiswa   = $siswa->count();
        $sudahLunas   = $siswa->filter(fn($s) => $s->tagihanSiswa->whereIn('status', ['belum_bayar','cicilan'])->isEmpty())->count();
        $adaTunggakan = $totalSiswa - $sudahLunas;
        $totalSisa    = $siswa->sum(fn($s) => $s->tagihanSiswa->whereIn('status',['belum_bayar','cicilan'])->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar));
        $pctLunas     = $totalSiswa > 0 ? round($sudahLunas / $totalSiswa * 100) : 0;
    @endphp

    {{-- Header kelas --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl px-6 py-5 flex items-center gap-4 text-white shadow-lg">
        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-chalkboard-teacher text-2xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-blue-200 text-xs uppercase tracking-widest font-semibold">Wali Kelas</p>
            <p class="text-2xl font-extrabold leading-tight">Kelas {{ $kelas->nama }}</p>
            <p class="text-blue-200 text-xs mt-0.5">Tahun Ajaran {{ $kelas->tahunAjaran->nama }}</p>
        </div>
        <div class="hidden sm:flex flex-col items-end gap-1">
            <div class="bg-white/20 rounded-xl px-4 py-2 text-center">
                <p class="text-2xl font-extrabold">{{ $pctLunas }}%</p>
                <p class="text-blue-200 text-xs">siswa lunas</p>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3 border-l-4 border-blue-500">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-users text-blue-600"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-gray-800">{{ $totalSiswa }}</p>
                <p class="text-xs text-gray-500 font-medium">Total Siswa</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3 border-l-4 border-green-500">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-green-700">{{ $sudahLunas }}</p>
                <p class="text-xs text-gray-500 font-medium">Lunas Semua</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3 border-l-4 border-red-500">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-red-600">{{ $adaTunggakan }}</p>
                <p class="text-xs text-gray-500 font-medium">Ada Tunggakan</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3 border-l-4 border-orange-400">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-money-bill-wave text-orange-500"></i>
            </div>
            <div>
                <p class="text-sm font-extrabold text-orange-700 leading-snug">Rp {{ number_format($totalSisa, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 font-medium">Total Tunggakan</p>
            </div>
        </div>
    </div>

    {{-- Progress bar kelas --}}
    <div class="bg-white rounded-2xl shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Progress Pelunasan Kelas</p>
            <p class="text-xs font-bold text-blue-700">{{ $sudahLunas }} dari {{ $totalSiswa }} siswa lunas</p>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
            <div class="h-2.5 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 transition-all duration-700"
                 style="width: {{ $pctLunas }}%"></div>
        </div>
    </div>

    {{-- Search + filter --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 bg-white rounded-xl shadow-sm px-4 py-2.5 flex items-center gap-2 border border-transparent focus-within:border-blue-400 transition-colors">
            <i class="fas fa-search text-gray-400 text-sm flex-shrink-0"></i>
            <input type="text" x-model="search" placeholder="Cari nama atau NIS siswa..."
                   class="flex-1 text-sm outline-none bg-transparent placeholder-gray-400">
            <button x-show="search" @click="search=''" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="flex gap-1.5 bg-white rounded-xl shadow-sm p-1.5 flex-shrink-0">
            <button @click="filter='semua'"
                    :class="filter==='semua' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-100'"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all">
                Semua
            </button>
            <button @click="filter='lunas'"
                    :class="filter==='lunas' ? 'bg-green-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-100'"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all">
                <i class="fas fa-check mr-1"></i>Lunas
            </button>
            <button @click="filter='nunggak'"
                    :class="filter==='nunggak' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-100'"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all">
                <i class="fas fa-exclamation mr-1"></i>Tunggakan
            </button>
        </div>
    </div>

    {{-- Tabel Siswa --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gradient-to-r from-blue-700 to-blue-600">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-blue-100 uppercase tracking-wide w-10">#</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-blue-100 uppercase tracking-wide">Siswa</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-blue-100 uppercase tracking-wide hidden sm:table-cell">Pelunasan</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-blue-100 uppercase tracking-wide hidden md:table-cell">Sisa Tagihan</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-blue-100 uppercase tracking-wide">Status</th>
                    <th class="px-3 py-3.5 w-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($siswa as $index => $s)
                @php
                    $tagihanAktif = $s->tagihanSiswa->whereIn('status', ['belum_bayar', 'cicilan']);
                    $sisaTotal    = $tagihanAktif->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);
                    $lunas        = $tagihanAktif->isEmpty();
                    $totalTs      = $s->tagihanSiswa->sum('nominal_total');
                    $terbayar     = $s->tagihanSiswa->sum('nominal_terbayar');
                    $pct          = $totalTs > 0 ? min(100, round($terbayar / $totalTs * 100)) : 100;
                    $statusVal    = $lunas ? 'lunas' : 'nunggak';
                    $initials     = collect(explode(' ', $s->nama))->take(2)->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
                    $avatarColors = ['bg-blue-500','bg-purple-500','bg-pink-500','bg-indigo-500','bg-teal-500','bg-cyan-500'];
                    $avatarColor  = $lunas ? 'bg-gradient-to-br from-green-400 to-emerald-500' : 'bg-gradient-to-br from-blue-400 to-blue-600';
                @endphp
                <tr x-show="matches('{{ addslashes($s->nama) }}', '{{ $s->nis }}', '{{ $statusVal }}')"
                    class="hover:bg-blue-50/40 transition-colors cursor-pointer group"
                    onclick="window.location='{{ route('wali-kelas.siswa.tagihan', $s->id) }}'">

                    {{-- No --}}
                    <td class="px-5 py-3.5 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>

                    {{-- Siswa --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if($s->user?->foto_profil)
                            <img src="{{ $s->user->fotoProfilUrl() }}" alt="{{ $s->nama }}"
                                 class="w-9 h-9 rounded-xl object-cover flex-shrink-0 shadow-sm ring-2 ring-white">
                            @else
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-xs font-bold shadow-sm {{ $avatarColor }}">
                                {{ $initials }}
                            </div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">{{ $s->nama }}</p>
                                <p class="text-xs text-gray-400">NIS {{ $s->nis }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Progress --}}
                    <td class="px-5 py-3.5 hidden sm:table-cell">
                        <div class="flex items-center gap-2 min-w-[120px]">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all {{ $lunas ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gradient-to-r from-orange-400 to-orange-500' }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs font-bold {{ $lunas ? 'text-green-600' : 'text-orange-600' }} w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>

                    {{-- Sisa --}}
                    <td class="px-5 py-3.5 text-right hidden md:table-cell">
                        @if($lunas)
                        <span class="text-xs text-green-600 font-medium">—</span>
                        @else
                        <div>
                            <p class="text-sm font-extrabold text-red-600">Rp {{ number_format($sisaTotal, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-400">{{ $tagihanAktif->count() }} tagihan</p>
                        </div>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-3.5 text-center">
                        @if($lunas)
                        <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                            <i class="fas fa-check-circle text-xs"></i>
                            <span class="hidden sm:inline">Lunas</span>
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-full">
                            <i class="fas fa-clock text-xs"></i>
                            <span class="hidden sm:inline">Belum Lunas</span>
                        </span>
                        @endif
                    </td>

                    {{-- Chevron --}}
                    <td class="px-3 py-3.5 text-center">
                        <i class="fas fa-chevron-right text-gray-300 text-xs group-hover:text-blue-400 transition-colors"></i>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Empty state filter --}}
        <div class="py-10 text-center text-gray-400 text-sm"
             x-show="[...document.querySelectorAll('tbody tr')].filter(r => r.style.display !== 'none').length === 0"
             x-cloak>
            <i class="fas fa-search text-3xl mb-3 block text-gray-200"></i>
            Tidak ada siswa yang sesuai filter.
        </div>
    </div>

    @endif
</div>
@endsection
