@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-4">

    <p class="text-sm text-gray-500">
        Tahun Ajaran: <strong>{{ $tahunAjaran?->nama ?? '-' }}</strong>
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-400 mb-1">Total Tagihan Aktif</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalTagihan }}</p>
            <p class="text-xs text-gray-400 mt-1">Jenis tagihan yang sudah dibuat</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <p class="text-xs text-gray-400 mb-1">Total Kelas</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalKelas }}</p>
            <p class="text-xs text-gray-400 mt-1">Kelas aktif tahun ajaran ini</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Aksi Cepat</h3>
        <div class="flex gap-3">
            <a href="{{ route('admin-tu.tagihan.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i> Buat Tagihan Baru
            </a>
            <a href="{{ route('admin-tu.tagihan.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-list"></i> Lihat Semua Tagihan
            </a>
        </div>
    </div>

</div>
@endsection
