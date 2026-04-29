@extends('layouts.app')
@section('title', 'Tahun Ajaran')
@section('page-title', 'Tahun Ajaran')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">Total: {{ $tahunAjaranList->count() }} tahun ajaran</p>
        <a href="{{ route('admin.tahun-ajaran.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Tahun Ajaran
        </a>
    </div>

    {{-- Info aktif --}}
    @php $aktif = $tahunAjaranList->firstWhere('is_aktif', true); @endphp
    @if($aktif)
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <i class="fas fa-calendar-check text-blue-600"></i>
        <div>
            <p class="text-sm font-semibold text-blue-800">Tahun Ajaran Aktif: {{ $aktif->nama }}</p>
            <p class="text-xs text-blue-600">
                {{ $aktif->tanggal_mulai->format('d M Y') }} — {{ $aktif->tanggal_selesai->format('d M Y') }}
                &nbsp;·&nbsp; Semua tagihan dan transaksi baru menggunakan tahun ajaran ini.
            </p>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
        <p class="text-sm text-yellow-700">Belum ada tahun ajaran yang aktif. Aktifkan salah satu agar sistem dapat berjalan.</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Tanggal Mulai</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Tanggal Selesai</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Jumlah Kelas</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tahunAjaranList as $ta)
                <tr class="hover:bg-gray-50 {{ $ta->is_aktif ? 'bg-blue-50/40' : '' }}">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $ta->nama }}
                        @if($ta->is_aktif)
                            <span class="ml-1 text-xs text-blue-600"><i class="fas fa-star"></i></span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $ta->tanggal_mulai->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $ta->tanggal_selesai->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $ta->kelas->count() }} kelas</td>
                    <td class="px-4 py-3">
                        @if($ta->is_aktif)
                            <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium ring-1 ring-blue-200">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-full">
                                Tidak Aktif
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if(!$ta->is_aktif)
                            <form method="POST" action="{{ route('admin.tahun-ajaran.aktifkan', $ta) }}"
                                  onsubmit="return confirm('Aktifkan tahun ajaran {{ $ta->nama }}? Tahun ajaran yang sedang aktif akan dinonaktifkan.')">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Aktifkan</button>
                            </form>
                            @endif
                            <a href="{{ route('admin.tahun-ajaran.edit', $ta) }}"
                               class="text-xs text-yellow-600 hover:underline">Edit</a>
                            @if(!$ta->is_aktif)
                            <form method="POST" action="{{ route('admin.tahun-ajaran.destroy', $ta) }}"
                                  onsubmit="return confirm('Hapus tahun ajaran {{ $ta->nama }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">
                        Belum ada tahun ajaran. <a href="{{ route('admin.tahun-ajaran.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400">
        <i class="fas fa-info-circle mr-1"></i>
        Tahun ajaran aktif digunakan sebagai konteks default untuk seluruh kelas, tagihan, dan transaksi keuangan siswa.
        Hanya satu tahun ajaran yang dapat aktif dalam satu waktu.
    </p>
</div>
@endsection
