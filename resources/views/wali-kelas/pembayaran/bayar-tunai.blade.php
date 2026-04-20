@extends('layouts.app')
@section('title', 'Input Bayar Tunai')
@section('page-title', 'Input Pembayaran Tunai')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-sm text-gray-500">Siswa</p>
        <p class="font-semibold text-gray-800">{{ $tagihan->siswa->nama }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ $tagihan->jenisTagihan->nama }}</p>
        <div class="grid grid-cols-3 gap-3 mt-3 text-sm bg-gray-50 rounded-lg p-3">
            <div>
                <p class="text-gray-500 text-xs">Total</p>
                <p class="font-medium">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Terbayar</p>
                <p class="font-medium text-green-600">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Sisa</p>
                <p class="font-medium text-red-600">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <form method="POST" action="{{ route('wali-kelas.pembayaran.bayar-tunai', $tagihan->id) }}">
            @csrf
            <div class="space-y-4">

                @if($detail['cicilan']->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Cicilan</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-blue-400">
                            <input type="radio" name="cicilan_id" value="" checked class="text-blue-600">
                            <span class="text-sm text-gray-700">Bayar di luar cicilan / pelunasan</span>
                        </label>
                        @foreach($detail['cicilan'] as $cicilan)
                        @if($cicilan->status === 'belum_bayar')
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-blue-400">
                            <input type="radio" name="cicilan_id" value="{{ $cicilan->id }}" class="text-blue-600">
                            <div class="flex-1">
                                <p class="text-sm font-medium">Cicilan ke-{{ $cicilan->ke }}</p>
                                <p class="text-xs text-gray-500">Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}
                                    @if($cicilan->due_date) | Jatuh tempo: {{ $cicilan->due_date->format('d M Y') }} @endif
                                </p>
                            </div>
                        </label>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Dibayar (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal" value="{{ old('nominal') }}" required min="1000"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                           placeholder="Masukkan nominal yang diterima">
                    @error('nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="catatan" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                              placeholder="Catatan pembayaran">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    Catat Pembayaran
                </button>
                <a href="{{ route('wali-kelas.siswa.tagihan', $tagihan->siswa_id) }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
