@extends('layouts.app')
@section('title', 'Kelola SPP')
@section('page-title', 'Kelola SPP')

@section('content')
@php
$bulanNama = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
              '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
@endphp
<div class="space-y-4">

    <div class="flex justify-between items-center">
        @include('layouts.partials.tahun-ajaran-select', [
            'allTahunAjaran' => $allTahunAjaran,
            'selectedTa'     => $tahunAjaran,
            'taRoute'        => 'bendahara.spp.index',
        ])
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

    @if($periodeByTahun->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-file-invoice-dollar text-4xl mb-3 block"></i>
        <p class="font-medium mb-1">Belum ada SPP yang dibuat</p>
        <p class="text-sm mb-4">Buat SPP untuk mendistribusikan tagihan ke seluruh siswa.</p>
        <a href="{{ route('bendahara.spp.create') }}" class="inline-block bg-blue-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-blue-700">
            Buat SPP Sekarang
        </a>
    </div>
    @else
    <div class="space-y-6">
        @foreach($periodeByTahun as $tahun => $bulanList)
        @php
            $totalSiswa   = $bulanList->sum('total_siswa');
            $totalLunas   = $bulanList->sum('lunas');
            $totalKumpul  = $bulanList->sum('terkumpul');
            $totalTarget  = $bulanList->sum('total_nominal_all');
            $pctTahun     = $totalTarget > 0 ? min(100, round($totalKumpul / $totalTarget * 100)) : 0;
        @endphp
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">

            {{-- Header tahun --}}
            <div class="px-5 py-4 border-b bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-gray-800 text-base">SPP Tahun {{ $tahun }}</span>
                    <span class="text-xs text-gray-500">{{ $bulanList->first()->jumlah_kelas }} kelas</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pctTahun >= 100 ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $pctTahun }}% terkumpul
                    </span>
                </div>
                <span class="text-sm font-semibold text-indigo-700">Rp {{ number_format($totalKumpul, 0, ',', '.') }}</span>
            </div>

            {{-- Tabel bulan --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 border-b">
                            <th class="text-left px-5 py-2 font-medium">Bulan</th>
                            <th class="text-center px-3 py-2 font-medium">Kelas</th>
                            <th class="text-center px-3 py-2 font-medium">Siswa</th>
                            <th class="text-center px-3 py-2 font-medium">Lunas</th>
                            <th class="text-right px-3 py-2 font-medium">Terkumpul</th>
                            <th class="text-right px-5 py-2 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach(range(1,12) as $b)
                        @php
                            $bKey  = str_pad($b, 2, '0', STR_PAD_LEFT);
                            $periode = "{$tahun}-{$bKey}";
                            $row   = $bulanList->firstWhere('deskripsi', $periode);
                            $pct   = $row && $row->total_nominal_all > 0 ? min(100, round($row->terkumpul / $row->total_nominal_all * 100)) : 0;
                        @endphp
                        <tr class="{{ $row ? 'hover:bg-gray-50' : 'opacity-40' }}">
                            <td class="px-5 py-3 font-medium text-gray-700">{{ $bulanNama[$bKey] }}</td>
                            <td class="px-3 py-3 text-center text-gray-500">{{ $row?->jumlah_kelas ?? '-' }}</td>
                            <td class="px-3 py-3 text-center text-gray-500">{{ $row?->total_siswa ?? '-' }}</td>
                            <td class="px-3 py-3 text-center">
                                @if($row)
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 h-1.5 bg-gray-200 rounded-full">
                                        <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $pct }}%</span>
                                </div>
                                @else
                                <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $row ? 'Rp '.number_format($row->terkumpul, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($row)
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('bendahara.spp.show', $periode) }}"
                                       class="text-xs text-blue-600 hover:underline">Lihat</a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('bendahara.spp.edit', $periode) }}"
                                       class="text-xs text-amber-600 hover:underline">Edit</a>
                                </div>
                                @else
                                <span class="text-xs text-gray-300">Belum dibuat</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
