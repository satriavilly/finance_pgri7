@extends('layouts.app')
@section('title', 'Dashboard Siswa')
@section('page-title', 'Tagihan Saya')

@section('content')
<div class="space-y-4">
    @if(!$siswa)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
        <p class="text-yellow-700">Data siswa belum terdaftar. Hubungi wali kelas atau admin.</p>
    </div>
    @else

    @php
        $semuaTagihan = $siswa->tagihanSiswa;
        $tagihanAktif = $semuaTagihan->whereIn('status', ['belum_bayar', 'cicilan']);
        $tagihanLunas = $semuaTagihan->where('status', 'lunas');
        $sisaTotal    = $tagihanAktif->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);

        $kategoriColor = [
            'spp'       => 'bg-indigo-100 text-indigo-700',
            'kas_kelas' => 'bg-blue-100 text-blue-700',
            'buku_lks'  => 'bg-purple-100 text-purple-700',
            'kegiatan'  => 'bg-orange-100 text-orange-700',
            'seragam'   => 'bg-teal-100 text-teal-700',
            'lainnya'   => 'bg-gray-100 text-gray-600',
        ];

        // Group tagihan by tahun ajaran, aktif dulu
        $byTahunAjaran = $semuaTagihan
            ->groupBy(fn($t) => $t->jenisTagihan->kelas?->tahunAjaran?->id ?? 0)
            ->map(fn($group) => [
                'tahunAjaran' => $group->first()->jenisTagihan->kelas?->tahunAjaran,
                'tagihan'     => $group,
            ])
            ->sortByDesc(fn($g) => $g['tahunAjaran']?->tanggal_mulai ?? '');
    @endphp

    {{-- Info Siswa --}}
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-blue-700 font-bold text-lg">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-gray-800">{{ $siswa->nama }}</p>
            <p class="text-sm text-gray-500">NIS: {{ $siswa->nis }} &middot; Kelas {{ $siswa->kelas?->nama }}</p>
            @if($tahunAjaran)
            <p class="text-xs mt-0.5">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                    <i class="fas fa-circle text-green-500 text-[8px] mr-1"></i>Tahun Ajaran {{ $tahunAjaran->nama }} &mdash; Aktif
                </span>
            </p>
            @endif
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

    @if($semuaTagihan->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <i class="fas fa-receipt text-3xl mb-2 block"></i>
        <p class="text-sm">Belum ada tagihan untuk tahun ajaran ini.</p>
    </div>
    @else

    {{-- Search + Download --}}
    <div class="flex gap-3 items-center">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="search-input"
                   placeholder="Cari nama tagihan atau kategori..."
                   autocomplete="off"
                   class="w-full pl-9 pr-9 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <button type="button" id="search-clear"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <a href="{{ route('siswa.tagihan.pdf') }}"
           class="flex-shrink-0 flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg">
            <i class="fas fa-file-pdf"></i> Unduh PDF
        </a>
    </div>

    {{-- Tabel per Tahun Ajaran --}}
    @foreach($byTahunAjaran as $group)
    @php
        $ta        = $group['tahunAjaran'];
        $isAktif   = $tahunAjaran && $ta && $ta->id === $tahunAjaran->id;
        $tagihanGrp = $group['tagihan'];
    @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        {{-- Header tahun ajaran --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b bg-gray-50">
            <i class="fas fa-graduation-cap text-gray-400"></i>
            <span class="text-sm font-semibold text-gray-700">
                Tahun Ajaran {{ $ta?->nama ?? 'Tidak diketahui' }}
            </span>
            @if($isAktif)
            <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">
                <i class="fas fa-circle text-green-500 text-[8px] mr-1"></i>Aktif
            </span>
            @endif
            <span class="ml-auto text-xs text-gray-400">{{ $tagihanGrp->count() }} tagihan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b">
                    <tr class="text-xs text-gray-500">
                        <th class="text-left px-4 py-2.5 font-medium">Nama Tagihan</th>
                        <th class="text-left px-4 py-2.5 font-medium">Kategori</th>
                        <th class="text-right px-4 py-2.5 font-medium">Total</th>
                        <th class="text-right px-4 py-2.5 font-medium">Terbayar</th>
                        <th class="text-right px-4 py-2.5 font-medium">Sisa</th>
                        <th class="text-left px-4 py-2.5 font-medium">Jatuh Tempo / Tgl Bayar</th>
                        <th class="text-center px-4 py-2.5 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tagihanGrp as $t)
                    @php
                        $kat   = $t->jenisTagihan->kategori;
                        $kat_label = \App\Models\JenisTagihan::kategoriLabel()[$kat] ?? $kat;
                        $kColor = $kategoriColor[$kat] ?? 'bg-gray-100 text-gray-600';
                        $lastPay = $t->pembayaran->first();
                        $tglBayar = $lastPay ? \Carbon\Carbon::parse($lastPay->tanggal_bayar ?? $lastPay->created_at) : null;
                        $statusColor = [
                            'belum_bayar' => 'bg-red-100 text-red-700',
                            'cicilan'     => 'bg-yellow-100 text-yellow-700',
                            'lunas'       => 'bg-green-100 text-green-700',
                        ][$t->status] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <tr class="tagihan-row hover:bg-gray-50"
                        data-nama="{{ strtolower($t->jenisTagihan->nama) }}"
                        data-kategori="{{ strtolower($kat_label) }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $t->jenisTagihan->nama }}</p>
                            @if($t->jenisTagihan->is_cicilan)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $t->jenisTagihan->jumlah_cicilan }}x cicilan</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $kColor }}">{{ $kat_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700 font-medium whitespace-nowrap">
                            Rp {{ number_format($t->nominal_total, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-green-600 font-medium whitespace-nowrap">
                            Rp {{ number_format($t->nominal_terbayar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap {{ $t->sisa_tagihan > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                            Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                            @if($t->status === 'lunas' && $tglBayar)
                                <span class="text-green-600"><i class="fas fa-check mr-1"></i>{{ $tglBayar->format('d M Y') }}</span>
                            @else
                                {{ $t->due_date?->format('d M Y') ?? '-' }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2.5 py-1 rounded-full {{ $statusColor }}">
                                {{ \App\Models\TagihanSiswa::statusLabel()[$t->status] ?? $t->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
    @endforeach

    <div id="empty-result" class="hidden bg-white rounded-xl shadow-sm px-4 py-8 text-center text-gray-400 text-sm">
        Tidak ada tagihan yang cocok dengan pencarian.
    </div>

    @endif
    @endif
</div>

@push('scripts')
<script>
(function () {
    const input   = document.getElementById('search-input');
    const clear   = document.getElementById('search-clear');
    const empty   = document.getElementById('empty-result');
    const rows    = document.querySelectorAll('.tagihan-row');
    const tables  = document.querySelectorAll('.bg-white.rounded-xl.shadow-sm.overflow-hidden');

    function filter(q) {
        let totalVisible = 0;

        // Show/hide individual rows
        rows.forEach(function (row) {
            const match = !q || row.dataset.nama.includes(q) || row.dataset.kategori.includes(q);
            row.classList.toggle('hidden', !match);
            if (match) totalVisible++;
        });

        // Hide entire year-group table if all its rows are hidden
        tables.forEach(function (table) {
            const tableRows = table.querySelectorAll('.tagihan-row');
            if (tableRows.length === 0) return;
            const anyVisible = [...tableRows].some(r => !r.classList.contains('hidden'));
            table.classList.toggle('hidden', !anyVisible);
        });

        if (empty) empty.classList.toggle('hidden', totalVisible > 0);
    }

    if (input) {
        input.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            clear.classList.toggle('hidden', !q);
            filter(q);
        });
    }

    if (clear) {
        clear.addEventListener('click', function () {
            input.value = '';
            this.classList.add('hidden');
            filter('');
        });
    }
})();
</script>
@endpush
@endsection
