@extends('layouts.app')
@section('title', 'Edit Tahun Ajaran')
@section('page-title', 'Edit Tahun Ajaran')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm p-6">
        @if($tahunAjaran->is_aktif)
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-5 text-sm text-blue-700 flex items-center gap-2">
            <i class="fas fa-calendar-check"></i>
            <span>Ini adalah tahun ajaran yang sedang aktif.</span>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.tahun-ajaran.update', $tahunAjaran) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Tahun Ajaran <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="{{ old('nama', $tahunAjaran->nama) }}"
                       placeholder="Contoh: 2024/2025"
                       class="w-full px-3 py-2.5 text-sm border {{ $errors->has('nama') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                @error('nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai"
                           value="{{ old('tanggal_mulai', $tahunAjaran->tanggal_mulai->format('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 text-sm border {{ $errors->has('tanggal_mulai') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    @error('tanggal_mulai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai"
                           value="{{ old('tanggal_selesai', $tahunAjaran->tanggal_selesai->format('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 text-sm border {{ $errors->has('tanggal_selesai') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    @error('tanggal_selesai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2.5 rounded-lg">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.tahun-ajaran.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-5 py-2.5 rounded-lg">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
