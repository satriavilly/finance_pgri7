@extends('layouts.app')
@section('title', 'Input Pembayaran')
@section('page-title', 'Input Pembayaran')

@section('content')
<div class="max-w-xl mx-auto space-y-4"
     x-data="{
        metode: 'tunai',
        sisaTagihan: {{ (int)$tagihan->sisa_tagihan }},
        display: '{{ old('nominal') ? number_format(old('nominal'), 0, ',', '.') : number_format($tagihan->sisa_tagihan, 0, ',', '.') }}',
        raw: '{{ old('nominal') ?? (int)$tagihan->sisa_tagihan }}',
        overLimit: false,
        today: '{{ now()->format('Y-m-d') }}',
        tanggal: '{{ old('tanggal_bayar', now()->format('Y-m-d')) }}',
        fileName: '',
        format(val) {
            let num = val.replace(/\D/g, '');
            this.raw = num;
            this.display = num ? parseInt(num).toLocaleString('id-ID') : '';
            this.overLimit = num !== '' && parseInt(num) > this.sisaTagihan;
        },
        setToday() { this.tanggal = this.today; },
        setSisa() {
            this.raw = '{{ (int)$tagihan->sisa_tagihan }}';
            this.display = '{{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}';
            this.overLimit = false;
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
                <p class="text-xs text-gray-400 mt-0.5">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    TA {{ $tagihan->jenisTagihan->kelas?->tahunAjaran?->nama ?? '—' }}
                </p>
                <div class="mt-1">
                    @if($tagihan->jenisTagihan->is_cicilan)
                    <span class="inline-flex items-center gap-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                        <i class="fas fa-layer-group text-[9px]"></i>
                        Cicilan bebas
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">
                        <i class="fas fa-bolt text-[9px]"></i>
                        Lunas sekaligus
                    </span>
                    @endif
                </div>
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
                        <label class="text-sm font-medium text-gray-700">
                            Nominal Dibayar <span class="text-red-500">*</span>
                            <span class="ml-1.5 text-xs font-normal text-green-600 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded-full">
                                <i class="fas fa-magic text-[9px] mr-0.5"></i>auto terisi
                            </span>
                        </label>
                        <button type="button" @click="setSisa()"
                                class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <i class="fas fa-rotate-left text-[10px]"></i>
                            Reset ke sisa (Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }})
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium"
                              :class="overLimit ? 'text-red-500' : 'text-gray-500'">Rp</span>
                        <input type="text" inputmode="numeric"
                               x-model="display"
                               @input="format($event.target.value)"
                               required
                               :class="overLimit
                                   ? 'border-red-400 bg-red-50 focus:ring-red-400'
                                   : 'border-gray-300 focus:ring-blue-500'"
                               class="w-full border rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:border-transparent outline-none"
                               placeholder="Contoh: 50.000">
                    </div>
                    <div x-show="overLimit" x-cloak
                         class="flex items-center gap-1.5 mt-1.5 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5">
                        <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                        <span>Nominal melebihi sisa tagihan
                            (maks Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}).
                            <button type="button" @click="setSisa()" class="underline font-medium ml-1">Reset ke sisa</button>
                        </span>
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
                              placeholder="Catatan pembayaran">{{ old('catatan', 'Pembayaran ' . $tagihan->jenisTagihan->nama) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit"
                        :disabled="overLimit"
                        :class="overLimit
                            ? 'bg-gray-300 cursor-not-allowed text-gray-500'
                            : (metode==='tunai' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white')"
                        class="font-medium px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas" :class="overLimit ? 'fa-ban' : (metode==='tunai' ? 'fa-check' : 'fa-paper-plane')"></i>
                    <span x-text="overLimit ? 'Nominal terlalu besar' : (metode==='tunai' ? 'Catat Pembayaran' : 'Kirim Bukti Bayar')"></span>
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
