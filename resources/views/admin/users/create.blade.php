@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')

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
$oldRole      = old('role', '');
$oldRoleLabel = $oldRole ? ucwords(str_replace('_', ' ', $oldRole)) : '';
@endphp

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.users.store') }}"
              x-data="{ role: '{{ $oldRole }}' }"
              x-on:role-changed.window="role = $event.detail">
            @csrf
            <div class="space-y-4">

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Username + No HP --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Password + show/hide --}}
                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="button" @click="show=!show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Role (searchable) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ $oldRole }}',
                            selectedLabel: '{{ $oldRoleLabel ?: 'Pilih role...' }}',
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
                                <input type="text" x-model="search" placeholder="Cari role..." autofocus
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
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- NIS (siswa role) --}}
                <div x-show="role === 'siswa'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" value="{{ old('nis') }}"
                           placeholder="Nomor Induk Siswa"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('nis')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Anak siswa (ortu) — searchable --}}
                <div x-show="role === 'ortu'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Siswa (Anak) <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    @php $oldAnak = old('anak_siswa_id', ''); $oldAnakLabel = $oldAnak ? ($siswaList->find($oldAnak)?->nama ?? '') : ''; @endphp
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ $oldAnak }}',
                            selectedLabel: '{{ addslashes($oldAnakLabel ?: '— Pilih siswa —') }}',
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
                </div>

                {{-- Kelas (wali_kelas / siswa) — searchable --}}
                <div x-show="role === 'wali_kelas' || role === 'siswa'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <span x-text="role === 'wali_kelas' ? 'Tugaskan sebagai Wali Kelas' : 'Kelas Siswa'"></span>
                    </label>
                    @php $oldKelas = old('kelas_id', ''); $oldKelasLabel = $oldKelas ? ($kelasList->find($oldKelas) ? 'Kelas '.$kelasList->find($oldKelas)->nama : '') : ''; @endphp
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ $oldKelas }}',
                            selectedLabel: '{{ addslashes($oldKelasLabel ?: 'Pilih kelas...') }}',
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
                    <p class="text-xs text-gray-500 mt-1" x-show="role === 'siswa'">Siswa akan otomatis didaftarkan ke kelas ini.</p>
                </div>

                {{-- Akun aktif --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', '1') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm text-gray-700">Akun aktif</label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                    Simpan
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
