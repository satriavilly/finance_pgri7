@extends('layouts.app')
@section('title', 'Dashboard Bendahara')
@section('page-title', 'Dashboard Bendahara')

@php
$totalTagihan  = $data['totalTagihan']  ?? 0;
$totalTerbayar = $data['totalTerbayar'] ?? 0;
$globalPct     = $totalTagihan > 0 ? min(100, round($totalTerbayar / $totalTagihan * 100)) : 0;
$metodeLabel   = ['tunai'=>'Tunai','transfer'=>'Transfer Bank','qris'=>'QRIS'];
$metodeIcon    = ['tunai'=>'fa-money-bill-wave','transfer'=>'fa-university','qris'=>'fa-qrcode'];
$metodeColor   = ['tunai'=>'text-green-600','transfer'=>'text-blue-600','qris'=>'text-purple-600'];
$meterBg       = ['tunai'=>'bg-green-500','transfer'=>'bg-blue-500','qris'=>'bg-purple-500'];
@endphp

@section('content')
<div class="space-y-5">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ route('bendahara.laporan.tagihan') }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 mb-1">Total Tagihan</p>
            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($totalTagihan/1000000,1) }}jt</p>
            <p class="text-xs text-gray-400">{{ number_format($totalTagihan,0,',','.') }}</p>
        </a>
        <a href="{{ route('bendahara.laporan.tagihan') }}?status=lunas" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 mb-1">Terkumpul</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($totalTerbayar/1000000,1) }}jt</p>
            <div class="mt-1.5 h-1.5 bg-gray-100 rounded-full"><div class="h-1.5 bg-green-500 rounded-full" style="width:{{ $globalPct }}%"></div></div>
        </a>
        <a href="{{ route('bendahara.laporan.tagihan') }}?status=belum_bayar" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 mb-1">Tunggakan</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format(($data['totalTunggakan']??0)/1000000,1) }}jt</p>
            <p class="text-xs text-gray-400">{{ 100 - $globalPct }}% belum terbayar</p>
        </a>
        <a href="{{ route('bendahara.laporan.transaksi') }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-indigo-500 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 mb-1">Bulan {{ now()->translatedFormat('F') }}</p>
            <p class="text-lg font-bold text-indigo-700">Rp {{ number_format(($data['pemasukanBulanIni']??0)/1000000,1) }}jt</p>
            <p class="text-xs text-gray-400">pemasukan bulan ini</p>
        </a>
        <a href="{{ route('bendahara.laporan.transaksi') }}?status_verifikasi=pending" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 mb-1">Perlu Verifikasi</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $data['menungguVerifikasi'] ?? 0 }}</p>
            <p class="text-xs text-gray-400">pembayaran pending</p>
        </a>
    </div>

    {{-- Row 2: Line trend + Donut status --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Line: Arus Kas Bulanan (2/3 width) --}}
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-1">
                <p class="font-semibold text-gray-700">Arus Kas Bulanan</p>
                <span class="text-xs text-gray-400">{{ $data['tahunIni'] ?? now()->year }}</span>
            </div>
            <p class="text-xs text-gray-400 mb-4">Total pemasukan yang diterima per bulan</p>
            <canvas id="chartBulanan" height="110"></canvas>
        </div>

        {{-- Donut: Status Tagihan (1/3 width) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Status Tagihan</p>
            <p class="text-xs text-gray-400 mb-4">Distribusi seluruh tagihan aktif</p>
            <div class="flex flex-col items-center gap-4">
                <div class="relative w-36 h-36">
                    <canvas id="chartStatus"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-extrabold text-gray-700">{{ $globalPct }}%</span>
                        <span class="text-xs text-gray-400">lunas</span>
                    </div>
                </div>
                <div class="w-full space-y-1.5">
                    @foreach(['lunas'=>['Lunas','bg-green-500'],'cicilan'=>['Cicilan','bg-yellow-400'],'belum_bayar'=>['Belum Bayar','bg-red-500']] as $k=>[$lbl,$clr])
                    @php $val = $data['statusDist'][$k] ?? 0; @endphp
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full {{ $clr }}"></span><span class="text-gray-600">{{ $lbl }}</span></div>
                        <span class="font-semibold text-gray-800">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Bar per kelas + Metode --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Bar per kelas (2/3) --}}
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Terkumpul vs Tunggakan per Kelas</p>
            <p class="text-xs text-gray-400 mb-4">Perbandingan nominal yang sudah terkumpul dan masih tertunggak</p>
            <canvas id="chartKelas" height="120"></canvas>
        </div>

        {{-- Metode Pembayaran (1/3) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Metode Pembayaran</p>
            <p class="text-xs text-gray-400 mb-4">Breakdown nominal per metode</p>
            <canvas id="chartMetode" class="mb-3" height="130"></canvas>
            <div class="space-y-2 mt-2">
                @foreach($data['metodeData'] ?? [] as $metode => $row)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <i class="fas {{ $metodeIcon[$metode] ?? 'fa-money-bill' }} {{ $metodeColor[$metode] ?? 'text-gray-500' }}"></i>
                        <span class="text-gray-600">{{ $metodeLabel[$metode] ?? $metode }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-gray-800">{{ $row->jumlah }}×</span>
                        <span class="text-gray-400 ml-1">Rp {{ number_format($row->total/1000000,1) }}jt</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Row 4: Pembayaran Terbaru + Per-kelas breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Transaksi Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-4">Transaksi Terbaru</p>
            @if(($data['pembayaranTerbaru'] ?? collect())->isEmpty())
            <p class="text-center text-sm text-gray-400 py-4">Belum ada transaksi.</p>
            @else
            <div class="space-y-2">
                @foreach($data['pembayaranTerbaru'] as $bayar)
                @php $m = $metodeLabel[$bayar->metode] ?? $bayar->metode; @endphp
                <div class="flex items-center gap-3 text-sm py-2 border-b border-gray-50 last:border-0">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $metodeIcon[$bayar->metode] ?? 'fa-money-bill' }} text-green-600 text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate text-xs">{{ $bayar->tagihanSiswa?->siswa?->nama }}</p>
                        <p class="text-gray-400 text-xs">{{ $bayar->tagihanSiswa?->jenisTagihan?->nama }} · {{ $m }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-semibold text-green-600 text-xs">Rp {{ number_format($bayar->nominal,0,',','.') }}</p>
                        <p class="text-gray-400 text-xs">{{ \Carbon\Carbon::parse($bayar->created_at)->format('d M') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Breakdown Per Kelas (collapsible) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-4">Rekap Per Kelas</p>
            @if(($data['perKelas'] ?? collect())->isEmpty())
            <p class="text-center text-sm text-gray-400 py-4">Belum ada data kelas.</p>
            @else
            <div class="space-y-2">
                @foreach($data['perKelas'] as $row)
                <div x-data="{ open: false }">
                    <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-lg p-2 -mx-2" @click="open=!open">
                        <div class="text-gray-300 transition-transform duration-150" :class="open?'rotate-90':''">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-xs font-semibold text-gray-700">Kelas {{ $row['kelas']->nama }}</p>
                                <p class="text-xs font-semibold {{ $row['tunggakan']>0?'text-red-500':'text-green-600' }}">{{ $row['pct'] }}%</p>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full">
                                <div class="h-1.5 rounded-full {{ $row['pct']>=100?'bg-green-500':'bg-blue-500' }}" style="width:{{ $row['pct'] }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div x-show="open" x-cloak class="ml-5 mt-1 mb-2 grid grid-cols-3 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2 text-center">
                            <p class="text-gray-400">Tagihan</p>
                            <p class="font-semibold text-gray-700">Rp {{ number_format($row['total']/1000000,1) }}jt</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-2 text-center">
                            <p class="text-gray-400">Terkumpul</p>
                            <p class="font-semibold text-green-600">Rp {{ number_format($row['terbayar']/1000000,1) }}jt</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-2 text-center">
                            <p class="text-gray-400">Tunggakan</p>
                            <p class="font-semibold text-red-500">Rp {{ number_format($row['tunggakan']/1000000,1) }}jt</p>
                        </div>
                        <div class="col-span-3 flex justify-end">
                            <a href="{{ route('bendahara.kelas.siswa', $row['kelas']->id) }}"
                               class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                <i class="fas fa-users"></i> Lihat per siswa
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const fmt = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

// ─── Line: Arus Kas Bulanan ───────────────────────────────
const bulananData = {!! json_encode(array_values($data['pemasukanBulanan'] ?? [])) !!};
new Chart(document.getElementById('chartBulanan'), {
    type: 'line',
    data: {
        labels: {!! json_encode($data['bulanLabels'] ?? []) !!},
        datasets: [{
            label: 'Pemasukan',
            data: bulananData,
            borderColor: '#6366F1',
            backgroundColor: 'rgba(99,102,241,0.08)',
            fill: true,
            tension: 0.45,
            pointBackgroundColor: '#6366F1',
            pointRadius: 4,
            pointHoverRadius: 7,
        }]
    },
    options: {
        plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: ctx => fmt(ctx.parsed.y) } } },
        scales: {
            y: { ticks:{ callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt', font:{size:10} }, grid:{color:'#f3f4f6'} },
            x: { ticks:{ font:{size:10} }, grid:{display:false} }
        }
    }
});

// ─── Donut: Status Tagihan ────────────────────────────────
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: ['Lunas','Cicilan','Belum Bayar'],
        datasets: [{
            data: [
                {{ $data['statusDist']['lunas']       ?? 0 }},
                {{ $data['statusDist']['cicilan']     ?? 0 }},
                {{ $data['statusDist']['belum_bayar'] ?? 0 }},
            ],
            backgroundColor: ['#10B981','#FBBF24','#EF4444'],
            borderWidth: 0, hoverOffset: 6,
        }]
    },
    options: {
        cutout: '72%',
        plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: ctx => ` ${ctx.label}: ${ctx.parsed} tagihan` } } },
    }
});

