@extends('layouts.app')
@section('title', 'Buat Tagihan Baru')
@section('page-title', 'Buat Tagihan Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin-tu.tagihan.store') }}"
              x-data="{
                  isCicilan: {{ old('is_cicilan') ? 'true' : 'false' }},
                  display: '{{ old('total_nominal') ? number_format((int)old('total_nominal'), 0, ',', '.') : '' }}',
                  raw: '{{ old('total_nominal') ?? '' }}',
                  format(val) {
                      let num = val.replace(/\D/g, '');
                      this.raw = num;
                      this.display = num ? parseInt(num).toLocaleString('id-ID') : '';
                  }
              }">
            @csrf
            <div class="space-y-5">

                {{-- Distribusi ke Kelas --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Distribusikan ke Kelas <span class="text-red-500">*</span></label>
                        <button type="button" id="btn-pilih-semua"
                                class="text-xs text-blue-600 hover:underline">Pilih Semua</button>
                    </div>

                    @error('kelas_ids')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror

                    <div class="space-y-2">
                        @foreach($perAngkatan as $tingkat => $kelasList)
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            {{-- Header angkatan --}}
                            <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50">
                                <span class="text-sm font-semibold text-gray-700">Angkatan {{ $tingkat }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-400">{{ $kelasList->sum('siswa_count') }} siswa</span>
                                    <button type="button"
                                            class="btn-pilih-angkatan text-xs text-blue-600 hover:underline"
                                            data-tingkat="{{ $tingkat }}">Pilih Semua</button>
                                </div>
                            </div>
                            {{-- Daftar kelas --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-3">
                                @foreach($kelasList as $kelas)
                                @php $checked = in_array($kelas->id, old('kelas_ids', [])); @endphp
                                <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer
                                              hover:bg-blue-50 hover:border-blue-300
                                              has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                                    <input type="checkbox"
                                           name="kelas_ids[]"
                                           value="{{ $kelas->id }}"
                                           class="kelas-cb rounded border-gray-300 text-blue-600"
                                           data-tingkat="{{ $tingkat }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Kelas {{ $kelas->nama }}</p>
                                        <p class="text-xs text-gray-400">{{ $kelas->siswa_count }} siswa</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tagihan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                           placeholder="Contoh: Kas Kelas Oktober 2025">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach(\App\Models\JenisTagihan::kategoriLabel() as $val => $label)
                        <option value="{{ $val }}" {{ old('kategori') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kategori')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Total Nominal --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Nominal (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                        <input type="text" inputmode="numeric"
                               x-model="display" @input="format($event.target.value)" required
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: 100.000">
                    </div>
                    <input type="hidden" name="total_nominal" :value="raw">
                    @error('total_nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Cicilan --}}
                <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_cicilan" value="1" x-model="isCicilan"
                               {{ old('is_cicilan') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm font-medium text-gray-700">Tagihan ini bisa dicicil</span>
                    </label>
                    <div x-show="isCicilan" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Cicilan <span class="text-red-500">*</span></label>
                        <select name="jumlah_cicilan"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            @for($i = 2; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('jumlah_cicilan', 2) == $i ? 'selected' : '' }}>{{ $i }}x cicilan</option>
                            @endfor
                        </select>
                        @error('jumlah_cicilan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Jatuh Tempo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('due_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                              placeholder="Keterangan tambahan (opsional)">{{ old('deskripsi') }}</textarea>
                </div>

            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mt-5 text-sm text-blue-700">
                <i class="fas fa-info-circle mr-2"></i>
                Tagihan akan otomatis didistribusikan ke semua siswa di kelas yang dipilih.
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    <i class="fas fa-paper-plane mr-2"></i>Buat & Distribusikan
                </button>
                <a href="{{ route('admin-tu.tagihan.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Pilih semua kelas
document.getElementById('btn-pilih-semua').addEventListener('click', function () {
    const cbs = document.querySelectorAll('.kelas-cb');
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
    this.textContent = allChecked ? 'Pilih Semua' : 'Batal Semua';
    updateAngkatanLabels();
});

// Pilih semua per angkatan
document.querySelectorAll('.btn-pilih-angkatan').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const tingkat = this.dataset.tingkat;
        const cbs = document.querySelectorAll(`.kelas-cb[data-tingkat="${tingkat}"]`);
        const allChecked = [...cbs].every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
        this.textContent = allChecked ? 'Pilih Semua' : 'Batal Semua';
    });
});

function updateAngkatanLabels() {
    document.querySelectorAll('.btn-pilih-angkatan').forEach(function (btn) {
        const tingkat = btn.dataset.tingkat;
        const cbs = document.querySelectorAll(`.kelas-cb[data-tingkat="${tingkat}"]`);
        const allChecked = [...cbs].every(cb => cb.checked);
        btn.textContent = allChecked ? 'Batal Semua' : 'Pilih Semua';
    });
}
</script>
@endpush
@endsection
