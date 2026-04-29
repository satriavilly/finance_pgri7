@extends('layouts.app')
@section('title', 'Edit Tagihan')
@section('page-title', 'Edit Tagihan')

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

        {{-- Info kelas (readonly) --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-600 mb-4">
            <i class="fas fa-school mr-2 text-gray-400"></i>
            Kelas: <strong>{{ $tagihan->kelas->nama }}</strong>
            <span class="text-gray-400 ml-2">(tidak dapat diubah)</span>
        </div>

        <form method="POST" action="{{ route('admin-tu.tagihan.update', $tagihan) }}"
              x-data="{
                  isCicilan: {{ old('is_cicilan', $tagihan->is_cicilan) ? 'true' : 'false' }},
                  display: '{{ old('total_nominal') ? number_format((int)old('total_nominal'), 0, ',', '.') : number_format((int)$tagihan->total_nominal, 0, ',', '.') }}',
                  raw: '{{ old('total_nominal', (int)$tagihan->total_nominal) }}',
                  format(val) {
                      let num = val.replace(/\D/g, '');
                      this.raw = num;
                      this.display = num ? parseInt(num).toLocaleString('id-ID') : '';
                  }
              }">
            @csrf @method('PUT')
            <div class="space-y-4">

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tagihan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $tagihan->nama) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach($kategoriList as $kat)
                        <option value="{{ $kat->kode }}" {{ old('kategori', $tagihan->kategori) === $kat->kode ? 'selected' : '' }}>{{ $kat->nama }}</option>
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
                               x-model="display"
                               @input="format($event.target.value)"
                               required
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <input type="hidden" name="total_nominal" :value="raw">
                    @error('total_nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Cicilan --}}
                <div class="border border-gray-200 rounded-xl p-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_cicilan" value="1"
                               x-model="isCicilan"
                               {{ old('is_cicilan', $tagihan->is_cicilan) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Tagihan ini bisa dicicil</span>
                            <p class="text-xs text-gray-400 mt-0.5">Siswa dapat membayar sebagian demi sebagian, tanpa batasan jumlah cicilan.</p>
                        </div>
                    </label>
                </div>

                {{-- Jatuh Tempo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $tagihan->due_date?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('due_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                              placeholder="Keterangan tambahan (opsional)">{{ old('deskripsi', $tagihan->deskripsi) }}</textarea>
                </div>

            </div>

            <p class="text-xs text-gray-400 mt-4">
                <i class="fas fa-info-circle mr-1"></i>
                Perubahan nominal hanya berlaku untuk tagihan siswa yang belum bayar.
            </p>

            <div class="flex gap-3 mt-4">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin-tu.tagihan.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