// ─── Bar per Kelas ────────────────────────────────────────
@php
$kelasNama     = $data['perKelas']->pluck('kelas.nama')->toJson();
$kelasTerbayar = $data['perKelas']->pluck('terbayar')->toJson();
$kelasTunggak  = $data['perKelas']->pluck('tunggakan')->toJson();
@endphp
new Chart(document.getElementById('chartKelas'), {
    type: 'bar',
    data: {
        labels: {!! $kelasNama !!},
        datasets: [
            { label:'Terkumpul', data:{!! $kelasTerbayar !!}, backgroundColor:'#10B981', borderRadius:5 },
            { label:'Tunggakan', data:{!! $kelasTunggak !!},  backgroundColor:'#FCA5A5', borderRadius:5 },
        ]
    },
    options: {
        plugins: { legend:{position:'top',labels:{boxWidth:12,font:{size:11}}}, tooltip:{ callbacks:{ label: ctx => ctx.dataset.label+': '+fmt(ctx.parsed.y) } } },
        scales: {
            y: { ticks:{ callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt', font:{size:10} }, grid:{color:'#f3f4f6'} },
            x: { ticks:{font:{size:11}}, grid:{display:false} }
        }
    }
});

// ─── Donut: Metode Pembayaran ─────────────────────────────
@php
$metodeKeys  = collect($data['metodeData'] ?? [])->keys();
$metodeVals  = collect($data['metodeData'] ?? [])->map(fn($r) => $r->total)->values();
$metodeNames = $metodeKeys->map(fn($k) => match($k) { 'tunai'=>'Tunai','transfer'=>'Transfer Bank','qris'=>'QRIS',default=>$k });
@endphp
new Chart(document.getElementById('chartMetode'), {
    type: 'doughnut',
    data: {
        labels: {!! $metodeNames->toJson() !!},
        datasets: [{
            data: {!! $metodeVals->toJson() !!},
            backgroundColor: ['#10B981','#3B82F6','#8B5CF6'],
            borderWidth: 0, hoverOffset: 5,
        }]
    },
    options: {
        cutout: '65%',
        plugins: { legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10}, padding:8 } }, tooltip:{ callbacks:{ label: ctx => fmt(ctx.parsed) } } },
    }
});
</script>
@endpush
