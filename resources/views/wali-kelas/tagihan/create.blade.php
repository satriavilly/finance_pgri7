@extends('layouts.app')
@section('title', 'Buat Tagihan Baru')
@section('page-title', 'Buat Tagihan — Kelas ' . $kelas->nama)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('wali-kelas.tagihan.store') }}" x-data="{ isCicilan: false }">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tagihan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                           placeholder="Contoh: Kas Kelas Oktober 2025">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        @foreach(\App\Models\JenisTagihan::kategoriLabel() as $val => $label)
                            <option value="{{ $val }}" {{ old('kategori') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="total_nominal" value="{{ old('total_nominal') }}" required min="1000" step="500"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                           placeholder="Contoh: 50000">
                    @error('total_nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_cicilan" id="is_cicilan" value="1"
                           x-model="isCicilan" {{ old('is_cicilan') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600">
                    <label for="is_cicilan" class="text-sm text-gray-700">Pembayaran cicilan?</label>
                </div>

                <div x-show="isCicilan" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Cicilan</label>
                    <input type="number" name="jumlah_cicilan" value="{{ old('jumlah_cicilan', 2) }}" min="2" max="12"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                              placeholder="Keterangan tambahan (opsional)">{{ old('deskripsi') }}</textarea>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                <p class="text-blue-700 text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tagihan akan otomatis didistribusikan ke semua siswa Kelas {{ $kelas->nama }}.
                </p>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    Buat Tagihan
                </button>
                <a href="{{ route('wali-kelas.tagihan.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
