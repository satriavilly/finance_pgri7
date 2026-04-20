@extends('layouts.app')
@section('title', 'Tagihan Saya')
@section('page-title', 'Tagihan Saya')

@section('content')
<div class="space-y-4">
    @forelse($tagihan as $item)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex justify-between items-start">
            <div>
                <p class="font-medium text-gray-800">{{ $item->jenisTagihan->nama }}</p>
                <p class="text-xs text-gray-500">{{ \App\Models\JenisTagihan::kategoriLabel()[$item->jenisTagihan->kategori] }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full
                {{ $item->status === 'lunas' ? 'bg-green-100 text-green-700' :
                   ($item->status === 'cicilan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ \App\Models\TagihanSiswa::statusLabel()[$item->status] }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-2 text-sm">
            <div>
                <p class="text-xs text-gray-500">Total</p>
                <p class="font-medium">Rp {{ number_format($item->nominal_total, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Terbayar</p>
                <p class="font-medium text-green-600">Rp {{ number_format($item->nominal_terbayar, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Sisa</p>
                <p class="font-medium text-red-600">Rp {{ number_format($item->sisa_tagihan, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <a href="{{ route('siswa.tagihan.show', $item->id) }}"
               class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">Detail</a>
            @if($item->status !== 'lunas' && $item->status !== 'void')
            <a href="{{ route('siswa.tagihan.upload', $item->id) }}"
               class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg">Upload Bukti</a>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
        <p>Tidak ada tagihan aktif.</p>
    </div>
    @endforelse

    {{ $tagihan->links() }}
</div>
@endsection
