@extends('layouts.app')
@section('title', 'Edit '.$namaSpp)
@section('page-title', 'Edit '.$namaSpp)

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('bendahara.spp.update', $periode) }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">

                {{-- Info periode (readonly) --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Periode: <strong>{{ $namaSpp }}</strong>
                </div>

                {{-- Jatuh Tempo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $dueDate) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <p class="text-xs text-gray-400 mt-1">Akan diperbarui ke semua tagihan yang belum lunas.</p>
                </div>

                {{-- Tarif per Angkatan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Tarif per Angkatan <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        @foreach($perTingkat as $tingkat => $data)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-700 text-sm">Angkatan {{ $tingkat }}</span>
                                <span class="text-xs text-gray-400">
                                    {{ $data['kelas_list']->pluck('nama')->implode(', ') }}
                                </span>
                            </div>
                            <div class="relative max-w-xs">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" name="nominal[{{ $tingkat }}]"
                                       value="{{ old("nominal.$tingkat", (int) $data['nominal']) }}"
                                       required min="1000"
                                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            @error("nominal.$tingkat")
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tarif baru hanya berlaku untuk tagihan yang belum bayar.
                    </p>
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('bendahara.spp.show', $periode) }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
