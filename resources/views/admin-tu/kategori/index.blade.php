@extends('layouts.app')
@section('title', 'Kelola Kategori Tagihan')
@section('page-title', 'Kelola Kategori Tagihan')

@section('content')
<div class="space-y-4 max-w-3xl">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Tambah kategori baru --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Tambah Kategori Baru</h2>
        <form method="POST" action="{{ route('admin-tu.kategori.store') }}"
              x-data="{ warna: '{{ array_key_first($warnaOptions) }}' }"
              class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           placeholder="Contoh: Ekstrakurikuler"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Warna Badge</label>
                    <select name="warna" x-model="warna"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach($warnaOptions as $cls => $label)
                        <option value="{{ $cls }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Tambah Kategori
                </button>
                {{-- Preview badge --}}
                <span class="text-xs text-gray-400">Preview:</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                      :class="warna">Nama Kategori</span>
            </div>
        </form>
    </div>

    {{-- Daftar kategori --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <p class="text-sm font-medium text-gray-700">{{ $kategoriList->count() }} Kategori Terdaftar</p>
        </div>

        @if($kategoriList->isEmpty())
        <div class="px-4 py-10 text-center text-gray-400 text-sm">
            Belum ada kategori. Tambahkan di atas.
        </div>
        @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Kategori</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Kode</th>
                    <th class="text-center px-4 py-3 text-xs text-gray-500 font-medium">Digunakan</th>
                    <th class="text-right px-4 py-3 text-xs text-gray-500 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($kategoriList as $kat)
                <tr class="hover:bg-gray-50" x-data="{ editing: false }">
                    {{-- View mode --}}
                    <td class="px-4 py-3" x-show="!editing">
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $kat->warna }}">
                            {{ $kat->nama }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono" x-show="!editing">
                        {{ $kat->kode }}
                    </td>
                    <td class="px-4 py-3 text-center" x-show="!editing">
                        @if($kat->jumlah_tagihan > 0)
                        <span class="text-xs font-medium text-gray-700">{{ $kat->jumlah_tagihan }} tagihan</span>
                        @else
                        <span class="text-xs text-gray-300">Belum dipakai</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right" x-show="!editing">
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="editing = true"
                                    class="text-xs text-blue-600 hover:underline">Edit</button>

                            @if($kat->jumlah_tagihan === 0)
                            <form method="POST" action="{{ route('admin-tu.kategori.destroy', $kat) }}"
                                  x-on:submit.prevent="if(confirm('Hapus kategori \"{{ addslashes($kat->nama) }}\"?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                            @else
                            <span class="text-xs text-gray-300" title="Masih digunakan oleh {{ $kat->jumlah_tagihan }} tagihan">Hapus</span>
                            @endif
                        </div>
                    </td>

                    {{-- Edit mode (colspan via absolute positioning trick) --}}
                    <td colspan="4" class="px-4 py-3" x-show="editing" x-cloak>
                        <form method="POST" action="{{ route('admin-tu.kategori.update', $kat) }}"
                              x-data="{ warna: '{{ $kat->warna }}' }"
                              class="flex flex-wrap items-end gap-3">
                            @csrf @method('PATCH')
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                                <input type="text" name="nama" value="{{ $kat->nama }}" required
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="w-36">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Warna</label>
                                <select name="warna" x-model="warna"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    @foreach($warnaOptions as $cls => $label)
                                    <option value="{{ $cls }}" {{ $kat->warna === $cls ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="warna">Preview</span>
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg">
                                    Simpan
                                </button>
                                <button type="button" @click="editing = false"
                                        class="text-xs text-gray-500 hover:text-gray-700">Batal</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <p class="text-xs text-gray-400">
        <i class="fas fa-info-circle mr-1"></i>
        Kode kategori dibuat otomatis dari nama dan tidak bisa diubah. Kategori yang sudah dipakai oleh tagihan tidak bisa dihapus.
    </p>
</div>
@endsection
