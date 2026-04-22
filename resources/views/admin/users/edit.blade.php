@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User — ' . $user->name)

@php
$roleOptions  = $roles->map(fn($r) => ['value' => $r->name, 'label' => ucwords(str_replace('_', ' ', $r->name))])->values();
$siswaOptions = $siswaList->map(fn($s) => [
    'value' => $s->id,
    'label' => $s->nama . ' — NIS ' . $s->nis . ($s->kelas ? ' (Kelas '.$s->kelas->nama.')' : ''),
])->values();
$kelasOptions = $kelasList->map(fn($k) => [
    'value' => $k->id,
    'label' => 'Kelas ' . $k->nama . ' — TA ' . $k->tahunAjaran->nama,
])->values();

$currentRole      = old('role', $user->getRoleNames()->first() ?? '');
$currentRoleLabel = $currentRole ? ucwords(str_replace('_', ' ', $currentRole)) : 'Pilih role...';

$kelasUser     = $user->hasRole('wali_kelas')
    ? \App\Models\Kelas::where('wali_kelas_id', $user->id)->first()?->id
    : ($user->siswa?->kelas_id ?? null);
$oldKelasId    = old('kelas_id', $kelasUser ?? '');
$oldKelasLabel = $oldKelasId ? ($kelasList->find($oldKelasId) ? 'Kelas '.$kelasList->find($oldKelasId)->nama : '') : 'Pilih kelas...';

$anakSiswaId  = $user->anak?->id ?? null;
$oldAnakId    = old('anak_siswa_id', $anakSiswaId ?? '');
$oldAnakLabel = $oldAnakId ? ($siswaList->find($oldAnakId)?->nama ?? '— Pilih siswa —') : '— Pilih siswa —';
@endphp

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}"
              x-data="{ role: '{{ $currentRole }}' }"
              x-on:role-changed.window="role = $event.detail">
            @csrf
            @method('PUT')
            <div class="space-y-4">

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                {{-- Username + No HP --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                {{-- Password + show/hide --}}
                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="button" @click="show=!show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Role (searchable) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ $currentRole }}',
                            selectedLabel: '{{ addslashes($currentRoleLabel) }}',
                            options: {{ Js::from($roleOptions) }},
                            get filtered() { return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase())) },
                            choose(o) { this.selected = o.value; this.selectedLabel = o.label; this.open = false; this.search = ''; $dispatch('role-changed', o.value); }
                         }" class="relative">
                        <input type="hidden" name="role" x-effect="$el.value = selected" required>
                        <button type="button" @click="open=!open"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-left flex items-center justify-between focus:ring-2 focus:ring-blue-500 outline-none bg-white"
                                :class="selected ? 'text-gray-800' : 'text-gray-400'">
                            <span x-text="selectedLabel"></span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open=false"
                             class="absolute z-30 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1">
                            <div class="p-2 border-b">
                                <input type="text" x-model="search" placeholder="Cari role..."
                                       class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="max-h-44 overflow-y-auto">
                                <template x-for="o in filtered" :key="o.value">
                                    <div @click="choose(o)"
                                         class="px-4 py-2.5 text-sm cursor-pointer hover:bg-blue-50 flex items-center justify-between"
                                         :class="o.value === selected ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'">
                                        <span x-text="o.label"></span>
                                        <i x-show="o.value === selected" class="fas fa-check text-blue-600 text-xs"></i>
                                    </div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">Tidak ditemukan</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NIS (siswa role) --}}
                <div x-show="role === 'siswa'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS
                        @if(!$user->siswa)<span class="text-red-500">*</span>@endif
                    </label>
                    @if($user->siswa)
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-600">
                        {{ $user->siswa->nis }} <span class="text-gray-400 text-xs ml-2">(tidak bisa diubah)</span>
                    </div>
                    <input type="hidden" name="nis" value="{{ $user->siswa->nis }}">
                    @else
                    <input type="text" name="nis" value="{{ old('nis') }}"
                           placeholder="Nomor Induk Siswa"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('nis')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @endif
                </div>

                {{-- Anak siswa (ortu) — searchable --}}
                <div x-show="role === 'ortu'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Siswa (Anak) <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ $oldAnakId }}',
                            selectedLabel: '{{ addslashes($oldAnakLabel) }}',
                            options: {{ Js::from($siswaOptions) }},
                            get filtered() { return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase())) },
                            choose(o) { this.selected = o.value; this.selectedLabel = o.label; this.open = false; this.search = ''; }
                         }" class="relative">
                        <input type="hidden" name="anak_siswa_id" x-effect="$el.value = selected">
                        <button type="button" @click="open=!open"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-left flex items-center justify-between focus:ring-2 focus:ring-blue-500 outline-none bg-white"
                                :class="selected ? 'text-gray-800' : 'text-gray-400'">
                            <span x-text="selectedLabel"></span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open=false"
                             class="absolute z-30 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1">
                            <div class="p-2 border-b">
                                <input type="text" x-model="search" placeholder="Cari nama / NIS..."
                                       class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div @click="selected=''; selectedLabel='— Pilih siswa —'; open=false; search=''"
                                     class="px-4 py-2.5 text-sm cursor-pointer hover:bg-gray-50 text-gray-400 italic">— Pilih siswa —</div>
                                <template x-for="o in filtered" :key="o.value">
                                    <div @click="choose(o)"
                                         class="px-4 py-2.5 text-sm cursor-pointer hover:bg-blue-50 flex items-center justify-between"
                                         :class="o.value == selected ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'">
                                        <span x-text="o.label"></span>
                                        <i x-show="o.value == selected" class="fas fa-check text-blue-600 text-xs"></i>
                                    </div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">Tidak ditemukan</div>
                            </div>
                        </div>
                    </div>
                    @if($anakSiswaId)
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fas fa-link mr-1"></i>Saat ini terhubung ke: <strong>{{ $user->anak->nama }}</strong>
                    </p>
                    @endif
                </div>

                {{-- Kelas (wali_kelas / siswa) — searchable --}}
                <div x-show="role === 'wali_kelas' || role === 'siswa'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <span x-text="role === 'wali_kelas' ? 'Tugaskan sebagai Wali Kelas' : 'Kelas Siswa'"></span>
                    </label>
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ $oldKelasId }}',
                            selectedLabel: '{{ addslashes($oldKelasLabel) }}',
                            options: {{ Js::from($kelasOptions) }},
                            get filtered() { return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase())) },
                            choose(o) { this.selected = o.value; this.selectedLabel = o.label; this.open = false; this.search = ''; }
                         }" class="relative">
                        <input type="hidden" name="kelas_id" x-effect="$el.value = selected">
                        <button type="button" @click="open=!open"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-left flex items-center justify-between focus:ring-2 focus:ring-blue-500 outline-none bg-white"
                                :class="selected ? 'text-gray-800' : 'text-gray-400'">
                            <span x-text="selectedLabel"></span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open=false"
                             class="absolute z-30 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1">
                            <div class="p-2 border-b">
                                <input type="text" x-model="search" placeholder="Cari kelas..."
                                       class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <template x-for="o in filtered" :key="o.value">
                                    <div @click="choose(o)"
                                         class="px-4 py-2.5 text-sm cursor-pointer hover:bg-blue-50 flex items-center justify-between"
                                         :class="o.value == selected ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'">
                                        <span x-text="o.label"></span>
                                        <i x-show="o.value == selected" class="fas fa-check text-blue-600 text-xs"></i>
                                    </div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">Tidak ditemukan</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Akun aktif --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ $user->is_active ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm text-gray-700">Akun aktif</label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    Perbarui
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
