@extends('layouts.app')
@section('title', 'Monitoring Tunggakan')
@section('page-title', 'Monitoring Siswa Menunggak')

@php
$statusLabel = ['belum_bayar' => 'Belum Bayar', 'cicilan' => 'Cicilan'];
$statusColor = [
    'belum_bayar' => 'bg-red-100 text-red-700',
    'cicilan'     => 'bg-yellow-100 text-yellow-700',
];
$kategoriWarna = \App\Models\KategoriTagihan::orderBy('urutan')->pluck('warna', 'kode')->toArray();
@endphp

@section('content')
<div class="space-y-4">

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-400">Siswa Menunggak</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['siswa_nunggak'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-xs text-gray-400">Total Tunggakan</p>
            <p class="text-base font-bold text-orange-600">Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-400">
            <p class="text-xs text-gray-400">Total Tagihan</p>
            <p class="text-2xl font-bold text-gray-700">{{ $summary['jumlah_tagihan'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-400">
            <p class="text-xs text-gray-400">Belum Bayar</p>
            <p class="text-2xl font-bold text-red-500">{{ $summary['jumlah_belum_bayar'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
            <p class="text-xs text-gray-400">Cicilan Berjalan</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $summary['jumlah_cicilan'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('bendahara.tunggakan.index') }}" class="flex flex-wrap gap-3 items-end">

            {{-- Tahun Ajaran --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Tahun Ajaran</label>
                <select name="ta" id="ta-select"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach($allTahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ $selectedTa?->id == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }}{{ $ta->is_aktif ? ' ★' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Kelas --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Kelas</label>
                <select name="kelas_id" id="kelas-select"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Kategori --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Kategori Tagihan</label>
                <select name="kategori"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                    <option value="{{ $kat->kode }}" {{ request('kategori') == $kat->kode ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Search --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Cari Siswa</label>
                <input type="text" name="cari" value="{{ request('cari') }}"
                       placeholder="Nama atau NIS..."
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-44">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium flex items-center gap-1.5">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
                @if(request()->hasAny(['kelas_id','kategori','cari']))
                <a href="{{ route('bendahara.tunggakan.index', ['ta' => $selectedTa?->id]) }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm flex items-center gap-1.5">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Data --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 text-sm">
                Daftar Siswa Menunggak
                @if($selectedTa)
                <span class="ml-2 text-xs font-normal text-gray-400">— {{ $selectedTa->nama }}</span>
                @endif
            </h2>
            <span class="text-xs text-gray-400">{{ $siswa->total() }} siswa</span>
        </div>

        @if($siswa->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-check-circle text-5xl mb-3 block text-green-300"></i>
            <p class="text-sm font-medium text-gray-500">Tidak ada siswa yang menunggak</p>
            <p class="text-xs text-gray-400 mt-1">
                @if(request()->hasAny(['kelas_id','kategori','cari']))
                dengan filter yang dipilih
                @else
                di tahun ajaran ini
                @endif
            </p>
        </div>
        @else
        <div class="divide-y divide-gray-100" x-data="{ open: null }">
            @foreach($siswa as $s)
            @php
                $totalTunggakan = $s->tagihanSiswa->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);
                $kelasNama = $s->tagihanSiswa->first()?->jenisTagihan?->kelas?->nama ?? $s->kelas?->nama ?? '-';
                $idx = $s->id;
            @endphp
            <div>
                {{-- Student row --}}
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 cursor-pointer"
                     @click="open = open === {{ $idx }} ? null : {{ $idx }}">

                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-red-700 font-bold text-sm">{{ strtoupper(substr($s->nama, 0, 1)) }}</span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $s->nama }}</p>
                        <p class="text-xs text-gray-400">NIS: {{ $s->nis }} · {{ $kelasNama }}</p>
                    </div>

                    {{-- Tagihan badges --}}
                    <div class="hidden md:flex flex-wrap gap-1 max-w-xs">
                        @foreach($s->tagihanSiswa as $t)
                        <span class="inline-block text-[10px] px-1.5 py-0.5 rounded-full {{ $statusColor[$t->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $t->jenisTagihan?->nama }}
                        </span>
                        @endforeach
                    </div>

                    {{-- Jumlah & total --}}
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-red-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400">{{ $s->tagihanSiswa->count() }} tagihan</p>
                    </div>

                    {{-- Chevron --}}
                    <i class="fas fa-chevron-down text-gray-400 text-xs ml-1 transition-transform duration-200"
                       :class="open === {{ $idx }} ? 'rotate-180' : ''"></i>
                </div>

                {{-- Expanded detail --}}
                <div x-show="open === {{ $idx }}" x-cloak
                     class="bg-gray-50 border-t border-gray-100 px-5 py-3">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-400 uppercase">
                                <th class="text-left pb-2 font-medium">Tagihan</th>
                                <th class="text-left pb-2 font-medium">Kategori</th>
                                <th class="text-right pb-2 font-medium">Total</th>
                                <th class="text-right pb-2 font-medium">Terbayar</th>
                                <th class="text-right pb-2 font-medium">Sisa</th>
                                <th class="text-center pb-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($s->tagihanSiswa as $t)
                            @php $sisa = $t->nominal_total - $t->nominal_terbayar; @endphp
                            <tr>
                                <td class="py-1.5 pr-3">
                                    <p class="font-medium text-gray-700">{{ $t->jenisTagihan?->nama ?? '-' }}</p>
                                    @if($t->jenisTagihan?->due_date)
                                    <p class="text-gray-400 text-[10px]">Jatuh tempo: {{ $t->jenisTagihan->due_date->format('d M Y') }}</p>
                                    @endif
                                </td>
                                <td class="py-1.5 pr-3">
                                    @php
                                        $kode = $t->jenisTagihan?->kategori ?? '';
                                        $warna = $kategoriWarna[$kode] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="inline-block px-1.5 py-0.5 rounded-full text-[10px] {{ $warna }}">
                                        {{ \App\Models\JenisTagihan::kategoriLabel()[$kode] ?? $kode }}
                                    </span>
                                </td>
                                <td class="py-1.5 text-right text-gray-600">Rp {{ number_format($t->nominal_total, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-right text-green-600">Rp {{ number_format($t->nominal_terbayar, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-right font-semibold text-red-600">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full {{ $statusColor[$t->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabel[$t->status] ?? $t->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-gray-200">
                                <td colspan="4" class="pt-2 text-right font-medium text-gray-500">Total sisa tunggakan:</td>
                                <td class="pt-2 text-right font-bold text-red-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($siswa->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $siswa->withQueryString()->links() }}
        </div>
        @endif
        @endif
    </div>

</div>

@push('scripts')
<script>
document.getElementById('ta-select')?.addEventListener('change', function () {
    document.getElementById('kelas-select').value = '';
    this.closest('form').submit();
});
</script>
@endpush
@endsection
