@extends('layouts.app')
@section('title', 'Input Pembayaran')
@section('page-title', 'Input Pembayaran')

@section('content')
<div class="max-w-xl mx-auto space-y-4"
     x-data="{
        metode: 'tunai',
        display: '{{ old('nominal') ? number_format(old('nominal'), 0, ',', '.') : '' }}',
        raw: '{{ old('nominal') ?? '' }}',
        today: '{{ now()->format('Y-m-d') }}',
        tanggal: '{{ old('tanggal_bayar', now()->format('Y-m-d')) }}',
        fileName: '',
        format(val) {
            let num = val.replace(/\D/g, '');
            this.raw = num;
            this.display = num ? parseInt(num).toLocaleString('id-ID') : '';
        },
        setToday() { this.tanggal = this.today; },
        setSisa() {
            this.raw = '{{ (int)$tagihan->sisa_tagihan }}';
            this.display = '{{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}';
        }
     }">

    {{-- Info Tagihan --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-blue-700 font-bold text-sm">{{ strtoupper(substr($tagihan->siswa->nama, 0, 1)) }}</span>
            </div>
            <div>
                <p class="font-semibold text-gray-800">{{ $tagihan->siswa->nama }}</p>
                <p class="text-xs text-gray-500">{{ $tagihan->jenisTagihan->nama }}</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 text-sm bg-gray-50 rounded-lg p-3">
            <div class="text-center">
                <p class="text-gray-500 text-xs mb-0.5">Total</p>
                <p class="font-semibold text-gray-700">Rp {{ number_format($tagihan->nominal_total, 0, ',', '.') }}</p>
            </div>
            <div class="text-center border-x border-gray-200">
                <p class="text-gray-500 text-xs mb-0.5">Terbayar</p>
                <p class="font-semibold text-green-600">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</p>
            </div>
            <div class="text-center">
                <p class="text-gray-500 text-xs mb-0.5">Sisa</p>
                <p class="font-semibold text-red-600">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Metode Toggle --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</p>
        <div class="grid grid-cols-3 gap-2">
            <button type="button" @click="metode='tunai'"
                    :class="metode==='tunai' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400'"
                    class="flex flex-col items-center gap-1.5 px-3 py-3 border-2 rounded-xl text-xs font-medium transition-all">
                <i class="fas fa-money-bill-wave text-base"></i>
                Tunai
            </button>
            <button type="button" @click="metode='transfer'"
                    :class="metode==='transfer' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400'"
                    class="flex flex-col items-center gap-1.5 px-3 py-3 border-2 rounded-xl text-xs font-medium transition-all">
                <i class="fas fa-university text-base"></i>
                Transfer Bank
            </button>
            <button type="button" @click="metode='qris'"
                    :class="metode==='qris' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400'"
                    class="flex flex-col items-center gap-1.5 px-3 py-3 border-2 rounded-xl text-xs font-medium transition-all">
                <i class="fas fa-qrcode text-base"></i>
                QRIS
            </button>
        </div>

        {{-- Info badge --}}
        <div class="mt-3 flex items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
            <i class="fas fa-check-circle"></i>
            <span x-text="metode==='tunai' ? 'Langsung tercatat sebagai lunas' : 'Langsung tercatat — bukti tersimpan sebagai lampiran'"></span>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <form method="POST"
              :action="metode==='tunai'
                  ? '{{ route('wali-kelas.pembayaran.bayar-tunai', $tagihan->id) }}'
                  : '{{ route('wali-kelas.pembayaran.upload-bukti', $tagihan->id) }}'"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="metode" :value="metode">

            <div class="space-y-4">

                {{-- Cicilan --}}
                @if($detail['cicilan']->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Cicilan</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-blue-400 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="cicilan_id" value="" checked class="text-blue-600">
                            <span class="text-sm text-gray-700">Pelunasan / di luar cicilan</span>
                        </label>
                        @foreach($detail['cicilan'] as $cicilan)
                        @if($cicilan->status === 'belum_bayar')
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-blue-400 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="cicilan_id" value="{{ $cicilan->id }}" class="text-blue-600">
                            <div class="flex-1 flex justify-between items-center">
                                <p class="text-sm font-medium">Cicilan ke-{{ $cicilan->ke }}</p>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-700">Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</p>
                                    @if($cicilan->due_date)
                                    <p class="text-xs text-gray-400">Jatuh tempo {{ $cicilan->due_date->format('d M Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Tanggal Bayar --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="date" name="tanggal_bayar" x-model="tanggal" required :max="today"
                               class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <button type="button" @click="setToday()"
                                class="px-3 py-2 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg whitespace-nowrap">
                            Hari ini
                        </button>
                    </div>
                    @error('tanggal_bayar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Nominal --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-sm font-medium text-gray-700">Nominal Dibayar <span class="text-red-500">*</span></label>
                        <button type="button" @click="setSisa()"
                                class="text-xs text-blue-600 hover:underline">
                            Isi sisa tagihan (Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }})
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                        <input type="text" inputmode="numeric"
                               x-model="display"
                               @input="format($event.target.value)"
                               required
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                               placeholder="Contoh: 50.000">
                    </div>
                    <input type="hidden" name="nominal" :value="raw">
                    @error('nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Upload Bukti Bayar (non-tunai) --}}
                <div x-show="metode!=='tunai'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bukti Bayar <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal text-xs ml-1">(JPG / PNG / PDF, maks 2 MB)</span>
                    </label>
                    <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors"
                           :class="fileName ? 'border-blue-400 bg-blue-50' : ''">
                        <div x-show="!fileName" class="flex flex-col items-center gap-1 text-gray-400">
                            <i class="fas fa-cloud-upload-alt text-2xl"></i>
                            <span class="text-xs">Klik untuk unggah atau drag & drop</span>
                        </div>
                        <div x-show="fileName" class="flex flex-col items-center gap-1 text-blue-600">
                            <i class="fas fa-file-check text-2xl"></i>
                            <span class="text-xs font-medium" x-text="fileName"></span>
                        </div>
                        <input type="file" name="bukti_bayar" class="hidden"
                               accept=".jpg,.jpeg,.png,.pdf"
                               @change="fileName = $event.target.files[0]?.name ?? ''">
                    </label>
                    @error('bukti_bayar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea name="catatan" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                              placeholder="Catatan pembayaran">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit"
                        :class="metode==='tunai' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                        class="text-white font-medium px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas" :class="metode==='tunai' ? 'fa-check' : 'fa-paper-plane'"></i>
                    <span x-text="metode==='tunai' ? 'Catat Pembayaran' : 'Kirim Bukti Bayar'"></span>
                </button>
                <a href="{{ route('wali-kelas.siswa.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
