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

    $kategoriColor = [
        'spp'       => 'bg-indigo-100 text-indigo-700',
        'kas_kelas' => 'bg-cyan-100 text-cyan-700',
        'buku_lks'  => 'bg-purple-100 text-purple-700',
        'kegiatan'  => 'bg-orange-100 text-orange-700',
        'seragam'   => 'bg-teal-100 text-teal-700',
        'lainnya'   => 'bg-gray-100 text-gray-600',
    ];
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
            <p class="text-sm text-gray-500">NIS {{ $siswa->nis }} &middot; Kelas {{ $siswa->kelas?->nama }}</p>
        </div>
        @if($tagihanAktif->isEmpty())
        <span class="bg-green-100 text-green-700 text-sm font-medium px-3 py-1.5 rounded-full flex-shrink-0">
            <i class="fas fa-check mr-1"></i>Semua Lunas
        </span>
        @else
        <div class="text-right flex-shrink-0">
            <p class="text-base font-bold text-red-600">Rp {{ number_format($totalSisa, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">total tunggakan</p>
        </div>
        @endif
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Tagihan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($totalBayar, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Sudah Terbayar</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-red-500">Rp {{ number_format($totalSisa, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Sisa Tunggakan</p>
        </div>
    </div>

    @if($tagihanList->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <i class="fas fa-file-invoice-dollar text-4xl mb-3 block"></i>
        <p>Belum ada tagihan untuk siswa ini.</p>
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

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-xs text-gray-500 font-medium">
                    <th class="text-left px-4 py-3">Nama Tagihan</th>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-right px-4 py-3">Terbayar</th>
                    <th class="text-right px-4 py-3">Sisa</th>
                    <th class="text-left px-4 py-3">Jatuh Tempo</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>

            {{-- Satu <tbody> per tagihan agar Alpine scope bekerja & search bisa sembunyikan group --}}
            @foreach($tagihanList as $tagihan)
            @php
                $kat      = $tagihan->jenisTagihan->kategori;
                $katLabel = \App\Models\JenisTagihan::kategoriLabel()[$kat] ?? $kat;
                $kColor   = $kategoriColor[$kat] ?? 'bg-gray-100 text-gray-600';
                $rowBg    = match($tagihan->status) {
                    'lunas'   => 'bg-green-50 hover:bg-green-100',
                    'cicilan' => 'bg-yellow-50 hover:bg-yellow-100',
                    default   => 'bg-white hover:bg-gray-50',
                };
                $statusColor = [
                    'belum_bayar' => 'bg-red-100 text-red-700',
                    'cicilan'     => 'bg-yellow-100 text-yellow-700',
                    'lunas'       => 'bg-green-100 text-green-700',
                    'void'        => 'bg-gray-100 text-gray-400',
                ][$tagihan->status] ?? 'bg-gray-100 text-gray-500';
                $hasPembayaran = $tagihan->pembayaran->isNotEmpty();
            @endphp

            <tbody x-data="{ open: false }" class="tagihan-group divide-y divide-gray-100"
                   data-nama="{{ strtolower($tagihan->jenisTagihan->nama) }}"
                   data-kategori="{{ strtolower($katLabel) }}">

                {{-- Baris utama --}}
                <tr class="tagihan-row {{ $rowBg }}">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
                        @if($tagihan->jenisTagihan->is_cicilan)
                        <span class="inline-flex items-center gap-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full mt-1">
                            <i class="fas fa-layer-group text-[9px]"></i>
                            Bisa dicicil &bull; {{ $tagihan->jenisTagihan->jumlah_cicilan }}x
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full mt-1">
                            <i class="fas fa-bolt text-[9px]"></i>
                            Lunas sekaligus
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $kColor }}">{{ $katLabel }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-700 whitespace-nowrap">
                        Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-green-600 whitespace-nowrap">
                        Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap {{ $tagihan->sisa_tagihan > 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">
                        Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $tagihan->due_date?->format('d M Y') ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2.5 py-1 rounded-full {{ $statusColor }}">
                            {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] ?? $tagihan->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            @if($hasPembayaran)
                            <button type="button" @click="open = !open"
                                    class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1">
                                <i class="fas fa-history text-[10px]"></i>
                                Riwayat ({{ $tagihan->pembayaran->count() }})
                                <i class="fas fa-chevron-down text-[9px] transition-transform duration-150" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            @endif
                            @if(!in_array($tagihan->status, ['lunas', 'void']))
                            <a href="{{ route('wali-kelas.pembayaran.form-tunai', $tagihan->id) }}"
                               class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg font-medium whitespace-nowrap">
                                <i class="fas fa-plus mr-1"></i>Catat Bayar
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Baris riwayat pembayaran (expandable) --}}
                @if($hasPembayaran)
                <tr x-show="open" x-cloak>
                    <td colspan="8" class="px-4 pt-0 pb-3">
                        <div class="border border-gray-200 rounded-lg overflow-hidden mt-1">
                            <div class="bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-500 border-b">
                                <i class="fas fa-history mr-1.5"></i>Riwayat Pembayaran
                            </div>
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr class="text-gray-400 border-b bg-white">
                                        <th class="text-left px-3 py-2 font-medium">Tanggal</th>
                                        <th class="text-right px-3 py-2 font-medium">Nominal</th>
                                        <th class="text-left px-3 py-2 font-medium">Metode</th>
                                        <th class="text-left px-3 py-2 font-medium">Bukti</th>
                                        <th class="text-right px-3 py-2 font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($tagihan->pembayaran as $bayar)
                                    <tbody x-data="{ showVoid: false }">
                                        <tr class="{{ $bayar->is_void ? 'opacity-50' : '' }} hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-600">
                                                {{ \Carbon\Carbon::parse($bayar->tanggal_bayar ?? $bayar->created_at)->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold text-gray-800">
                                                Rp {{ number_format($bayar->nominal, 0, ',', '.') }}
                                                @if($bayar->is_void)
                                                <span class="text-gray-400 font-normal">(dibatalkan)</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-gray-500">
                                                {{ \App\Models\Pembayaran::metodeLabel()[$bayar->metode] ?? $bayar->metode }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @if($bayar->bukti_bayar_path)
                                                <a href="{{ route('bukti.show', $bayar->id) }}" target="_blank"
                                                   class="text-blue-500 hover:underline flex items-center gap-1">
                                                    <i class="fas fa-image"></i> Lihat
                                                </a>
                                                @else
                                                <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                @if(!$bayar->is_void)
                                                <button type="button" @click="showVoid = !showVoid"
                                                        class="text-red-400 hover:text-red-600">
                                                    <i class="fas fa-undo-alt"></i> Batalkan
                                                </button>
                                                @else
                                                <span class="text-gray-400 italic">Dibatalkan</span>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- Form void --}}
                                        <tr x-show="showVoid" x-cloak>
                                            <td colspan="5" class="px-3 pb-2">
                                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
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
                                                        <button type="button" @click="showVoid = false"
                                                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs px-3 py-1.5 rounded-lg flex-shrink-0">
                                                            Batal
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif

            </tbody>
            @endforeach

        </table>

        <div id="empty-result" class="hidden px-4 py-8 text-center text-gray-400 text-sm border-t">
            Tidak ada tagihan yang cocok dengan pencarian.
        </div>
    </div>

    @endif
</div>

@push('scripts')
<script>
(function () {
    const input  = document.getElementById('search-input');
    const clear  = document.getElementById('search-clear');
    const empty  = document.getElementById('empty-result');
    const groups = document.querySelectorAll('.tagihan-group');

    function filter(q) {
        let visible = 0;
        groups.forEach(function (group) {
            const match = !q
                || group.dataset.nama.includes(q)
                || group.dataset.kategori.includes(q);
            group.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible > 0);
    }

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        clear.classList.toggle('hidden', !q);
        filter(q);
    });

    clear.addEventListener('click', function () {
        input.value = '';
        this.classList.add('hidden');
        filter('');
    });
})();
</script>
@endpush
@endsection
