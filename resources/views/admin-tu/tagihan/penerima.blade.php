@extends('layouts.app')
@section('title', 'Penerima Tagihan')
@section('page-title', 'Penerima Tagihan')

@section('content')
<div class="space-y-4">

    {{-- Back --}}
    <a href="{{ route('admin-tu.tagihan.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
        <i class="fas fa-arrow-left text-xs"></i> Kembali ke daftar tagihan
    </a>

    {{-- Info Tagihan --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-semibold text-gray-800 text-base">{{ $tagihan->nama }}</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    Kelas {{ $tagihan->kelas->nama }}
                    &middot;
                    {{ \App\Models\JenisTagihan::kategoriLabel()[$tagihan->kategori] ?? $tagihan->kategori }}
                    &middot;
                    Rp {{ number_format($tagihan->total_nominal, 0, ',', '.') }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-sm font-bold {{ $belum->isEmpty() ? 'text-green-600' : 'text-amber-600' }}">
                    {{ $sudah->count() }}/{{ $sudah->count() + $belum->count() }} siswa
                </span>
                @if($belum->isNotEmpty())
                <form method="POST" action="{{ route('admin-tu.tagihan.distribusi-ulang', $tagihan) }}"
                      x-data
                      x-on:submit.prevent="if(confirm('Distribusikan tagihan ini ke {{ $belum->count() }} siswa yang belum menerimanya?')) $el.submit()">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        <i class="fas fa-paper-plane"></i>
                        Distribusi ke {{ $belum->count() }} Siswa
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        @php
            $total = $sudah->count() + $belum->count();
            $pct   = $total > 0 ? round($sudah->count() / $total * 100) : 0;
        @endphp
        <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-400 mb-1">
                <span>Distribusi</span>
                <span>{{ $pct }}% ({{ $sudah->count() }} dari {{ $total }} siswa)</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-2 rounded-full transition-all {{ $pct >= 100 ? 'bg-green-500' : 'bg-amber-400' }}"
                     style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input type="text" id="search-input"
               placeholder="Cari nama siswa atau NIS..."
               autocomplete="off"
               class="w-full pl-9 pr-9 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
        <button type="button" id="search-clear"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Belum Menerima --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-4 py-3 border-b bg-amber-50">
                <i class="fas fa-exclamation-circle text-amber-500"></i>
                <span class="text-sm font-semibold text-amber-800">Belum Menerima</span>
                <span class="ml-auto text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">{{ $belum->count() }} siswa</span>
            </div>

            @if($belum->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">
                <i class="fas fa-check-circle text-green-400 text-3xl mb-2 block"></i>
                Semua siswa sudah menerima tagihan ini.
            </div>
            @else
            <ul class="divide-y divide-gray-100" id="list-belum">
                @foreach($belum as $s)
                <li class="siswa-row flex items-center gap-3 px-4 py-2.5"
                    data-nama="{{ strtolower($s->nama) }}"
                    data-nis="{{ $s->nis }}">
                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 text-xs font-bold text-amber-700">
                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $s->nama }}</p>
                        <p class="text-xs text-gray-400">NIS {{ $s->nis }}</p>
                    </div>
                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full flex-shrink-0">Belum</span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Sudah Menerima --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-4 py-3 border-b bg-green-50">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-sm font-semibold text-green-800">Sudah Menerima</span>
                <span class="ml-auto text-xs font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">{{ $sudah->count() }} siswa</span>
            </div>

            @if($sudah->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">
                Belum ada siswa yang menerima tagihan ini.
            </div>
            @else
            <ul class="divide-y divide-gray-100" id="list-sudah">
                @foreach($sudah as $s)
                <li class="siswa-row flex items-center gap-3 px-4 py-2.5"
                    data-nama="{{ strtolower($s->nama) }}"
                    data-nis="{{ $s->nis }}">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 text-xs font-bold text-green-700">
                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $s->nama }}</p>
                        <p class="text-xs text-gray-400">NIS {{ $s->nis }}</p>
                    </div>
                    <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full flex-shrink-0">Sudah</span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>

    <p id="empty-result" class="hidden text-center text-sm text-gray-400 py-4">
        Tidak ada siswa yang cocok dengan pencarian.
    </p>

</div>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('search-input');
    const clear = document.getElementById('search-clear');
    const empty = document.getElementById('empty-result');
    const rows  = document.querySelectorAll('.siswa-row');

    function filter(q) {
        let visible = 0;
        rows.forEach(function (row) {
            const match = !q || row.dataset.nama.includes(q) || row.dataset.nis.includes(q);
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
