@extends('layouts.app')
@section('title', 'Dashboard Orang Tua')
@section('page-title', 'Tagihan Anak')

@section('content')
<div class="space-y-6">
    @if($anak)
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <span class="text-blue-700 font-bold text-lg">{{ strtoupper(substr($anak->nama, 0, 1)) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $anak->nama }}</p>
            <p class="text-sm text-gray-500">NIS: {{ $anak->nis }} | Kelas {{ $anak->kelas?->nama }}</p>
        </div>
    </div>

    @foreach($anak->tagihanSiswa as $tagihan)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex justify-between items-center">
            <p class="font-medium text-gray-800">{{ $tagihan->jenisTagihan->nama }}</p>
            <span class="text-xs px-2 py-1 rounded-full
                {{ $tagihan->status === 'lunas' ? 'bg-green-100 text-green-700' :
                   ($tagihan->status === 'cicilan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] }}
            </span>
        </div>
        <div class="mt-2 text-sm text-gray-600">
            <span>Total: Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</span>
            <span class="mx-2">|</span>
            <span class="text-green-600">Terbayar: Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
        <p class="text-yellow-700">Data anak belum terdaftar. Hubungi wali kelas.</p>
    </div>
    @endif
</div>
@endsection
