@extends('layouts.app')
@section('title', 'Kelola Beasiswa / Subsidi')
@section('page-title', 'Beasiswa & Subsidi Penuh')

@php
$kategoriLabel = \App\Models\JenisTagihan::kategoriLabel();
$kategoriWarna = \App\Models\KategoriTagihan::orderBy('urutan')->pluck('warna', 'kode')->toArray();
@endphp

@section('content')
<div class="space-y-4">

    {{-- Header actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Siswa yang mendapat beasiswa tercatat <strong>lunas</strong> namun nominalnya <strong>tidak dihitung</strong> sebagai pemasukan kas.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('bendahara.beasiswa.export', ['ta' => $selectedTa?->id]) }}"
               class="flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                <i class="fas fa-file-excel"></i> Export
            </a>
            <button type="button" onclick="document.getElementById('modal-import').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                <i class="fas fa-file-import"></i> Import
            </button>
            <button type="button" onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-start gap-2">
        <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-start gap-2">
        <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('bendahara.beasiswa.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Tahun Ajaran</label>
                <select name="ta" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach($allTahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ $selectedTa?->id == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }}{{ $ta->is_aktif ? ' ★' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Kelas</label>
                <select name="kelas_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Cari</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Nama / NIS..."
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-44">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['kelas_id','cari']))
            <a href="{{ route('bendahara.beasiswa.index', ['ta' => $selectedTa?->id]) }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel penerima --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 text-sm">Daftar Penerima Beasiswa</h2>
            <span class="text-xs text-gray-400">{{ $penerima->total() }} siswa</span>
        </div>

        @if($penerima->isEmpty())
        <div class="text-center py-14 text-gray-400">
            <i class="fas fa-graduation-cap text-4xl mb-3 block"></i>
            <p class="text-sm">Belum ada siswa penerima beasiswa di tahun ajaran ini.</p>
        </div>
        @else
        <div class="divide-y divide-gray-100" x-data="{ open: null }">
            @foreach($penerima as $siswa)
            @php
                $totalSubsidi  = $siswa->tagihanSiswa->sum('nominal_subsidi');
                $totalNominal  = $siswa->tagihanSiswa->sum('nominal_total');
                $pctRata       = $totalNominal > 0 ? round($totalSubsidi / $totalNominal * 100) : 0;
                $namaBeasiswas = $siswa->tagihanSiswa
                    ->map(fn($t) => $t->pembayaran->first()?->catatan)
                    ->filter()->unique()->values();
                $idx = $siswa->id;
            @endphp

            {{-- Row siswa --}}
            <div>
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 cursor-pointer select-none"
                     @click="open = open === {{ $idx }} ? null : {{ $idx }}">

                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-700 font-bold text-sm">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
                    </div>

                    {{-- Identitas --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $siswa->nama }}</p>
                        <p class="text-xs text-gray-400">NIS: {{ $siswa->nis }} · {{ $siswa->kelas?->nama ?? '-' }}</p>
                    </div>

                    {{-- Nama beasiswa --}}
                    <div class="hidden md:block text-center flex-shrink-0 max-w-[180px]">
                        @foreach($namaBeasiswas as $nb)
                        <span class="inline-block text-xs bg-purple-50 text-purple-700 border border-purple-200 rounded-full px-2 py-0.5 mb-0.5">
                            {{ $nb }}
                        </span>
                        @endforeach
                    </div>

                    {{-- Persen --}}
                    <div class="text-center flex-shrink-0 w-16">
                        <p class="text-sm font-bold {{ $pctRata == 100 ? 'text-purple-600' : 'text-yellow-600' }}">
                            {{ $pctRata }}%
                        </p>
                        <p class="text-[10px] text-gray-400">subsidi</p>
                    </div>

                    {{-- Total subsidi --}}
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-purple-700">Rp {{ number_format($totalSubsidi, 0, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">{{ $siswa->tagihanSiswa->count() }} tagihan</p>
                    </div>

                    {{-- Batalkan --}}
                    <button type="button"
                            @click.stop="konfirmasiVoid({{ $siswa->id }}, '{{ addslashes($siswa->nama) }}', {{ $selectedTa?->id ?? 0 }})"
                            class="flex-shrink-0 text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded border border-red-200 hover:border-red-400 whitespace-nowrap">
                        <i class="fas fa-times"></i>
                    </button>

                    {{-- Chevron --}}
                    <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0 transition-transform duration-200"
                       :class="open === {{ $idx }} ? 'rotate-180' : ''"></i>
                </div>

                {{-- Detail tagihans --}}
                <div x-show="open === {{ $idx }}" x-cloak class="bg-gray-50 border-t border-gray-100 px-5 py-3">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-400 uppercase tracking-wide">
                                <th class="text-left pb-2 font-medium">Tagihan</th>
                                <th class="text-left pb-2 font-medium">Kategori</th>
                                <th class="text-right pb-2 font-medium">Nominal</th>
                                <th class="text-right pb-2 font-medium">Subsidi</th>
                                <th class="text-center pb-2 font-medium">%</th>
                                <th class="text-left pb-2 font-medium pl-3">Nama Beasiswa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($siswa->tagihanSiswa as $t)
                            @php
                                $pct         = $t->nominal_total > 0 ? round($t->nominal_subsidi / $t->nominal_total * 100) : 0;
                                $kode        = $t->jenisTagihan?->kategori ?? '';
                                $warna       = $kategoriWarna[$kode] ?? 'bg-gray-100 text-gray-600';
                                $namaTagihan = $t->jenisTagihan?->nama ?? '-';
                                $namaB       = $t->pembayaran->first()?->catatan ?? '-';
                            @endphp
                            <tr>
                                <td class="py-1.5 pr-3 font-medium text-gray-700">{{ $namaTagihan }}</td>
                                <td class="py-1.5 pr-3">
                                    <span class="inline-block px-1.5 py-0.5 rounded-full text-[10px] {{ $warna }}">
                                        {{ $kategoriLabel[$kode] ?? $kode }}
                                    </span>
                                </td>
                                <td class="py-1.5 text-right text-gray-500">Rp {{ number_format($t->nominal_total, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-right font-semibold text-purple-700">Rp {{ number_format($t->nominal_subsidi, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full font-semibold
                                        {{ $pct == 100 ? 'bg-purple-100 text-purple-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $pct }}%
                                    </span>
                                </td>
                                <td class="py-1.5 pl-3 text-gray-600 italic">{{ $namaB }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        @if($penerima->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $penerima->withQueryString()->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal Tambah Manual --}}
<div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Tambah Penerima Beasiswa</h3>
            <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        @if($jenisBeasiswaList->isEmpty())
        {{-- Tidak ada master beasiswa --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-4 text-sm">
            <p class="font-medium text-amber-800 mb-1"><i class="fas fa-exclamation-triangle mr-1"></i>Master beasiswa belum diisi</p>
            <p class="text-amber-700 text-xs">Tambahkan jenis beasiswa terlebih dahulu sebelum menambah penerima.</p>
            <a href="{{ route('bendahara.jenis-beasiswa.index') }}"
               class="inline-block mt-2 text-xs font-medium text-purple-600 hover:text-purple-800 underline">
                → Kelola Master Beasiswa
            </a>
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Tutup</button>
        </div>
        @else
        <form method="POST" action="{{ route('bendahara.beasiswa.store') }}">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa?->id }}">

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Beasiswa <span class="text-red-500">*</span></label>
                <select name="beasiswa_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                    <option value="">-- Pilih Jenis Beasiswa --</option>
                    @foreach($jenisBeasiswaList as $jb)
                    <option value="{{ $jb->id }}">
                        {{ $jb->nama }}{{ $jb->sumber ? ' · '.$jb->sumber : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Siswa <span class="text-red-500">*</span></label>
                @if($siswaBelum->isEmpty())
                <p class="text-sm text-gray-400 italic py-1">Semua siswa sudah lunas atau sudah mendapat beasiswa.</p>
                @else
                <select name="siswa_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($siswaBelum as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }}) — {{ $s->kelas?->nama }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            <p class="text-xs text-gray-500 mb-4 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                <i class="fas fa-info-circle text-yellow-500 mr-1"></i>
                Semua tagihan belum lunas siswa ini akan dilunasi. Nominal tidak terhitung sebagai pemasukan kas.
            </p>

            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                @if($siswaBelum->isNotEmpty())
                <button type="submit" class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">
                    <i class="fas fa-graduation-cap mr-1"></i>Terapkan Beasiswa
                </button>
                @endif
            </div>
        </form>
        @endif
        </form>
    </div>
</div>

{{-- Modal Import --}}
<div id="modal-import" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Import Penerima Beasiswa</h3>
            <button type="button" onclick="document.getElementById('modal-import').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        {{-- Template download --}}
        <div class="mb-4 flex items-center justify-between bg-purple-50 border border-purple-200 rounded-lg px-3 py-2.5">
            <div>
                <p class="text-xs font-medium text-purple-800">Belum punya template?</p>
                <p class="text-xs text-purple-600">Unduh template Excel yang sudah ada contoh pengisian.</p>
            </div>
            <a href="{{ route('bendahara.beasiswa.template') }}"
               class="flex-shrink-0 ml-3 px-3 py-1.5 text-xs bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-1">
                <i class="fas fa-download"></i> Template
            </a>
        </div>

        <form method="POST" action="{{ route('bendahara.beasiswa.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa?->id }}">

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel <span class="text-gray-400 font-normal text-xs">(XLSX / CSV)</span></label>
                <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                       class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="mb-4 text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 space-y-0.5">
                <p class="font-medium text-gray-700 mb-1">Format kolom:</p>
                <p><code class="bg-gray-200 rounded px-1 text-gray-700">nis</code> — NIS siswa <span class="text-red-500">(wajib)</span></p>
                <p><code class="bg-gray-200 rounded px-1 text-gray-700">kode_beasiswa</code> — Kode dari master beasiswa <span class="text-red-500">(wajib)</span></p>
                <p class="pt-1 text-gray-400">Lihat sheet "Referensi Kode" di template untuk daftar kode yang tersedia.</p>
            </div>

            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modal-import').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                    <i class="fas fa-upload mr-1"></i>Upload & Proses
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Hidden void form --}}
<form id="form-void" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="tahun_ajaran_id" id="void-ta-id">
    <input type="hidden" name="catatan_void" id="void-catatan">
</form>

{{-- Modal Konfirmasi Void --}}
<div id="modal-void" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="font-semibold text-gray-800 mb-2">Batalkan Beasiswa</h3>
        <p class="text-sm text-gray-600 mb-3">Batalkan beasiswa untuk <strong id="void-nama"></strong>? Semua pembayaran beasiswa siswa ini di tahun ajaran ini akan di-void dan tagihan kembali ke status belum lunas.</p>
        <div class="mb-4">
            <label class="block text-xs text-gray-600 mb-1 font-medium">Alasan pembatalan <span class="text-red-500">*</span></label>
            <textarea id="void-alasan" rows="2" placeholder="Contoh: Siswa tidak jadi penerima beasiswa"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 outline-none"></textarea>
        </div>
        <div class="flex gap-2 justify-end">
            <button type="button" onclick="document.getElementById('modal-void').classList.add('hidden')"
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Batal</button>
            <button type="button" onclick="submitVoid()"
                    class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                <i class="fas fa-times mr-1"></i>Batalkan Beasiswa
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function konfirmasiVoid(siswaId, nama, taId) {
    document.getElementById('void-nama').textContent = nama;
    document.getElementById('void-ta-id').value = taId;
    document.getElementById('form-void').action = '/bendahara/beasiswa/' + siswaId + '/void';
    document.getElementById('void-alasan').value = '';
    document.getElementById('modal-void').classList.remove('hidden');
}

function submitVoid() {
    const alasan = document.getElementById('void-alasan').value.trim();
    if (!alasan) { alert('Alasan pembatalan wajib diisi.'); return; }
    document.getElementById('void-catatan').value = alasan;
    document.getElementById('form-void').submit();
}
</script>
@endpush
@endsection
