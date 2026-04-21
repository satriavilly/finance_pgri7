@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@php
$roleColor = [
    'admin'       => 'bg-red-100 text-red-700 ring-1 ring-red-200',
    'kepsek'      => 'bg-purple-100 text-purple-700 ring-1 ring-purple-200',
    'bendahara'   => 'bg-orange-100 text-orange-700 ring-1 ring-orange-200',
    'wali_kelas'  => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
    'siswa'       => 'bg-green-100 text-green-700 ring-1 ring-green-200',
    'ortu'        => 'bg-teal-100 text-teal-700 ring-1 ring-teal-200',
];
$roleLabel = [
    'admin'       => 'Admin',
    'kepsek'      => 'Kepala Sekolah',
    'bendahara'   => 'Bendahara',
    'wali_kelas'  => 'Wali Kelas',
    'siswa'       => 'Siswa',
    'ortu'        => 'Orang Tua',
];
@endphp

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">
            Total: {{ $users->total() }} pengguna
            @if($search)
                <span class="ml-1 text-blue-600">· hasil pencarian "<strong>{{ $search }}</strong>"</span>
            @endif
        </p>
        <a href="{{ route('admin.users.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="bg-white rounded-xl shadow-sm px-4 py-3">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama, username, atau email..."
                       class="w-full pl-9 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       autocomplete="off">
                @if($search)
                <a href="{{ route('admin.users.index') }}"
                   class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-sm"></i>
                </a>
                @else
                <button type="submit"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500">
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Username</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Email</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Role</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kelas</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                @php
                    $role = $user->getRoleNames()->first();
                    $kelas = null;
                    if ($role === 'wali_kelas') {
                        $kelas = $user->kelasWali->first()?->nama;
                    } elseif ($role === 'siswa') {
                        $kelas = $user->siswa?->kelas?->nama;
                    }
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $user->username }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @if($role)
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $roleColor[$role] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $roleLabel[$role] ?? $role }}
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $kelas ? 'Kelas ' . $kelas : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="{{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs px-2 py-0.5 rounded-full">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-xs text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.users.toggle-aktif', $user) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs {{ $user->is_active ? 'text-red-500' : 'text-green-600' }} hover:underline">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-2 text-xs">
        @foreach($roleLabel as $key => $label)
        <span class="px-2.5 py-1 rounded-full font-medium {{ $roleColor[$key] }}">{{ $label }}</span>
        @endforeach
    </div>
</div>
@endsection
