@extends('layouts.app')
@section('title', 'Penugasan Wali Kelas')
@section('page-title', 'Penugasan Wali Kelas')

@section('content')
<div class="space-y-4">

    {{-- Tahun Ajaran --}}
    <div class="flex items-center justify-between">
        @include('layouts.partials.tahun-ajaran-select', [
            'allTahunAjaran' => $allTahunAjaran,
            'selectedTa'     => $tahunAjaran,
            'taRoute'        => 'admin.wali-kelas.index',
        ])
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(!$tahunAjaran)
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-calendar-times text-4xl mb-3 block"></i>
        <p>Belum ada tahun ajaran. Buat tahun ajaran terlebih dahulu.</p>
    </div>
    @elseif($kelasList->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-door-open text-4xl mb-3 block"></i>
        <p class="font-medium mb-1">Belum ada kelas di tahun ajaran ini</p>
        <p class="text-sm">Kelas akan muncul di sini setelah data siswa diimport.</p>
    </div>
    @else

    @if($waliUsers->isEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3 text-sm text-amber-800">
        <i class="fas fa-exclamation-circle text-amber-500 flex-shrink-0"></i>
        <span>Belum ada akun dengan role <strong>wali_kelas</strong>. Tambahkan user terlebih dahulu di <a href="{{ route('admin.users.index') }}" class="underline">Manajemen User</a>.</span>
    </div>
    @endif

    {{-- Ringkasan --}}
    @php
        $sudahDiisi  = $kelasList->whereNotNull('wali_kelas_id')->count();
        $belumDiisi  = $kelasList->whereNull('wali_kelas_id')->count();
    @endphp
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-blue-500">
            <p class="text-2xl font-bold text-gray-800">{{ $kelasList->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Kelas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-green-500">
            <p class="text-2xl font-bold text-green-600">{{ $sudahDiisi }}</p>
            <p class="text-xs text-gray-500 mt-1">Sudah Ada Wali</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-red-400">
            <p class="text-2xl font-bold text-red-500">{{ $belumDiisi }}</p>
            <p class="text-xs text-gray-500 mt-1">Belum Ada Wali</p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <p class="text-sm font-medium text-gray-700">
                Daftar Kelas — TA {{ $tahunAjaran->nama }}
            </p>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Kelas</th>
                    <th class="text-center px-4 py-3 text-xs text-gray-500 font-medium">Siswa</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Wali Kelas Saat Ini</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium w-80">Ganti Wali Kelas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($kelasList as $kelas)
                @php $punya = $kelas->waliKelas !== null; @endphp
                <tr class="hover:bg-gray-50 {{ !$punya ? 'bg-red-50/40' : '' }}">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-800">Kelas {{ $kelas->nama }}</p>
                        <p class="text-xs text-gray-400">Tingkat {{ $kelas->tingkat }}</p>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600 text-sm">
                        {{ $kelas->siswa_count }}
                    </td>
                    <td class="px-4 py-3">
                        @if($punya)
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($kelas->waliKelas->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-xs">{{ $kelas->waliKelas->name }}</p>
                                <p class="text-gray-400 text-xs">{{ $kelas->waliKelas->username }}</p>
                            </div>
                        </div>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded-full">
                            <i class="fas fa-exclamation-circle text-[10px]"></i> Belum ditugaskan
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST"
                              action="{{ route('admin.wali-kelas.update', $kelas) }}"
                              class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="wali_kelas_id"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">— Tidak ada wali —</option>
                                @foreach($waliUsers as $u)
                                <option value="{{ $u->id }}"
                                        {{ $kelas->wali_kelas_id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="flex-shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg whitespace-nowrap">
                                Simpan
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
