@extends('layouts.app')
@section('title', 'Buat SPP Baru')
@section('page-title', 'Buat SPP Baru')

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

        <form method="POST" action="{{ route('bendahara.spp.store') }}">
            @csrf
            <div class="space-y-5">

                {{-- Tahun --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun" value="{{ old('tahun', now()->year) }}" required
                           min="2020" max="2099"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                {{-- Jatuh Tempo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                {{-- Per Angkatan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Tarif & Kelas per Angkatan <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        @foreach($perAngkatan as $tingkat => $kelasList)
                        @php $oldNominal = old("nominal.$tingkat"); $oldKelas = old('kelas_ids', []); @endphp
                        <div x-data="{ open: true }" class="border border-gray-200 rounded-xl overflow-hidden">

                            {{-- Header angkatan --}}
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 cursor-pointer select-none"
                                 @click="open = !open">
                                <i class="fas fa-chevron-right text-xs text-gray-400 transition-transform duration-200"
                                   :class="open ? 'rotate-90' : ''"></i>
                                <span class="font-semibold text-gray-800 flex-1">Angkatan {{ $tingkat }} (Kelas {{ $tingkat }})</span>
                                <span class="text-xs text-gray-400">{{ $kelasList->sum('siswa_count') }} siswa</span>
                            </div>

                            {{-- Body angkatan --}}
                            <div x-show="open" x-cloak class="px-4 pb-4 pt-3 space-y-3 border-t border-gray-100">

                                {{-- Nominal input --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        Tarif SPP Angkatan {{ $tingkat }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative max-w-xs">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                        <input type="number" name="nominal[{{ $tingkat }}]"
                                               value="{{ $oldNominal }}"
                                               placeholder="0"
                                               min="1000"
                                               class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                    @error("nominal.$tingkat")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Pilih kelas --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-medium text-gray-600">Distribusikan ke Kelas</p>
                                        <button type="button"
                                                class="angkatan-select-all text-xs text-blue-600 hover:underline"
                                                data-tingkat="{{ $tingkat }}">
                                            Pilih Semua
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach($kelasList as $kelas)
                                        <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                                            <input type="checkbox"
                                                   name="kelas_ids[]"
                                                   value="{{ $kelas->id }}"
                                                   data-tingkat="{{ $tingkat }}"
                                                   class="kelas-cb rounded border-gray-300 text-blue-600"
                                                   {{ in_array($kelas->id, $oldKelas) ? 'checked' : '' }}>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">Kelas {{ $kelas->nama }}</p>
                                                <p class="text-xs text-gray-400">{{ $kelas->siswa_count }} siswa</p>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('kelas_ids')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    SPP akan dibuat untuk 12 bulan (Januari–Desember) sekaligus. Tarif berbeda per angkatan sesuai ketentuan sekolah. Bulan yang sudah punya SPP di kelas tersebut akan dilewati.
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    <i class="fas fa-paper-plane mr-2"></i>Buat & Distribusikan
                </button>
                <a href="{{ route('bendahara.spp.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.angkatan-select-all').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const tingkat = this.dataset.tingkat;
        const cbs = document.querySelectorAll(`.kelas-cb[data-tingkat="${tingkat}"]`);
        const allChecked = [...cbs].every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
        this.textContent = allChecked ? 'Pilih Semua' : 'Batal Semua';
    });
});
</script>
@endpush
@endsection
