@extends('layouts.app')
@section('title', 'Kelola SPP')
@section('page-title', 'Kelola SPP')

@section('content')
<div class="space-y-4">

    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">Daftar periode SPP yang sudah dibuat</p>
        <a href="{{ route('bendahara.spp.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Buat SPP Baru
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if($periodeList->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-file-invoice-dollar text-4xl mb-3 block"></i>
        <p class="font-medium mb-1">Belum ada SPP yang dibuat</p>
        <p class="text-sm mb-4">Buat SPP pertama untuk mendistribusikan tagihan ke seluruh siswa.</p>
        <a href="{{ route('bendahara.spp.create') }}" class="inline-block bg-blue-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-blue-700">
            Buat SPP Sekarang
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($periodeList as $row)
        @php
            $pct = $row->total_nominal_all > 0 ? min(100, round($row->terkumpul / $row->total_nominal_all * 100)) : 0;
            [$tahun, $bulan] = explode('-', $row->deskripsi);
            $bulanNama = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                          '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'][$bulan] ?? $bulan;
        @endphp
        <a href="{{ route('bendahara.spp.show', $row->deskripsi) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow block border border-transparent hover:border-blue-200">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-semibold text-gray-800">{{ $bulanNama }} {{ $tahun }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $row->jumlah_kelas }} kelas · {{ $row->total_siswa }} siswa</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full {{ $pct >= 100 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $pct }}% lunas
                </span>
            </div>

            <div class="space-y-1 mb-3">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Terkumpul</span>
                    <span class="font-semibold text-green-600">Rp {{ number_format($row->terkumpul, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Target</span>
                    <span>Rp {{ number_format($row->total_nominal_all, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="h-2 bg-gray-100 rounded-full">
                <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' }} transition-all"
                     style="width: {{ $pct }}%"></div>
            </div>

            <div class="flex justify-between mt-3 text-xs">
                <span class="text-green-600 font-medium">{{ $row->lunas }} lunas</span>
                <span class="text-red-500 font-medium">{{ $row->total_siswa - $row->lunas }} belum</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
