@extends('layouts.app')
@section('title', 'Master Beasiswa')
@section('page-title', 'Master Jenis Beasiswa')

@section('content')
<div class="space-y-4 max-w-3xl">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <i class="fas fa-check-circle"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Tambah baru --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Tambah Jenis Beasiswa</h2>
        <form method="POST" action="{{ route('bendahara.jenis-beasiswa.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Beasiswa <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           placeholder="Contoh: KIP, Beasiswa Prestasi"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sumber</label>
                    <select name="sumber"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                        <option value="">— Pilih sumber —</option>
                        @foreach($sumberOptions as $opt)
                        <option value="{{ $opt }}" {{ old('sumber') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="deskripsi" rows="2"
                          placeholder="Keterangan singkat tentang beasiswa ini..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Tambah Beasiswa
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-700">{{ $list->count() }} Jenis Beasiswa</p>
            <p class="text-xs text-gray-400">Kode digunakan saat import Excel</p>
        </div>

        @if($list->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <i class="fas fa-graduation-cap text-3xl mb-2 block"></i>
            <p class="text-sm">Belum ada jenis beasiswa. Tambahkan di atas.</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($list as $item)
            <div x-data="{ editing: false }" class="px-4 py-3">

                {{-- Tampil mode --}}
                <div x-show="!editing" class="flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-800 text-sm">{{ $item->nama }}</p>
                            <code class="text-[10px] bg-gray-100 text-gray-500 rounded px-1.5 py-0.5 font-mono">{{ $item->kode }}</code>
                            @if($item->sumber)
                            <span class="text-[10px] bg-blue-50 text-blue-600 border border-blue-200 rounded-full px-2 py-0.5">{{ $item->sumber }}</span>
                            @endif
                            @if(!$item->is_aktif)
                            <span class="text-[10px] bg-gray-100 text-gray-400 rounded-full px-2 py-0.5">Nonaktif</span>
                            @endif
                        </div>
                        @if($item->deskripsi)
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $item->deskripsi }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Toggle aktif --}}
                        <form method="POST" action="{{ route('bendahara.jenis-beasiswa.toggle', $item) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs px-2 py-1 rounded border {{ $item->is_aktif ? 'text-green-600 border-green-200 hover:bg-green-50' : 'text-gray-400 border-gray-200 hover:bg-gray-50' }}">
                                <i class="fas {{ $item->is_aktif ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </button>
                        </form>
                        <button type="button" @click="editing = true"
                                class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1 rounded border border-blue-200 hover:border-blue-400">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <form method="POST" action="{{ route('bendahara.jenis-beasiswa.destroy', $item) }}"
                              onsubmit="return confirm('Hapus beasiswa {{ addslashes($item->nama) }}? Data penerima yang sudah tercatat tidak akan terpengaruh.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded border border-red-200 hover:border-red-400">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Edit mode --}}
                <div x-show="editing" x-cloak>
                    <form method="POST" action="{{ route('bendahara.jenis-beasiswa.update', $item) }}" class="space-y-2">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-0.5">Nama</label>
                                <input type="text" name="nama" value="{{ $item->nama }}" required
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-0.5">Sumber</label>
                                <select name="sumber"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                                    <option value="">— Pilih sumber —</option>
                                    @foreach($sumberOptions as $opt)
                                    <option value="{{ $opt }}" {{ $item->sumber == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Deskripsi</label>
                            <textarea name="deskripsi" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">{{ $item->deskripsi }}</textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">
                                <i class="fas fa-save mr-1"></i>Simpan
                            </button>
                            <button type="button" @click="editing = false"
                                    class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <p class="text-xs text-gray-400 px-1">
        <i class="fas fa-info-circle mr-1"></i>
        Kode (<code class="bg-gray-100 rounded px-1">kode</code>) digunakan pada kolom <code class="bg-gray-100 rounded px-1">kode_beasiswa</code> saat import Excel penerima beasiswa.
    </p>
</div>
@endsection
