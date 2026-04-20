@extends('layouts.app')
@section('title', 'Dashboard Wali Kelas')
@section('page-title', 'Dashboard — ' . ($kelas?->nama ?? 'Kelas'))

@section('content')
<div class="space-y-6">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total Siswa</p>
            <p class="text-2xl font-bold text-gray-800">{{ $data['totalSiswa'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Sudah Lunas Semua</p>
            <p class="text-2xl font-bold text-green-600">{{ $data['sudahLunasSemua'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $data['menungguVerifikasi'] ?? 0 }}</p>
            @if(($data['menungguVerifikasi'] ?? 0) > 0)
                <a href="{{ route('wali-kelas.pembayaran.verifikasi') }}" class="text-xs text-blue-500 hover:underline">Lihat →</a>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total Terkumpul</p>
            <p class="text-xl font-bold text-blue-700">Rp {{ number_format($data['totalTerbayar'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    @if($kelas)
    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Aksi Cepat — Kelas {{ $kelas->nama }}</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('wali-kelas.tagihan.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Tagihan Baru
            </a>
            <a href="{{ route('wali-kelas.tagihan.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg">
                Daftar Tagihan
            </a>
            <a href="{{ route('wali-kelas.pembayaran.verifikasi') }}"
               class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-sm px-4 py-2 rounded-lg">
                Verifikasi Bukti Bayar
                @if(($data['menungguVerifikasi'] ?? 0) > 0)
                    <span class="ml-1 bg-yellow-400 text-white text-xs rounded-full px-1.5">{{ $data['menungguVerifikasi'] }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Daftar Siswa --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Daftar Siswa Kelas {{ $kelas->nama }}</h2>
        @if($kelas->siswa->isEmpty())
            <p class="text-gray-500 text-sm">Belum ada siswa di kelas ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">NIS</th>
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">Nama</th>
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">Status</th>
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($kelas->siswa as $siswa)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 text-gray-500">{{ $siswa->nis }}</td>
                            <td class="py-2 px-3 font-medium text-gray-800">{{ $siswa->nama }}</td>
                            <td class="py-2 px-3">
                                @php $adaTunggakan = $siswa->tagihanBelumLunas->count() > 0; @endphp
                                @if($adaTunggakan)
                                    <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">Ada Tunggakan</span>
                                @else
                                    <span class="bg-green-100 text-green-600 text-xs px-2 py-0.5 rounded-full">Lunas</span>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                <a href="{{ route('wali-kelas.siswa.tagihan', $siswa->id) }}"
                                   class="text-blue-600 hover:underline text-xs">Lihat Tagihan</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
        <p class="text-yellow-700">Anda belum ditugaskan sebagai wali kelas pada tahun ajaran aktif.</p>
    </div>
    @endif

</div>
@endsection
