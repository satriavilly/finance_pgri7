@extends('layouts.app')
@section('title', 'Kelola Beasiswa / Subsidi')
@section('page-title', 'Beasiswa & Subsidi Penuh')

@section('content')
<div class="space-y-4">

    {{-- Header actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Siswa yang mendapat subsidi penuh tercatat <strong>lunas</strong> namun nominalnya <strong>tidak dihitung</strong> sebagai pemasukan kas.</p>
        <div class="flex gap-2">
            {{-- Export --}}
            <a href="{{ route('bendahara.beasiswa.export', ['ta' => $selectedTa?->id]) }}"
               class="flex items-center gap-1.5 px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                <i class="fas fa-file-excel"></i> Export
            </a>
            {{-- Import trigger --}}
            <button type="button" onclick="document.getElementById('modal-import').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
            {{-- Tambah manual trigger --}}
            <button type="button" onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">
                <i class="fas fa-plus"></i> Tambah Manual
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
                <label class="block text-xs text-gray-500 mb-1">Tahun Ajaran</label>
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
                <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                <select name="kelas_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Nama / NIS..."
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-48">
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
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-graduation-cap text-4xl mb-3 block"></i>
            <p class="text-sm">Belum ada siswa penerima beasiswa di tahun ajaran ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Siswa</th>
                        <th class="px-4 py-3 text-left">Kelas</th>
                        <th class="px-4 py-3 text-right">Tagihan Disubsidi</th>
                        <th class="px-4 py-3 text-right">Total Subsidi</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($penerima as $siswa)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $siswa->nama }}</p>
                            <p class="text-xs text-gray-400">NIS: {{ $siswa->nis }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $siswa->kelas?->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-medium text-gray-700">{{ $siswa->tagihanSiswa->count() }} tagihan</span>
                            <div class="text-xs text-gray-400 mt-0.5">
                                @foreach($siswa->tagihanSiswa as $t)
                                <div>{{ $t->jenisTagihan?->nama }}</div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-purple-700">
                            Rp {{ number_format($siswa->tagihanSiswa->sum('nominal_subsidi'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button"
                                    onclick="konfirmasiVoid({{ $siswa->id }}, '{{ addslashes($siswa->nama) }}', {{ $selectedTa?->id ?? 0 }})"
                                    class="text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded border border-red-200 hover:border-red-400">
                                <i class="fas fa-times mr-1"></i>Batalkan
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
        <form method="POST" action="{{ route('bendahara.beasiswa.store') }}">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa?->id }}">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Siswa</label>
                @if($siswaBelum->isEmpty())
                <p class="text-sm text-gray-500 italic">Semua siswa di TA ini sudah lunas atau sudah mendapat beasiswa.</p>
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
                Semua tagihan yang belum lunas dari siswa ini akan dilunasi sebagai beasiswa. Nominal tidak terhitung sebagai pemasukan kas.
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
        <form method="POST" action="{{ route('bendahara.beasiswa.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa?->id }}">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel <span class="text-gray-400 font-normal text-xs">(XLSX / CSV)</span></label>
                <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                       class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <div class="mb-4 text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 space-y-1">
                <p class="font-medium text-gray-700">Format kolom yang diperlukan:</p>
                <p><code class="bg-gray-200 rounded px-1">nis</code> — NIS siswa</p>
                <p>Baris pertama adalah header. Satu baris = satu siswa. Semua tagihan belum lunas siswa tersebut akan dilunasi.</p>
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

{{-- Modal Konfirmasi Void (hidden form) --}}
<form id="form-void" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="tahun_ajaran_id" id="void-ta-id">
    <input type="hidden" name="catatan_void" id="void-catatan">
</form>

<div id="modal-void" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="font-semibold text-gray-800 mb-2">Batalkan Beasiswa</h3>
        <p class="text-sm text-gray-600 mb-3">Batalkan beasiswa untuk <strong id="void-nama"></strong>? Semua pembayaran beasiswa siswa ini di tahun ajaran ini akan di-void.</p>
        <div class="mb-4">
            <label class="block text-xs text-gray-600 mb-1">Alasan pembatalan <span class="text-red-500">*</span></label>
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
    if (!alasan) {
        alert('Alasan pembatalan wajib diisi.');
        return;
    }
    document.getElementById('void-catatan').value = alasan;
    document.getElementById('form-void').submit();
}
</script>
@endpush
@endsection
