<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SiKas SMP PGRI 7 Bandung</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/pgri7.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-900 to-blue-700 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-36 h-36 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg p-3">
            <img src="{{ asset('storage/pgri7.png') }}" alt="Logo SMP PGRI 7" class="w-full h-full object-contain">
        </div>
        <h1 class="text-white text-2xl font-bold">SiKas</h1>
        <p class="text-blue-200 text-sm">Sistem Informasi Keuangan Sekolah</p>
        <p class="text-blue-300 text-xs mt-1">SMP PGRI 7 Kota Bandung</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-gray-800 text-xl font-semibold mb-6 text-center">Masuk ke Sistem</h2>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username atau Email</label>
                <input type="text" name="login" value="{{ old('login') }}" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       placeholder="Masukkan username atau email">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       placeholder="Masukkan password">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-blue-600">
                <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
            </div>

            <button type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg transition-colors">
                Masuk
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-500 text-center">
            <p>Akun demo: <code class="bg-gray-100 px-1 rounded">admin</code> / <code class="bg-gray-100 px-1 rounded">admin</code></p>
        </div>
    </div>
</div>
</body>
</html>
