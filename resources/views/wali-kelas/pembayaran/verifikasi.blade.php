@extends('layouts.app')
@section('title', 'Verifikasi Bukti Bayar')
@section('page-title', 'Verifikasi Bukti Bayar')

@section('content')
<div class="space-y-4">
    @if($pembayaran->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
        <p>Tidak ada bukti bayar yang menunggu verifikasi.</p>
    </div>
    @else
    @foreach($pembayaran as $item)
    <div class="bg-white rounded-xl shadow-sm p-5" x-data="{ showReject: false }">
        <div class="flex justify-between items-start mb-3">
            <div>
                <p class="font-semibold text-gray-800">{{ $item->tagihanSiswa->siswa->nama }}</p>
                <p class="text-sm text-gray-600">{{ $item->tagihanSiswa->jenisTagihan->nama }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    Rp {{ number_format($item->nominal, 0, ',', '.') }} — {{ \App\Models\Pembayaran::metodeLabel()[$item->metode] }}
                    | Dikirim {{ $item->created_at->diffForHumans() }}
                </p>
            </div>
            <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">Menunggu Verifikasi</span>
        </div>

        @if($item->bukti_bayar_path)
        <div class="mb-3">
            <a href="/bukti/{{ $item->id }}" target="_blank"
               class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat Bukti Bayar
            </a>
        </div>
        @endif

        <div class="flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('wali-kelas.pembayaran.approve', $item->id) }}">
                @csrf
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-1.5 rounded-lg">
                    Setujui
                </button>
            </form>

            <button @click="showReject = !showReject"
                    class="bg-red-50 hover:bg-red-100 text-red-700 text-sm px-4 py-1.5 rounded-lg border border-red-200">
                Tolak
            </button>
        </div>

        {{-- Form tolak --}}
        <div x-show="showReject" x-cloak class="mt-3">
            <form method="POST" action="{{ route('wali-kelas.pembayaran.reject', $item->id) }}" class="flex gap-2">
                @csrf
                <input type="text" name="catatan_tolak" required placeholder="Alasan penolakan (wajib diisi)"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-red-400 outline-none">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-1.5 rounded-lg">
                    Kirim
                </button>
            </form>
        </div>
    </div>
    @endforeach

    <div>{{ $pembayaran->links() }}</div>
    @endif
</div>
@endsection
