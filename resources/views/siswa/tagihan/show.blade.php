@extends('layouts.app')
@section('title', 'Detail Tagihan')
@section('page-title', 'Detail Tagihan')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="font-semibold text-gray-800 text-lg">{{ $tagihan->jenisTagihan->nama }}</p>
                <p class="text-sm text-gray-500">{{ \App\Models\JenisTagihan::kategoriLabel()[$tagihan->jenisTagihan->kategori] }}</p>
            </div>
            <span class="text-sm px-3 py-1 rounded-full
                {{ $tagihan->status === 'lunas' ? 'bg-green-100 text-green-700' :
                   ($tagihan->status === 'cicilan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ \App\Models\TagihanSiswa::statusLabel()[$tagihan->status] }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-4 bg-gray-50 rounded-lg p-4">
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">Total Tagihan</p>
                <p class="font-bold text-gray-800">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">Terbayar</p>
                <p class="font-bold text-green-600">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">Sisa</p>
                <p class="font-bold text-red-600">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($tagihan->status !== 'lunas' && $tagihan->status !== 'void')
        <div class="mt-4">
            <a href="{{ route('siswa.tagihan.upload', $tagihan->id) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg inline-block">
                Upload Bukti Bayar
            </a>
        </div>
        @endif
    </div>

    @if($detail['cicilan']->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-3">Jadwal Cicilan</h3>
        <div class="space-y-2">
            @foreach($detail['cicilan'] as $cicilan)
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <div>
                    <p class="text-sm font-medium">Cicilan ke-{{ $cicilan->ke }}</p>
                    @if($cicilan->due_date)
                    <p class="text-xs text-gray-500">Jatuh tempo: {{ $cicilan->due_date->format('d M Y') }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium">Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $cicilan->status === 'lunas' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ $cicilan->status === 'lunas' ? 'Lunas' : 'Belum Bayar' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($detail['pembayaran']->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-3">Riwayat Pembayaran</h3>
        <div class="space-y-3">
            @foreach($detail['pembayaran'] as $bayar)
            <div class="border border-gray-200 rounded-lg p-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">{{ \App\Models\Pembayaran::metodeLabel()[$bayar->metode] }} | {{ $bayar->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $bayar->status_verifikasi === 'approved' ? 'bg-green-100 text-green-700' :
                           ($bayar->status_verifikasi === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ \App\Models\Pembayaran::statusVerifikasiLabel()[$bayar->status_verifikasi] }}
                    </span>
                </div>
                @if($bayar->bukti_bayar_path)
                <div class="mt-2">
                    <a href="{{ route('bukti.show', $bayar->id) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 hover:underline">
                        <i class="fas fa-image"></i> Lihat Bukti Bayar
                    </a>
                </div>
                @endif
                @if($bayar->catatan_tolak)
                <p class="text-xs text-red-600 mt-1">Alasan ditolak: {{ $bayar->catatan_tolak }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
