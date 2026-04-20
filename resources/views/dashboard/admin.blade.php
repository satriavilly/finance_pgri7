@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-500 mb-1">Total Pengguna</p>
            <p class="text-3xl font-bold text-gray-800">{{ \App\Models\User::count() }}</p>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-500 hover:underline mt-1 block">Kelola →</a>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-500 mb-1">Tahun Ajaran Aktif</p>
            <p class="text-xl font-bold text-gray-800">{{ $tahunAjaran?->nama ?? 'Belum diset' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-500 mb-1">Total Kelas</p>
            <p class="text-3xl font-bold text-gray-800">
                {{ $tahunAjaran ? \App\Models\Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->count() : 0 }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Manajemen Sistem</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('admin.users.index') }}"
               class="flex flex-col items-center gap-2 p-4 border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-sm text-gray-700 font-medium">Manajemen User</span>
            </a>
        </div>
    </div>
</div>
@endsection
