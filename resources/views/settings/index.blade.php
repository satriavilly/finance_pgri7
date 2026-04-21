@extends('layouts.app')
@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Foto Profil --}}
    <div class="bg-white rounded-xl shadow-sm p-6"
         x-data="{
            preview: '{{ $user->foto_profil ? $user->fotoProfilUrl() : '' }}',
            fileName: '',
            onFile(e) {
                const f = e.target.files[0];
                if (!f) return;
                this.fileName = f.name;
                const reader = new FileReader();
                reader.onload = ev => this.preview = ev.target.result;
                reader.readAsDataURL(f);
            }
         }">
        <h2 class="text-base font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fas fa-camera text-blue-500"></i> Foto Profil
        </h2>

        <div class="flex items-center gap-6">
            {{-- Avatar preview --}}
            <div class="flex-shrink-0">
                <div class="w-24 h-24 rounded-full overflow-hidden bg-blue-100 flex items-center justify-center ring-4 ring-blue-100">
                    <img x-show="preview" :src="preview" class="w-full h-full object-cover" x-cloak>
                    <span x-show="!preview" class="text-blue-700 font-bold text-2xl">{{ $user->inisial() }}</span>
                </div>
            </div>

            <div class="flex-1">
                <form method="POST" action="{{ route('settings.foto') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors"
                           :class="fileName ? 'border-blue-400 bg-blue-50' : ''">
                        <div x-show="!fileName" class="flex flex-col items-center gap-1 text-gray-400 text-xs">
                            <i class="fas fa-cloud-upload-alt text-xl"></i>
                            <span>Klik untuk pilih foto</span>
                            <span class="text-gray-300">JPG / PNG / WebP · maks 2 MB</span>
                        </div>
                        <div x-show="fileName" class="flex flex-col items-center gap-1 text-blue-600 text-xs">
                            <i class="fas fa-check-circle text-xl"></i>
                            <span x-text="fileName" class="font-medium max-w-[180px] truncate"></span>
                        </div>
                        <input type="file" name="foto_profil" accept=".jpg,.jpeg,.png,.webp"
                               class="hidden" @change="onFile($event)">
                    </label>
                    @error('foto_profil')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror

                    <div class="flex gap-2">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fas fa-upload"></i> Simpan Foto
                        </button>
                        @if($user->foto_profil)
                        <form method="POST" action="{{ route('settings.foto.hapus') }}">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5"
                                    onclick="return confirm('Hapus foto profil?')">
                                <i class="fas fa-trash"></i> Hapus Foto
                            </button>
                        </form>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Akun --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-user text-blue-500"></i> Informasi Akun
        </h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Nama Lengkap</p>
                <p class="font-medium text-gray-800">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Username</p>
                <p class="font-medium text-gray-800 font-mono">{{ $user->username }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Email</p>
                <p class="font-medium text-gray-800">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Role</p>
                <p class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $user->getRoleNames()->first() ?? '—')) }}</p>
            </div>
            @if($user->no_hp)
            <div>
                <p class="text-gray-400 text-xs mb-0.5">No. HP</p>
                <p class="font-medium text-gray-800">{{ $user->no_hp }}</p>
            </div>
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-4">Untuk mengubah data akun, hubungi administrator.</p>
    </div>

    {{-- Ubah Password --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-lock text-blue-500"></i> Ubah Password
        </h2>
        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4"
              x-data="{ showLama: false, showBaru: false, showKonfirm: false }">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input :type="showLama ? 'text' : 'password'" name="password_lama" required
                           class="w-full border border-gray-300 rounded-lg px-4 pr-11 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <button type="button" @click="showLama=!showLama"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas text-sm" :class="showLama ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                @error('password_lama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input :type="showBaru ? 'text' : 'password'" name="password_baru" required
                           class="w-full border border-gray-300 rounded-lg px-4 pr-11 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <button type="button" @click="showBaru=!showBaru"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas text-sm" :class="showBaru ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                @error('password_baru')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input :type="showKonfirm ? 'text' : 'password'" name="password_baru_confirmation" required
                           class="w-full border border-gray-300 rounded-lg px-4 pr-11 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <button type="button" @click="showKonfirm=!showKonfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas text-sm" :class="showKonfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg flex items-center gap-1.5">
                <i class="fas fa-key"></i> Perbarui Password
            </button>
        </form>
    </div>

</div>
@endsection
