@extends('layouts.app')
@section('title', 'Tagihan Saya')
@section('page-title', 'Tagihan Saya')

@section('content')
@php
    $kategoriColor = [
        'spp'       => 'bg-indigo-100 text-indigo-700',
        'kas_kelas' => 'bg-blue-100 text-blue-700',
        'buku_lks'  => 'bg-purple-100 text-purple-700',
        'kegiatan'  => 'bg-orange-100 text-orange-700',
        'seragam'   => 'bg-teal-100 text-teal-700',
        'lainnya'   => 'bg-gray-100 text-gray-600',
    ];

    $byTahunAjaran = $tagihan
        ->groupBy(fn($t) => $t->jenisTagihan->kelas?->tahunAjaran?->id ?? 0)
        ->map(fn($group) => [
            'tahunAjaran' => $group->first()->jenisTagihan->kelas?->tahunAjaran,
            'tagihan'     => $group,
        ])
        ->sortByDesc(fn($g) => $g['tahunAjaran']?->tanggal_mulai ?? '');
@endphp
<div class="space-y-4">

    {{-- Info Siswa --}}
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-blue-700 font-bold">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $siswa->nama }}</p>
            <p class="text-sm text-gray-500">NIS {{ $siswa->nis }} &middot; Kelas {{ $siswa->kelas?->nama }}</p>
        </div>
        <a href="{{ route('siswa.tagihan.pdf') }}"
           class="ml-auto flex-shrink-0 flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <i class="fas fa-file-pdf"></i> Unduh PDF
        </a>
    </div>

    @if($tagihan->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-receipt text-4xl mb-3 block"></i>
        <p>Belum ada tagihan untuk tahun ajaran ini.</p>
    </div>
    @else

    {{-- Search --}}
    <div class="relative">
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

    @foreach($byTahunAjaran as $group)
    @php $ta = $group['tahunAjaran']; @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-3 border-b bg-gray-50">
            <i class="fas fa-graduation-cap text-gray-400"></i>
            <span class="text-sm font-semibold text-gray-700">Tahun Ajaran {{ $ta?->nama ?? '-' }}</span>
            @if($ta?->is_aktif)
            <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">
                <i class="fas fa-circle text-green-500 text-[8px] mr-1"></i>Aktif
            </span>
            @endif
            <span class="ml-auto text-xs text-gray-400">{{ $group['tagihan']->count() }} tagihan</span>
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
                    @foreach($group['tagihan'] as $t)
                    @php
                        $kat      = $t->jenisTagihan->kategori;
                        $katLabel = \App\Models\JenisTagihan::kategoriLabel()[$kat] ?? $kat;
                        $kColor   = $kategoriColor[$kat] ?? 'bg-gray-100 text-gray-600';
                        $lastPay  = $t->pembayaran->first();
                        $tglBayar = $lastPay ? \Carbon\Carbon::parse($lastPay->tanggal_bayar ?? $lastPay->created_at) : null;
                        $statusColor = [
                            'belum_bayar' => 'bg-red-100 text-red-700',
                            'cicilan'     => 'bg-yellow-100 text-yellow-700',
                            'lunas'       => 'bg-green-100 text-green-700',
                        ][$t->status] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <tr class="tagihan-row hover:bg-gray-50 {{ $t->status === 'lunas' ? 'bg-green-50/30' : '' }}"
                        data-nama="{{ strtolower($t->jenisTagihan->nama) }}"
                        data-kategori="{{ strtolower($katLabel) }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $t->jenisTagihan->nama }}</p>
                            @if($t->jenisTagihan->is_cicilan)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $t->jenisTagihan->jumlah_cicilan }}x cicilan</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $kColor }}">{{ $katLabel }}</span>
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
                        <td class="px-4 py-3 text-xs whitespace-nowrap">
                            @if($t->status === 'lunas' && $tglBayar)
                                <span class="text-green-600"><i class="fas fa-check mr-1"></i>{{ $tglBayar->format('d M Y') }}</span>
                            @else
                                <span class="text-gray-500">{{ $t->due_date?->format('d M Y') ?? '-' }}</span>
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
</div>

@push('scripts')
<script>
(function () {
    const input  = document.getElementById('search-input');
    const clear  = document.getElementById('search-clear');
    const empty  = document.getElementById('empty-result');
    const rows   = document.querySelectorAll('.tagihan-row');
    const tables = document.querySelectorAll('.bg-white.rounded-xl.shadow-sm.overflow-hidden');

    function filter(q) {
        let totalVisible = 0;
        rows.forEach(function (row) {
            const match = !q || row.dataset.nama.includes(q) || row.dataset.kategori.includes(q);
            row.classList.toggle('hidden', !match);
            if (match) totalVisible++;
        });
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
