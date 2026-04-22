@extends('layouts.app')
@section('title', $namaSpp)
@section('page-title', $namaSpp)

@section('content')
<div class="space-y-4">

    {{-- Back + actions --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('bendahara.spp.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Kembali ke daftar SPP
        </a>
        <form method="POST" action="{{ route('bendahara.spp.distribusi-ulang', $periode) }}"
              x-data x-on:submit.prevent="if(confirm('Distribusikan SPP ini ke siswa baru yang belum memilikinya?')) $el.submit()">
            @csrf
            <button type="submit" class="text-sm bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                <i class="fas fa-sync-alt text-xs"></i> Distribusi ke Siswa Baru
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @php $pct = $summary['total_nominal'] > 0 ? min(100, round($summary['terkumpul'] / $summary['total_nominal'] * 100)) : 0; @endphp
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-400">Total Siswa</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-400">Sudah Bayar</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['lunas'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-400">
            <p class="text-xs text-gray-400">Belum Bayar</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['total'] - $summary['lunas'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-indigo-500">
            <p class="text-xs text-gray-400">Terkumpul</p>
            <p class="text-lg font-bold text-indigo-700">Rp {{ number_format($summary['terkumpul'], 0, ',', '.') }}</p>
            <div class="mt-1 h-1.5 bg-gray-100 rounded-full">
                <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Per kelas --}}
    @foreach($perKelas as $row)
    @php $kelas = $row['kelas']; $tagihan = $row['tagihan']; $nominalKelas = $row['nominal']; @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Kelas header --}}
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-gray-700">Kelas {{ $kelas->nama }}</span>
                <span class="text-xs text-gray-400">{{ $tagihan->count() }} siswa</span>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-green-600 font-medium">{{ $row['lunas'] }} lunas</span>
                <span class="text-red-500 font-medium">{{ $row['belum'] }} belum</span>
                <span class="text-gray-500">Rp {{ number_format($row['terkumpul'], 0, ',', '.') }}</span>
                <span class="text-xs text-blue-600 font-medium border border-blue-200 bg-blue-50 px-2 py-0.5 rounded-full">
                    Tarif Rp {{ number_format($nominalKelas, 0, ',', '.') }}
                </span>
            </div>
        </div>

        @if($tagihan->isEmpty())
        <p class="text-center text-sm text-gray-400 py-6">Belum ada siswa di kelas ini.</p>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($tagihan as $t)
            @php $lunas = $t->status === 'lunas'; @endphp
            <div x-data="{ open: false }" class="px-4 py-3">

                {{-- Row siswa --}}
                <div class="flex items-center gap-3">
                    {{-- Avatar --}}
                    @if($t->siswa->user?->foto_profil)
                    <img src="{{ $t->siswa->user->fotoProfilUrl() }}" alt="{{ $t->siswa->nama }}"
                         class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                    @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                                {{ $lunas ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ strtoupper(substr($t->siswa->nama, 0, 1)) }}
                    </div>
                    @endif

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $t->siswa->nama }}</p>
                        <p class="text-xs text-gray-400">NIS {{ $t->siswa->nis }}</p>
                    </div>

                    {{-- Status --}}
                    @if($lunas)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full flex items-center gap-1">
                        <i class="fas fa-check"></i> Lunas
                    </span>
                    @else
                    <span class="text-xs text-red-600 font-semibold whitespace-nowrap">
                        Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
                    </span>
                    @endif

                    {{-- Tombol bayar / riwayat --}}
                    <button type="button" @click="open = !open"
                            class="flex-shrink-0 text-xs {{ $lunas ? 'text-gray-400 hover:text-gray-600' : 'bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg' }}">
                        @if($lunas)
                        <i class="fas fa-history"></i>
                        @else
                        <i class="fas fa-cash-register mr-1"></i>Catat Bayar
                        @endif
                    </button>
                </div>

                {{-- Expand: form bayar + riwayat --}}
                <div x-show="open" x-cloak class="mt-3 space-y-3">

                    {{-- Form input pembayaran (hanya jika belum lunas) --}}
                    @if(!$lunas)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-xs font-semibold text-blue-700 mb-3">
                            <i class="fas fa-cash-register mr-1"></i>Catat Pembayaran SPP
                        </p>
                        <form method="POST" action="{{ route('bendahara.spp.bayar', $t->id) }}">
                            @csrf
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Nominal <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                        <input type="number" name="nominal" value="{{ $t->sisa_tagihan }}" required
                                               min="1000" max="{{ $t->sisa_tagihan }}"
                                               class="w-full border border-gray-300 rounded-lg pl-7 pr-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Metode <span class="text-red-500">*</span></label>
                                    <select name="metode" required
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal_bayar" value="{{ now()->toDateString() }}" required
                                           max="{{ now()->toDateString() }}"
                                           class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Catatan</label>
                                    <input type="text" name="catatan" placeholder="opsional"
                                           class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-1.5 rounded-lg">
                                    <i class="fas fa-save mr-1"></i>Simpan Pembayaran
                                </button>
                                <button type="button" @click="open = false"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs px-3 py-1.5 rounded-lg">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- Riwayat pembayaran --}}
                    @if($t->pembayaran->isNotEmpty())
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Riwayat Pembayaran</p>
                        <div class="space-y-1.5">
                            @foreach($t->pembayaran as $bayar)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-money-bill text-green-500"></i>
                                    <span class="font-medium text-gray-700">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</span>
                                    <span class="text-gray-400">· {{ \Carbon\Carbon::parse($bayar->created_at)->format('d M Y') }}</span>
                                    <span class="text-gray-400">· {{ \App\Models\Pembayaran::metodeLabel()[$bayar->metode] ?? $bayar->metode }}</span>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-xs
                                    {{ $bayar->status_verifikasi === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $bayar->status_verifikasi === 'approved' ? 'Diterima' : 'Pending' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @elseif($lunas === false)
                    <p class="text-xs text-gray-400 text-center py-1">Belum ada riwayat pembayaran.</p>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach

</div>
@endsection
