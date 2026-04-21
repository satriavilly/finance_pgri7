@extends('layouts.app')
@section('title', 'Rekap Tagihan')
@section('page-title', 'Rekap Tagihan Siswa')

@php
$statusLabel = ['lunas'=>'Lunas','cicilan'=>'Cicilan','belum_bayar'=>'Belum Bayar'];
$statusColor = ['lunas'=>'bg-green-100 text-green-700','cicilan'=>'bg-yellow-100 text-yellow-700','belum_bayar'=>'bg-red-100 text-red-700'];
$kategoriLabel = ['spp'=>'SPP','komite'=>'Komite','kegiatan'=>'Kegiatan','lainnya'=>'Lainnya'];
@endphp

@section('content')
<div class="space-y-4">

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-400">Total Tagihan</p>
            <p class="text-lg font-bold text-blue-700">Rp {{ number_format($summary['total'],0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-400">Terkumpul</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($summary['terbayar'],0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-400">Tunggakan</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($summary['tunggakan'],0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-400">
            <p class="text-xs text-gray-400">Tagihan Lunas</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['lunas'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-400">
            <p class="text-xs text-gray-400">Belum / Cicilan</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['belum'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('bendahara.laporan.tagihan') }}" class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <input type="text" name="cari" value="{{ request('cari') }}"
                       placeholder="Cari nama / NIS siswa..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <select name="kelas_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id')==$k->id?'selected':'' }}>Kelas {{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    @foreach($statusLabel as $k=>$v)
                    <option value="{{ $k }}" {{ request('status')==$k?'selected':'' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="kategori" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriLabel as $k=>$v)
                    <option value="{{ $k }}" {{ request('kategori')==$k?'selected':'' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2 rounded-lg">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
                @if(request()->anyFilled(['cari','kelas_id','status','kategori']))
                <a href="{{ route('bendahara.laporan.tagihan') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-2 rounded-lg flex items-center">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Tagihan --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-700">
                {{ $tagihan->total() }} tagihan ditemukan
            </p>
            <p class="text-xs text-gray-400">Halaman {{ $tagihan->currentPage() }} / {{ $tagihan->lastPage() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Siswa</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Kelas</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Jenis Tagihan</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Kategori</th>
                        <th class="text-right px-4 py-3 text-xs text-gray-500 font-medium">Total</th>
                        <th class="text-right px-4 py-3 text-xs text-gray-500 font-medium">Terbayar</th>
                        <th class="text-right px-4 py-3 text-xs text-gray-500 font-medium">Sisa</th>
                        <th class="text-center px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
                        <th class="text-center px-4 py-3 text-xs text-gray-500 font-medium">Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tagihan as $t)
                    @php
                        $sisa = $t->nominal_total - $t->nominal_terbayar;
                        $pct  = $t->nominal_total > 0 ? min(100, round($t->nominal_terbayar / $t->nominal_total * 100)) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $t->siswa?->nama ?? '—' }}</p>
                            <p class="text-gray-400 text-xs">NIS {{ $t->siswa?->nis }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">{{ $t->siswa?->kelas?->nama ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs max-w-[160px] truncate" title="{{ $t->jenisTagihan?->nama }}">{{ $t->jenisTagihan?->nama ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $kategoriLabel[$t->jenisTagihan?->kategori] ?? ($t->jenisTagihan?->kategori ?? '—') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 text-xs whitespace-nowrap font-medium">
                            Rp {{ number_format($t->nominal_total,0,',','.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-green-700 text-xs whitespace-nowrap font-semibold">
                            Rp {{ number_format($t->nominal_terbayar,0,',','.') }}
                            <div class="mt-1 h-1 bg-gray-100 rounded-full w-16 ml-auto">
                                <div class="h-1 bg-green-500 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-xs whitespace-nowrap {{ $sisa > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            @if($sisa > 0) Rp {{ number_format($sisa,0,',','.') }} @else — @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor[$t->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabel[$t->status] ?? $t->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $bayarCount = $t->pembayaran?->where('is_void', false)->count() ?? 0; @endphp
                            @if($bayarCount > 0)
                            <a href="{{ route('bendahara.laporan.transaksi') }}?cari={{ urlencode($t->siswa?->nis) }}"
                               class="text-blue-500 hover:text-blue-700 text-xs flex items-center justify-center gap-1">
                                <i class="fas fa-list-ul"></i> {{ $bayarCount }}×
                            </a>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-400">
                            <i class="fas fa-search text-2xl mb-2 block"></i>
                            Tidak ada tagihan yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t flex items-center justify-between">
            <p class="text-xs text-gray-400">Menampilkan {{ $tagihan->firstItem() }}–{{ $tagihan->lastItem() }} dari {{ $tagihan->total() }}</p>
            {{ $tagihan->links() }}
        </div>
    </div>
</div>
@endsection
