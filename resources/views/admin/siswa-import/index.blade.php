@extends('layouts.app')
@section('title', 'Import & Export Data Siswa')
@section('page-title', 'Import & Export Data Siswa')

@section('content')
<div class="space-y-6">

    {{-- Hasil Import --}}
    @if(session('import_result'))
    @php $result = session('import_result'); @endphp
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 {{ count($result['errors']) === 0 ? 'border-green-500' : 'border-yellow-400' }}">
        <p class="font-semibold text-gray-800 mb-3">
            <i class="fas fa-clipboard-check mr-2 {{ count($result['errors']) === 0 ? 'text-green-500' : 'text-yellow-500' }}"></i>
            Hasil Import
        </p>
        <div class="flex gap-6 mb-3">
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ $result['created'] }}</p>
                <p class="text-xs text-gray-500">Siswa baru ditambahkan</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $result['updated'] }}</p>
                <p class="text-xs text-gray-500">Data diperbarui (naik kelas)</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-red-500">{{ count($result['errors']) }}</p>
                <p class="text-xs text-gray-500">Baris gagal</p>
            </div>
        </div>
        @if(count($result['errors']) > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-1">
            <p class="text-xs font-semibold text-red-700 mb-1">Detail error:</p>
            @foreach($result['errors'] as $err)
            <p class="text-xs text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $err }}</p>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ===== EXPORT ===== --}}
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-download text-green-600"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Export Data Siswa</h2>
                    <p class="text-xs text-gray-500">Unduh data siswa ke file Excel per tahun ajaran</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.siswa-import.export') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tahun Ajaran <span class="text-red-500">*</span>
                        </label>
                        <select name="tahun_ajaran_id" required
                                class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="">— Pilih Tahun Ajaran —</option>
                            @foreach($tahunAjaranList as $ta)
                            <option value="{{ $ta->id }}">
                                {{ $ta->nama }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg flex items-center justify-center gap-2">
                        <i class="fas fa-download"></i>
                        Download Excel
                    </button>
                </div>
            </form>

            <div class="border-t pt-4">
                <p class="text-xs text-gray-500 font-medium mb-2">Kolom yang diekspor:</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['No', 'NIS', 'Nama', 'Kelamin', 'Kelas', 'Tingkat', 'Tahun Ajaran', 'Alamat'] as $col)
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded font-mono">{{ $col }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== IMPORT ===== --}}
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-upload text-blue-600"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Import Data Siswa</h2>
                    <p class="text-xs text-gray-500">Tambah atau perbarui data siswa dari file Excel</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.siswa-import.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tahun Ajaran Tujuan <span class="text-red-500">*</span>
                        </label>
                        <select name="tahun_ajaran_id" required
                                class="w-full px-3 py-2.5 text-sm border {{ $errors->has('tahun_ajaran_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="">— Pilih Tahun Ajaran —</option>
                            @foreach($tahunAjaranList as $ta)
                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('tahun_ajaran_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">Siswa akan ditempatkan di kelas yang ada pada tahun ajaran ini.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            File Excel <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file" accept=".xlsx,.xls"
                               class="w-full text-sm text-gray-600 border {{ $errors->has('file') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0
                                      file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100 cursor-pointer">
                        @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">Format: .xlsx atau .xls · Maks. 5 MB</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg flex items-center justify-center gap-2">
                            <i class="fas fa-upload"></i>
                            Proses Import
                        </button>
                        <a href="{{ route('admin.siswa-import.template') }}"
                           class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-2.5 rounded-lg whitespace-nowrap">
                            <i class="fas fa-file-excel text-green-600"></i>
                            Template
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== ATURAN IMPORT ===== --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>
            Aturan & Panduan Import
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Format Kolom --}}
            <div>
                <p class="text-sm font-medium text-gray-700 mb-3">Format Kolom Excel</p>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="w-full text-xs">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-3 py-2 text-left">Kolom</th>
                                <th class="px-3 py-2 text-left">Wajib</th>
                                <th class="px-3 py-2 text-left">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="bg-gray-50">
                                <td class="px-3 py-2 font-mono font-bold">NIS</td>
                                <td class="px-3 py-2"><span class="text-red-500 font-bold">Ya</span></td>
                                <td class="px-3 py-2 text-gray-600">Nomor Induk Siswa, unik per siswa</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono font-bold">Nama</td>
                                <td class="px-3 py-2"><span class="text-red-500 font-bold">Ya</span></td>
                                <td class="px-3 py-2 text-gray-600">Nama lengkap siswa</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-3 py-2 font-mono font-bold">Kelamin</td>
                                <td class="px-3 py-2"><span class="text-red-500 font-bold">Ya</span></td>
                                <td class="px-3 py-2 text-gray-600">Isi <strong>L</strong> (Laki-laki) atau <strong>P</strong> (Perempuan)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono font-bold">Kelas</td>
                                <td class="px-3 py-2"><span class="text-red-500 font-bold">Ya</span></td>
                                <td class="px-3 py-2 text-gray-600">Nama kelas harus sudah dibuat, mis. <strong>7A</strong>, <strong>8B</strong></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-3 py-2 font-mono font-bold">Alamat</td>
                                <td class="px-3 py-2 text-gray-400">Tidak</td>
                                <td class="px-3 py-2 text-gray-600">Boleh dikosongkan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Aturan --}}
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Perilaku Import</p>
                    <ul class="space-y-2">
                        <li class="flex gap-2 text-xs text-gray-600">
                            <i class="fas fa-plus-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                            <span><strong>Siswa baru</strong> (NIS belum ada) — akun login dibuat otomatis. Username = NIS, password awal = NIS.</span>
                        </li>
                        <li class="flex gap-2 text-xs text-gray-600">
                            <i class="fas fa-sync-alt text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <span><strong>Siswa lama</strong> (NIS sudah ada) — data diperbarui dan kelas diubah ke tahun ajaran yang dipilih. Cocok untuk kenaikan kelas (mis. 7A → 8A).</span>
                        </li>
                        <li class="flex gap-2 text-xs text-gray-600">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 flex-shrink-0"></i>
                            <span><strong>Kelas tidak ditemukan</strong> — baris dilewati dan dilaporkan sebagai error. Pastikan kelas sudah dibuat terlebih dahulu untuk tahun ajaran tujuan.</span>
                        </li>
                        <li class="flex gap-2 text-xs text-gray-600">
                            <i class="fas fa-layer-group text-purple-500 mt-0.5 flex-shrink-0"></i>
                            <span><strong>Baris kosong</strong> dilewati otomatis.</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Contoh Skenario Kenaikan Kelas</p>
                    <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600 space-y-1">
                        <p class="font-medium text-gray-700">Tahun ajaran 2024/2025 → 2025/2026:</p>
                        <p>1. Buat tahun ajaran <strong>2025/2026</strong> dan kelas-kelasnya (7A, 8A, 9A, dst.)</p>
                        <p>2. Export data dari tahun ajaran <strong>2024/2025</strong> sebagai referensi</p>
                        <p>3. Edit kolom <strong>Kelas</strong> sesuai posisi baru (mis. 7A → 8A)</p>
                        <p>4. Import ke tahun ajaran <strong>2025/2026</strong></p>
                        <p class="text-blue-600 mt-1"><i class="fas fa-info-circle mr-1"></i>Tidak ada aturan kelas baku — admin bebas menentukan kelas tujuan tiap siswa.</p>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2.5 flex gap-2 text-xs text-yellow-800">
                    <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                    <span>Pastikan <strong>baris pertama adalah header</strong> (NIS, Nama, Kelamin, Kelas, Alamat) dan tidak ada baris kosong di antara data. Gunakan tombol <strong>Template</strong> untuk format yang benar.</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
