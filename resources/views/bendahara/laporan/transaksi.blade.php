@extends('layouts.app')
@section('title', 'Laporan Transaksi')
@section('page-title', 'Laporan Transaksi Pembayaran')

@php
$metodeLabel  = ['tunai'=>'Tunai','transfer'=>'Transfer Bank','qris'=>'QRIS'];
$metodeColor  = ['tunai'=>'bg-green-100 text-green-700','transfer'=>'bg-blue-100 text-blue-700','qris'=>'bg-purple-100 text-purple-700'];
$metodeIcon   = ['tunai'=>'fa-money-bill-wave','transfer'=>'fa-university','qris'=>'fa-qrcode'];
$statusLabel  = ['approved'=>'Disetujui','pending'=>'Menunggu','rejected'=>'Ditolak'];
$statusColor  = ['approved'=>'bg-green-100 text-green-700','pending'=>'bg-yellow-100 text-yellow-700','rejected'=>'bg-red-100 text-red-700'];
@endphp

@section('content')
<div class="space-y-4">

    {{-- Summary mini cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-400">Total Terkumpul</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($summary['approved'],0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-400">Total Transaksi</p>
            <p class="text-lg font-bold text-blue-700">{{ $summary['count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
            <p class="text-xs text-gray-400">Pending Verifikasi</p>
            <p class="text-lg font-bold text-yellow-600">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-300">
            <p class="text-xs text-gray-400">Semua Nominal</p>
            <p class="text-lg font-bold text-gray-700">Rp {{ number_format($summary['total'],0,',','.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('bendahara.laporan.transaksi') }}" id="form-transaksi" class="space-y-3">
            {{-- Row 1: Tahun Ajaran --}}
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <label class="text-xs text-gray-500 whitespace-nowrap font-medium">Tahun Ajaran:</label>
                <select name="ta" id="ta-transaksi"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach($allTahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ $tahunAjaran?->id == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }}{{ $ta->is_aktif ? ' ★' : '' }}
                    </option>
                    @endforeach
                </select>
                <span class="text-xs text-gray-400">— ganti tahun ajaran untuk melihat data tahun lain</span>
            </div>
            {{-- Row 2: Filter detail --}}
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <input type="text" name="cari" value="{{ request('cari') }}"
                           placeholder="Cari nama / NIS siswa..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <select name="kelas_id" id="kelas-transaksi" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id')==$k->id?'selected':'' }}>Kelas {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="metode" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Metode</option>
                        @foreach($metodeLabel as $k=>$v)
                        <option value="{{ $k }}" {{ request('metode')==$k?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status_verifikasi" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Status</option>
                        @foreach($statusLabel as $k=>$v)
                        <option value="{{ $k }}" {{ request('status_verifikasi')==$k?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2 rounded-lg">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                    @if(request()->anyFilled(['cari','kelas_id','metode','status_verifikasi','dari','sampai']))
                    <a href="{{ route('bendahara.laporan.transaksi', ['ta' => $tahunAjaran?->id]) }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-2 rounded-lg flex items-center">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
                <div>
                    <input type="date" name="dari" value="{{ request('dari') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <input type="date" name="sampai" value="{{ request('sampai') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-700">
                {{ $transaksi->total() }} transaksi ditemukan
            </p>
            <p class="text-xs text-gray-400">Halaman {{ $transaksi->currentPage() }} / {{ $transaksi->lastPage() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Tgl Bayar</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Siswa</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Kelas</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Jenis Tagihan</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Metode</th>
                        <th class="text-right px-4 py-3 text-xs text-gray-500 font-medium">Nominal</th>
                        <th class="text-center px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
                        <th class="text-center px-4 py-3 text-xs text-gray-500 font-medium">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transaksi as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $t->tagihanSiswa?->siswa?->nama ?? '—' }}</p>
                            <p class="text-gray-400 text-xs">NIS {{ $t->tagihanSiswa?->siswa?->nis }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $t->tagihanSiswa?->siswa?->kelas?->nama ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs max-w-[150px] truncate">{{ $t->tagihanSiswa?->jenisTagihan?->nama ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $metodeColor[$t->metode] ?? 'bg-gray-100 text-gray-600' }}">
                                <i class="fas {{ $metodeIcon[$t->metode] ?? 'fa-money-bill' }} mr-1"></i>
                                {{ $metodeLabel[$t->metode] ?? $t->metode }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800 text-xs whitespace-nowrap">
                            Rp {{ number_format($t->nominal,0,',','.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor[$t->status_verifikasi] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabel[$t->status_verifikasi] ?? $t->status_verifikasi }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($t->bukti_bayar_path)
                            <a href="{{ route('bukti.show', $t->id) }}" target="_blank"
                               class="text-blue-500 hover:text-blue-700 text-xs flex items-center justify-center gap-1">
                                <i class="fas fa-image"></i> Lihat
                            </a>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">
                            <i class="fas fa-search text-2xl mb-2 block"></i>
                            Tidak ada transaksi yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t flex items-center justify-between">
            <p class="text-xs text-gray-400">Menampilkan {{ $transaksi->firstItem() }}–{{ $transaksi->lastItem() }} dari {{ $transaksi->total() }}</p>
            {{ $transaksi->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var taSelect = document.getElementById('ta-transaksi');
    var kelasSelect = document.getElementById('kelas-transaksi');
    if (taSelect) {
        taSelect.addEventListener('change', function () {
            if (kelasSelect) kelasSelect.value = '';
            this.closest('form').submit();
        });
    }
})();
</script>
@endpush
