@extends('layouts.app')
@section('title', 'Dashboard Bendahara')
@section('page-title', 'Dashboard Bendahara')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total Tagihan</p>
            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($data['totalTagihan'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Terkumpul</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($data['totalTerbayar'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Tunggakan</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($data['totalTunggakan'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Pemasukan Bulan Ini</p>
            <p class="text-lg font-bold text-blue-700">Rp {{ number_format($data['pemasukanBulanIni'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $data['menungguVerifikasi'] ?? 0 }}</p>
        </div>
    </div>
</div>
@endsection
