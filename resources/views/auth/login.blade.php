<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SiKas SMP PGRI 7 Bandung</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/pgri7.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .bg-gedung {
            background-image: url('{{ asset('storage/gedung.png') }}');
            background-size: cover;
            background-position: center;
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px #f0f4ff inset !important;
        }
    </style>
</head>
<body class="min-h-screen flex">

    {{-- Left panel: gedung photo --}}
    <div class="hidden lg:flex lg:w-3/5 relative bg-gedung">
        {{-- Overlay gelap --}}
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-blue-800/60 to-transparent"></div>

        {{-- Konten overlay --}}
        <div class="relative z-10 flex flex-col justify-between p-12 w-full">
            {{-- Logo + nama sekolah --}}
            <div class="flex items-center gap-4">
              
   
            </div>

            {{-- Headline tengah --}}
            <div>
                <h1 class="text-white text-4xl font-extrabold leading-tight mb-4">
                    Sistem Informasi<br>Keuangan Sekolah
                </h1>
                <p class="text-blue-200 text-base max-w-sm">
                    Kelola tagihan, pembayaran, dan laporan keuangan siswa secara terpadu, transparan, dan efisien.
                </p>
                <div class="flex gap-6 mt-8">
                    <div class="glass rounded-xl px-5 py-3 text-center">
                        <i class="fas fa-users text-blue-200 text-xl mb-1"></i>
                        <p class="text-white font-bold text-lg">Multi Peran</p>
                        <p class="text-blue-300 text-xs">Admin, Bendahara, Wali Kelas, Siswa</p>
                    </div>
                    <div class="glass rounded-xl px-5 py-3 text-center">
                        <i class="fas fa-shield-alt text-blue-200 text-xl mb-1"></i>
                        <p class="text-white font-bold text-lg">Aman & Akurat</p>
                        <p class="text-blue-300 text-xs">Data terenkripsi & terlindungi</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <p class="text-blue-300 text-xs">© {{ date('Y') }} SiKas — SMP PGRI 7 Kota Bandung</p>
        </div>
    </div>

    {{-- Right panel: form --}}
    <div class="w-full lg:w-2/5 flex items-center justify-center bg-gray-50 p-8">
        <div class="w-full max-w-sm">

            {{-- Logo --}}
            <div class="flex flex-col items-center mb-8">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg p-2 mb-3">
                    <img src="{{ asset('storage/pgri7.png') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-gray-800 text-xl font-bold">SiKas</h1>
                <p class="text-gray-500 text-sm">SMP PGRI 7 Kota Bandung</p>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-extrabold text-gray-800">Selamat Datang</h2>
                <p class="text-gray-500 text-sm mt-1">Masuk untuk mengakses sistem</p>
            </div>

            @if($errors->any())
            <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
                <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="mb-5 flex items-start gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 text-sm">
                <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username atau Email</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white transition-all"
                               placeholder="Masukkan username atau email">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="w-full pl-10 pr-11 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white transition-all"
                               placeholder="Masukkan password">
                        <button type="button" @click="show = !show"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white font-bold py-3 rounded-xl transition-colors text-sm flex items-center justify-center gap-2 shadow-lg shadow-blue-200">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk ke Sistem
                </button>
            </form>

            <div class="mt-8 pt-5 border-t border-gray-200">
                <p class="text-xs text-gray-400 text-center mb-2">Akun demo</p>
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach([['admin','Admin'],['wali_kelas','Wali Kelas'],['bendahara','Bendahara'],['siswa','Siswa']] as [$u,$l])
                    <span class="bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-lg font-mono">{{ $u }}</span>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 text-center mt-2">Password sama dengan username</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
