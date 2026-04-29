@extends('layouts.app')
@section('title', 'Kelola Tagihan')
@section('page-title', 'Kelola Tagihan')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        @include('layouts.partials.tahun-ajaran-select', [
            'allTahunAjaran' => $allTahunAjaran,
            'selectedTa'     => $tahunAjaran,
            'taRoute'        => 'admin-tu.tagihan.index',
        ])
        <a href="{{ route('admin-tu.tagihan.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Buat Tagihan Baru
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if($jenisTagihanList->isNotEmpty())
    @php
        $adaGap = $jenisTagihanList->filter(fn($i) => $i->tagihan_siswa_count < ($i->kelas->siswa_count ?? 0))->count();
    @endphp

    {{-- Banner distribusi massal --}}
    @if($adaGap > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm text-amber-800">
            <i class="fas fa-exclamation-circle text-amber-500 text-base flex-shrink-0"></i>
            <span>
                <strong>{{ $adaGap }} dari {{ $jenisTagihanList->count() }} tagihan</strong> belum terdistribusi ke semua siswa kelasnya.
                Klik badge <strong>X/Y siswa</strong> di kolom Penerima untuk melihat siapa yang belum menerima.
            </span>
        </div>
        <form method="POST"
              action="{{ route('admin-tu.tagihan.distribusi-semua') }}?ta={{ $tahunAjaran?->id }}"
              x-data
              x-on:submit.prevent="if(confirm('Distribusikan semua tagihan ke siswa baru yang belum memilikinya?\n\nProses ini hanya menambah tagihan ke siswa yang belum punya, tidak mengubah data yang sudah ada.')) $el.submit()">
            @csrf
            <button type="submit"
                    class="flex-shrink-0 flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg whitespace-nowrap">
                <i class="fas fa-paper-plane"></i> Distribusi Semua
            </button>
        </form>
    </div>
    @endif

    {{-- Search --}}
    <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input type="text" id="search-input"
               placeholder="Cari nama tagihan atau kelas..."
               autocomplete="off"
               class="w-full pl-9 pr-9 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <button type="button" id="search-clear"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama Tagihan</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kelas</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kategori</th>
                    <th class="text-right px-4 py-3 text-gray-600 font-medium">Nominal</th>
                    <th class="text-center px-4 py-3 text-gray-600 font-medium">Penerima</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Jatuh Tempo</th>
                    <th class="text-right px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($jenisTagihanList as $item)
                @php
                    $totalSiswa   = $item->kelas->siswa_count ?? 0;
                    $sudahTerima  = $item->tagihan_siswa_count;
                    $adaSiswaBaru = $sudahTerima < $totalSiswa;
                @endphp
                <tr class="tagihan-row {{ $adaSiswaBaru ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-gray-50' }}"
                    data-nama="{{ strtolower($item->nama) }}"
                    data-kelas="{{ strtolower($item->kelas->nama) }}">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $item->nama }}</p>
                        @if($item->deskripsi)
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $item->deskripsi }}</p>
                        @endif
                        @if($item->is_cicilan)
                        <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full mt-0.5 inline-block">Cicilan bebas</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">Kelas {{ $item->kelas->nama }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ match($item->kategori) {
                                'kas_kelas' => 'bg-blue-100 text-blue-700',
                                'buku_lks'  => 'bg-purple-100 text-purple-700',
                                'kegiatan'  => 'bg-orange-100 text-orange-700',
                                'seragam'   => 'bg-teal-100 text-teal-700',
                                'spp'       => 'bg-indigo-100 text-indigo-700',
                                default     => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ \App\Models\JenisTagihan::kategoriLabel()[$item->kategori] ?? $item->kategori }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-800 whitespace-nowrap">
                        Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($totalSiswa > 0)
                            @if($adaSiswaBaru)
                            <a href="{{ route('admin-tu.tagihan.penerima', $item) }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 hover:bg-amber-200 px-2 py-0.5 rounded-full transition-colors">
                                <i class="fas fa-exclamation-circle text-[10px]"></i>
                                {{ $sudahTerima }}/{{ $totalSiswa }} siswa
                            </a>
                            @else
                            <a href="{{ route('admin-tu.tagihan.penerima', $item) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-100 hover:bg-green-200 px-2 py-0.5 rounded-full transition-colors">
                                <i class="fas fa-check text-[10px]"></i>
                                {{ $sudahTerima }} siswa
                            </a>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-sm whitespace-nowrap">
                        {{ $item->due_date?->format('d M Y') ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin-tu.tagihan.edit', $item) }}"
                               class="text-xs text-blue-600 hover:underline">Edit</a>

                            @if($adaSiswaBaru)
                            <form method="POST" action="{{ route('admin-tu.tagihan.distribusi-ulang', $item) }}"
                                  x-data
                                  x-on:submit.prevent="if(confirm('Distribusikan tagihan ini ke {{ $totalSiswa - $sudahTerima }} siswa baru?')) $el.submit()">
                                @csrf
                                <button type="submit" class="text-xs text-amber-600 hover:underline font-semibold">
                                    Distribusi
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-green-600"><i class="fas fa-check mr-1"></i>Lengkap</span>
                            @endif

                            <form method="POST" action="{{ route('admin-tu.tagihan.destroy', $item) }}"
                                  x-data
                                  x-on:submit.prevent="if(confirm('Hapus tagihan \"{{ addslashes($item->nama) }}\"?\nTagihan siswa yang sudah dibuat tidak ikut terhapus.')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div id="empty-result" class="hidden px-4 py-8 text-center text-gray-400 text-sm border-t">
            Tidak ada tagihan yang cocok.
        </div>
    </div>

    @else
    {{-- Search (tetap tampil walau kosong, tapi kondisi ini hanya jika list kosong) --}}
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-file-invoice-dollar text-4xl mb-3 block"></i>
        <p class="font-medium mb-1">Belum ada tagihan</p>
        <p class="text-sm mb-4">Buat tagihan baru untuk didistribusikan ke siswa.</p>
        <a href="{{ route('admin-tu.tagihan.create') }}"
           class="inline-block bg-blue-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-blue-700">
            Buat Tagihan Sekarang
        </a>
    </div>
    @endif

</div>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('search-input');
    const clear = document.getElementById('search-clear');
    const empty = document.getElementById('empty-result');
    const rows  = document.querySelectorAll('.tagihan-row');

    if (!input) return;

    function filter(q) {
        let visible = 0;
        rows.forEach(function (row) {
            const match = !q || row.dataset.nama.includes(q) || row.dataset.kelas.includes(q);
            row.classList.toggle('hidden', !match);
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
