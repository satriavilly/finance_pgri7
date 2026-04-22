@extends('layouts.app')
@section('title', 'Kelola Tagihan')
@section('page-title', 'Tagihan Kelas ' . $kelas->nama)

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">Tahun Ajaran: {{ $kelas->tahunAjaran->nama }}</p>
        <div class="flex gap-2">
            @if(!$jenisTagihan->isEmpty())
            <form method="POST" action="{{ route('wali-kelas.tagihan.distribusi-ulang') }}"
                  x-data
                  x-on:submit.prevent="
                    if(confirm('Distribusikan semua tagihan aktif ke siswa yang belum memilikinya?\n\nSiswa lama yang sudah punya tagihan tidak akan terpengaruh.'))
                        $el.submit()
                  ">
                @csrf
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    Distribusi ke Siswa Baru
                </button>
            </form>
            @endif
            <a href="{{ route('wali-kelas.tagihan.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Tagihan Baru
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($jenisTagihan->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>Belum ada tagihan dibuat. <a href="{{ route('wali-kelas.tagihan.create') }}" class="text-blue-600 hover:underline">Buat sekarang</a>.</p>
            </div>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama Tagihan</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">Kategori</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-medium">Nominal</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">Cicilan</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">Jatuh Tempo</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($jenisTagihan as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $item->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ \App\Models\JenisTagihan::kategoriLabel()[$item->kategori] }}</td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if($item->is_cicilan)
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ $item->jumlah_cicilan }}x cicilan</span>
                            @else
                                <span class="text-gray-400 text-xs">Lunas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $item->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="{{ $item->is_aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} text-xs px-2 py-0.5 rounded-full">
                                {{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">
                {{ $jenisTagihan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
