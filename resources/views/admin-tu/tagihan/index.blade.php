@extends('layouts.app')
@section('title', 'Kelola Tagihan')
@section('page-title', 'Kelola Tagihan')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">Pilih tahun ajaran untuk melihat tagihan</p>
        <a href="{{ route('admin-tu.tagihan.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Buat Tagihan Baru
        </a>
    </div>

    {{-- Tahun Ajaran Tabs --}}
    @if($allTahunAjaran->isNotEmpty())
    <div class="flex gap-2 flex-wrap">
        @foreach($allTahunAjaran as $ta)
        <a href="{{ route('admin-tu.tagihan.index', ['ta' => $ta->id]) }}"
           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium border transition-colors
                  {{ $tahunAjaran?->id === $ta->id
                     ? 'bg-blue-600 text-white border-blue-600'
                     : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400 hover:text-blue-600' }}">
            {{ $ta->nama }}
            @if($ta->is_aktif)
            <span class="text-xs {{ $tahunAjaran?->id === $ta->id ? 'bg-blue-500' : 'bg-green-500' }} text-white px-1.5 py-0.5 rounded-full leading-none">Aktif</span>
            @endif
        </a>
        @endforeach
    </div>
    @endif

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
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
        @if($jenisTagihanList->isEmpty())
        <div class="p-10 text-center text-gray-400">
            <i class="fas fa-file-invoice-dollar text-4xl mb-3 block"></i>
            <p class="font-medium mb-1">Belum ada tagihan</p>
            <p class="text-sm mb-4">Buat tagihan baru untuk didistribusikan ke siswa.</p>
            <a href="{{ route('admin-tu.tagihan.create') }}"
               class="inline-block bg-blue-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-blue-700">
                Buat Tagihan Sekarang
            </a>
        </div>
        @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama Tagihan</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kelas</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kategori</th>
                    <th class="text-right px-4 py-3 text-gray-600 font-medium">Nominal</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Cicilan</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Jatuh Tempo</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    <th class="text-right px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($jenisTagihanList as $item)
                <tr class="hover:bg-gray-50 tagihan-row"
                    data-nama="{{ strtolower($item->nama) }}"
                    data-kelas="{{ strtolower($item->kelas->nama) }}">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $item->nama }}</p>
                        @if($item->deskripsi)
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $item->deskripsi }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">Kelas {{ $item->kelas->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \App\Models\JenisTagihan::kategoriLabel()[$item->kategori] ?? $item->kategori }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-800">
                        Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        @if($item->is_cicilan)
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ $item->jumlah_cicilan }}x cicilan</span>
                        @else
                        <span class="text-gray-400 text-xs">Lunas</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-sm">{{ $item->due_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $item->is_aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin-tu.tagihan.edit', $item) }}"
                               class="text-xs text-blue-600 hover:underline">Edit</a>

                            <form method="POST" action="{{ route('admin-tu.tagihan.distribusi-ulang', $item) }}"
                                  x-data
                                  x-on:submit.prevent="if(confirm('Distribusikan tagihan ini ke siswa baru yang belum memilikinya?')) $el.submit()">
                                @csrf
                                <button type="submit" class="text-xs text-amber-600 hover:underline">
                                    Distribusi
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin-tu.tagihan.destroy', $item) }}"
                                  x-data
                                  x-on:submit.prevent="if(confirm('Hapus tagihan \"{{ addslashes($item->nama) }}\"? Tagihan siswa yang sudah dibuat tidak ikut terhapus.')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div id="empty-result" class="hidden px-4 py-8 text-center text-gray-400 text-sm">
            Tidak ada tagihan yang cocok.
        </div>
        @endif
    </div>
</div>
@push('scripts')
<script>
(function () {
    const input  = document.getElementById('search-input');
    const clear  = document.getElementById('search-clear');
    const empty  = document.getElementById('empty-result');
    const rows   = document.querySelectorAll('.tagihan-row');

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
