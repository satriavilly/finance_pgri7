@extends('layouts.app')
@section('title', 'Dashboard Siswa')
@section('page-title', 'Tagihan Saya')

@section('content')
<div class="space-y-6">
    @if($siswa)
    {{-- Info Siswa --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <span class="text-blue-700 font-bold text-lg">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $siswa->nama }}</p>
            <p class="text-sm text-gray-500">NIS: {{ $siswa->nis }} | Kelas {{ $siswa->kelas?->nama }}</p>
        </div>
    </div>

    {{-- Tagihan Aktif --}}
    @php
        $tagihanAktif = $siswa->tagihanSiswa->whereIn('status', ['belum_bayar', 'cicilan']);
        $tagihanLunas = $siswa->tagihanSiswa->where('status', 'lunas');
    @endphp

    @if($tagihanAktif->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Tagihan Aktif ({{ $tagihanAktif->count() }})</h2>
        <div class="space-y-3">
            @foreach($tagihanAktif as $tagihan)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ \App\Models\JenisTagihan::kategoriLabel()[$tagihan->jenisTagihan->kategori] }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $tagihan->status === 'cicilan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700' }}">
                        {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
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
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('siswa.tagihan.show', $tagihan->id) }}"
                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">Detail</a>
                    <a href="{{ route('siswa.tagihan.upload', $tagihan->id) }}"
                       class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg">Upload Bukti Bayar</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
        <p class="text-green-700 font-medium">Semua tagihan sudah lunas!</p>
    </div>
    @endif

    @if($tagihanLunas->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Riwayat Pembayaran</h2>
        <div class="space-y-2">
            @foreach($tagihanLunas as $tagihan)
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
                    <span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full">Lunas</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
        <p class="text-yellow-700">Data siswa belum terdaftar. Hubungi wali kelas atau admin.</p>
    </div>
    @endif
</div>
@endsection
