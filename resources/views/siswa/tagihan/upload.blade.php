@extends('layouts.app')
@section('title', 'Upload Bukti Bayar')
@section('page-title', 'Upload Bukti Bayar')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
        <p class="text-sm text-gray-500">Tagihan</p>
        <p class="font-semibold text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
        <p class="text-sm text-gray-600">Sisa: Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <form method="POST" action="{{ route('siswa.tagihan.upload.store', $tagihan->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="metode" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Dibayar (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal" value="{{ old('nominal') }}" required min="1000"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                @if($tagihan->cicilan->where('status', 'belum_bayar')->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Untuk Cicilan (opsional)</label>
                    <select name="cicilan_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Pilih cicilan...</option>
                        @foreach($tagihan->cicilan->where('status', 'belum_bayar') as $cicilan)
                        <option value="{{ $cicilan->id }}">Cicilan ke-{{ $cicilan->ke }} — Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bukti Bayar <span class="text-red-500">*</span></label>
                    <input type="file" name="bukti_bayar" required accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, atau PDF. Maksimal 2MB.</p>
                    @error('bukti_bayar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="catatan" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                              placeholder="Informasi tambahan">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    Upload Bukti Bayar
                </button>
                <a href="{{ route('siswa.tagihan.show', $tagihan->id) }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
