@extends('layouts.app')
@section('title', 'Dashboard Kepala Sekolah')
@section('page-title', 'Laporan Keuangan Sekolah')

@php
$kategoriLabel = \App\Models\JenisTagihan::kategoriLabel();
$totalTagihan  = $data['totalTagihan']  ?? 0;
$totalTerbayar = $data['totalTerbayar'] ?? 0;
$totalSiswa    = $data['totalSiswa']    ?? 0;
$siswaLunas    = $data['siswaLunas']    ?? 0;
$pct           = $totalTagihan > 0 ? round($totalTerbayar / $totalTagihan * 100, 1) : 0;
$tahunIni      = $data['tahunIni']      ?? now()->year;
@endphp

@section('content')
<div class="space-y-6">

    {{-- TA Header --}}
    @if($tahunAjaran)
    <div class="flex items-center gap-3 bg-gradient-to-r from-blue-700 to-blue-500 text-white rounded-2xl px-5 py-4 shadow">
        <i class="fas fa-graduation-cap text-2xl opacity-80"></i>
        <div>
            <p class="text-xs opacity-70 uppercase tracking-wide">Tahun Ajaran Aktif</p>
            <p class="font-bold text-lg">{{ $tahunAjaran->nama }}</p>
        </div>
        <div class="ml-auto text-right">
            <p class="text-xs opacity-70">Progress Pembayaran</p>
            <p class="text-2xl font-extrabold">{{ $pct }}%</p>
        </div>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Tagihan</p>
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-blue-600 text-sm"></i>
                </div>
            </div>
            <p class="text-xl font-extrabold text-gray-800">Rp {{ number_format($totalTagihan/1000000, 1) }}jt</p>
            <p class="text-xs text-gray-400 mt-1">{{ number_format($totalTagihan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Terkumpul</p>
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-sm"></i>
                </div>
            </div>
            <p class="text-xl font-extrabold text-green-600">Rp {{ number_format($totalTerbayar/1000000, 1) }}jt</p>
            <div class="mt-2 h-1.5 bg-gray-100 rounded-full"><div class="h-1.5 bg-green-500 rounded-full" style="width:{{ $pct }}%"></div></div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-red-500">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Tunggakan</p>
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
                </div>
            </div>
            <p class="text-xl font-extrabold text-red-600">Rp {{ number_format(($totalTagihan-$totalTerbayar)/1000000, 1) }}jt</p>
            <p class="text-xs text-gray-400 mt-1">{{ 100 - $pct }}% belum terbayar</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-purple-500">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Siswa Lunas</p>
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-sm"></i>
                </div>
            </div>
            <p class="text-xl font-extrabold text-purple-700">{{ $siswaLunas }} / {{ $totalSiswa }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $totalSiswa > 0 ? round($siswaLunas/$totalSiswa*100) : 0 }}% siswa lunas semua tagihan</p>
        </div>
    </div>

    {{-- Row 2: Donut status + Line trend bulanan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Donut: Status Tagihan --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Distribusi Status Tagihan</p>
            <p class="text-xs text-gray-400 mb-4">Proporsi tagihan berdasarkan status pembayaran</p>
            <div class="flex items-center gap-4">
                <div class="relative w-40 h-40 flex-shrink-0">
                    <canvas id="chartStatus"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-extrabold text-gray-700">{{ $pct }}%</span>
                        <span class="text-xs text-gray-400">terkumpul</span>
                    </div>
                </div>
                <div class="space-y-2 flex-1">
                    @php
                    $statusInfo = [
                        'lunas'       => ['label'=>'Lunas',       'color'=>'bg-green-500'],
                        'cicilan'     => ['label'=>'Cicilan',     'color'=>'bg-yellow-400'],
                        'belum_bayar' => ['label'=>'Belum Bayar', 'color'=>'bg-red-500'],
                    ];
                    @endphp
                    @foreach($statusInfo as $key => $info)
                    @php $val = $data['statusDist'][$key] ?? 0; @endphp
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $info['color'] }} flex-shrink-0"></span>
                            <span class="text-gray-600">{{ $info['label'] }}</span>
                        </div>
                        <span class="font-semibold text-gray-800">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Line: Trend Pemasukan Bulanan --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Trend Pemasukan Bulanan</p>
            <p class="text-xs text-gray-400 mb-4">Total pembayaran masuk per bulan — {{ $tahunIni }}</p>
            <canvas id="chartBulanan" height="140"></canvas>
        </div>
    </div>

    {{-- Row 3: Bar per kelas --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <p class="font-semibold text-gray-700 mb-1">Perbandingan Pembayaran per Kelas</p>
        <p class="text-xs text-gray-400 mb-4">Nominal terkumpul vs tunggakan per kelas</p>
        <canvas id="chartKelas" height="100"></canvas>
    </div>

    {{-- Row 4: Pie kategori + Top tunggakan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Pie: Per Kategori --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Tagihan per Kategori</p>
            <p class="text-xs text-gray-400 mb-4">Distribusi nominal tagihan berdasarkan jenis</p>
            <div class="flex items-center gap-4">
                <canvas id="chartKategori" class="w-36 h-36 flex-shrink-0" style="max-width:144px;max-height:144px"></canvas>
                <div class="space-y-1.5 flex-1 text-sm">
                    @php
                    $katColors = ['kas_kelas'=>'bg-blue-500','buku_lks'=>'bg-purple-500','kegiatan'=>'bg-orange-400','seragam'=>'bg-teal-500','lainnya'=>'bg-gray-400'];
                    @endphp
                    @foreach($data['perKategori'] ?? [] as $kat => $total)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $katColors[$kat] ?? 'bg-gray-300' }} flex-shrink-0"></span>
                            <span class="text-gray-600 text-xs">{{ $kategoriLabel[$kat] ?? $kat }}</span>
                        </div>
                        <span class="font-semibold text-gray-800 text-xs">Rp {{ number_format($total/1000000, 1) }}jt</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Tunggakan Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Siswa dengan Tunggakan Terbesar</p>
            <p class="text-xs text-gray-400 mb-4">Top 7 siswa berdasarkan total tunggakan aktif</p>
            @if(($data['topTunggakan'] ?? collect())->isEmpty())
            <div class="text-center text-gray-400 py-6 text-sm">
                <i class="fas fa-check-circle text-green-400 text-2xl mb-2"></i><br>Tidak ada tunggakan!
            </div>
            @else
            <div class="space-y-2">
                @foreach($data['topTunggakan'] as $i => $s)
                @php
                    $maxTunggakan = $data['topTunggakan']->first()->tunggakan ?? 1;
                    $barPct = $maxTunggakan > 0 ? round($s->tunggakan / $maxTunggakan * 100) : 0;
                    $rankColor = match($i) { 0=>'text-red-600 bg-red-50', 1=>'text-orange-500 bg-orange-50', 2=>'text-yellow-600 bg-yellow-50', default=>'text-gray-500 bg-gray-50' };
                @endphp
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 {{ $rankColor }}">{{ $i+1 }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-0.5">
                            <span class="text-gray-700 font-medium truncate text-xs">{{ $s->nama }}</span>
                            <span class="text-red-600 font-bold text-xs ml-2 flex-shrink-0">Rp {{ number_format($s->tunggakan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full">
                                <div class="h-1.5 bg-red-400 rounded-full" style="width:{{ $barPct }}%"></div>
                            </div>
                            <span class="text-gray-400 text-xs flex-shrink-0">Kelas {{ $s->kelas?->nama }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Row 5: Metode pembayaran + Siswa lunas per kelas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Metode Pembayaran --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Metode Pembayaran</p>
            <p class="text-xs text-gray-400 mb-4">Distribusi nominal berdasarkan metode</p>
            <canvas id="chartMetode" height="160"></canvas>
        </div>

        {{-- Siswa Lunas per Kelas --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-1">Kelulusan Pembayaran per Kelas</p>
            <p class="text-xs text-gray-400 mb-4">Jumlah siswa lunas semua tagihan vs ada tunggakan</p>
            <canvas id="chartLunasKelas" height="160"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const fmt = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

// ─── 1. Donut Status ────────────────────────────────────────────────
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: ['Lunas', 'Cicilan', 'Belum Bayar'],
        datasets: [{
            data: [
                {{ $data['statusDist']['lunas']       ?? 0 }},
                {{ $data['statusDist']['cicilan']     ?? 0 }},
                {{ $data['statusDist']['belum_bayar'] ?? 0 }},
            ],
            backgroundColor: ['#10B981','#FBBF24','#EF4444'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '72%',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} tagihan` } } },
    }
});

// ─── 2. Line Bulanan ────────────────────────────────────────────────
const bulananData = {!! json_encode(array_values($data['pemasukanBulanan'] ?? [])) !!};
new Chart(document.getElementById('chartBulanan'), {
    type: 'line',
    data: {
        labels: {!! json_encode($data['bulanLabels'] ?? []) !!},
        datasets: [{
            label: 'Pemasukan',
            data: bulananData,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3B82F6',
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => fmt(ctx.parsed.y) } } },
        scales: {
            y: { ticks: { callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt', font:{size:10} }, grid: { color:'#f3f4f6' } },
            x: { ticks: { font:{size:10} }, grid: { display:false } }
        }
    }
});

// ─── 3. Bar per Kelas ───────────────────────────────────────────────
@php
$kelasNama     = $data['perKelas']->pluck('nama')->toJson();
$kelasTerbayar = $data['perKelas']->pluck('terbayar')->toJson();
$kelasTunggak  = $data['perKelas']->pluck('tunggakan')->toJson();
@endphp
new Chart(document.getElementById('chartKelas'), {
    type: 'bar',
    data: {
        labels: {!! $kelasNama !!},
        datasets: [
            { label: 'Terkumpul', data: {!! $kelasTerbayar !!}, backgroundColor: '#10B981', borderRadius: 5 },
            { label: 'Tunggakan', data: {!! $kelasTunggak !!},  backgroundColor: '#FCA5A5', borderRadius: 5 },
        ]
    },
    options: {
        plugins: { legend: { position:'top', labels:{boxWidth:12,font:{size:11}} }, tooltip: { callbacks: { label: ctx => ctx.dataset.label+': '+fmt(ctx.parsed.y) } } },
        scales: {
            y: { stacked: false, ticks: { callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt', font:{size:10} }, grid:{ color:'#f3f4f6' } },
            x: { ticks: { font:{size:11} }, grid: { display:false } }
        }
    }
});

// ─── 4. Pie Kategori ────────────────────────────────────────────────
@php
$katKeys   = collect($data['perKategori'] ?? [])->keys();
$katVals   = collect($data['perKategori'] ?? [])->values();
$katLabels = $katKeys->map(fn($k) => $kategoriLabel[$k] ?? $k);
@endphp
new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
        labels: {!! $katLabels->toJson() !!},
        datasets: [{
            data: {!! $katVals->toJson() !!},
            backgroundColor: ['#3B82F6','#8B5CF6','#F97316','#14B8A6','#9CA3AF'],
            borderWidth: 0,
            hoverOffset: 5,
        }]
    },
    options: {
        cutout: '60%',
        plugins: { legend: { display:false }, tooltip: { callbacks: { label: ctx => fmt(ctx.parsed) } } },
    }
});

// ─── 5. Bar Metode ──────────────────────────────────────────────────
@php
$metodeKeys   = collect($data['metodeData'] ?? [])->keys();
$metodeVals   = collect($data['metodeData'] ?? [])->values();
$metodeLabels = $metodeKeys->map(fn($k) => match($k) { 'tunai'=>'Tunai', 'transfer'=>'Transfer Bank', 'qris'=>'QRIS', default=>$k });
@endphp
new Chart(document.getElementById('chartMetode'), {
    type: 'bar',
    data: {
        labels: {!! $metodeLabels->toJson() !!},
        datasets: [{
            label: 'Nominal',
            data: {!! $metodeVals->toJson() !!},
            backgroundColor: ['#3B82F6','#8B5CF6','#F97316'],
            borderRadius: 8,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: ctx => fmt(ctx.parsed.x) } } },
        scales: {
            x: { ticks:{ callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt', font:{size:10} }, grid:{color:'#f3f4f6'} },
            y: { ticks:{ font:{size:11} }, grid:{display:false} }
        }
    }
});

// ─── 6. Stacked Bar Lunas per Kelas ─────────────────────────────────
@php
$kelasLunas    = $data['perKelas']->pluck('lunas')->toJson();
$kelasTunggakC = $data['perKelas']->map(fn($r) => $r['siswa'] - $r['lunas'])->toJson();
@endphp
new Chart(document.getElementById('chartLunasKelas'), {
    type: 'bar',
    data: {
        labels: {!! $kelasNama !!},
        datasets: [
            { label: 'Lunas',        data: {!! $kelasLunas !!},    backgroundColor: '#10B981', borderRadius: 4 },
            { label: 'Ada Tunggakan',data: {!! $kelasTunggakC !!}, backgroundColor: '#FCA5A5', borderRadius: 4 },
        ]
    },
    options: {
        plugins: { legend:{ position:'top', labels:{boxWidth:12,font:{size:11}} } },
        scales: {
            x: { stacked:true, ticks:{font:{size:11}}, grid:{display:false} },
            y: { stacked:true, ticks:{stepSize:1, font:{size:10}}, grid:{color:'#f3f4f6'} }
        }
    }
});
</script>
@endpush
