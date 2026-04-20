@extends('layouts.app')
@section('title', 'Tagihan ' . $siswa->nama)
@section('page-title', 'Tagihan — ' . $siswa->nama)

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            <span class="text-blue-700 font-bold">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $siswa->nama }}</p>
            <p class="text-sm text-gray-500">NIS: {{ $siswa->nis }} | Kelas {{ $siswa->kelas?->nama }}</p>
        </div>
    </div>

    @forelse($siswa->tagihanSiswa->where('status', '!=', 'void') as $tagihan)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex justify-between items-start">
            <div>
                <p class="font-medium text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
                <p class="text-xs text-gray-500">{{ \App\Models\JenisTagihan::kategoriLabel()[$tagihan->jenisTagihan->kategori] }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full
                {{ $tagihan->status === 'lunas' ? 'bg-green-100 text-green-700' :
                   ($tagihan->status === 'cicilan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
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

        @if($tagihan->status !== 'lunas')
        <div class="mt-3">
            <a href="{{ route('wali-kelas.pembayaran.form-tunai', $tagihan->id) }}"
               class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Input Bayar Tunai
            </a>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
        <p>Belum ada tagihan untuk siswa ini.</p>
    </div>
    @endforelse
</div>
@endsection
