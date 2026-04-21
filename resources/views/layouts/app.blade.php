<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SiKas') — SMP PGRI 7 Bandung</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/pgri7.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link.active { @apply bg-blue-700 text-white; }
    </style>
</head>
<body class="bg-gray-100 font-sans" x-data="{ sidebarOpen: true }">

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <aside class="bg-blue-900 text-white transition-all duration-300 flex flex-col"
           :class="sidebarOpen ? 'w-64' : 'w-16'">
        {{-- Logo --}}
        <div class="flex items-center gap-3 p-4 border-b border-blue-700">
            <img src="{{ asset('storage/pgri7.png') }}" alt="Logo" class="w-8 h-8 object-contain flex-shrink-0">
            <div x-show="sidebarOpen" x-cloak>
                <p class="font-bold text-sm leading-tight">SiKas</p>
                <p class="text-blue-300 text-xs">SMP PGRI 7 Bandung</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-4 overflow-y-auto">
            @include('layouts.partials.sidebar-menu')
        </nav>

        {{-- User Info --}}
        <div class="border-t border-blue-700">
            <a href="{{ route('settings') }}"
               class="flex items-center gap-3 p-4 hover:bg-blue-800 transition-colors {{ request()->routeIs('settings') ? 'bg-blue-800' : '' }}">
                {{-- Avatar --}}
                <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-blue-600
                            {{ auth()->user()->foto_profil ? '' : 'bg-blue-600 flex items-center justify-center' }}">
                    @if(auth()->user()->foto_profil)
                        <img src="{{ auth()->user()->fotoProfilUrl() }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-white text-sm font-bold">{{ auth()->user()->inisial() }}</span>
                    @endif
                </div>
                <div x-show="sidebarOpen" x-cloak class="min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-blue-300 text-xs truncate">{{ ucfirst(str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? '')) }}</p>
                </div>
                <i class="fas fa-cog text-blue-400 text-xs ml-auto flex-shrink-0" x-show="sidebarOpen" x-cloak></i>
            </a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Header --}}
        <header class="bg-white shadow-sm px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-gray-700 font-semibold text-lg">@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('settings') }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-gray-200
                                {{ auth()->user()->foto_profil ? '' : 'bg-blue-600 flex items-center justify-center' }}">
                        @if(auth()->user()->foto_profil)
                            <img src="{{ auth()->user()->fotoProfilUrl() }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-xs font-bold">{{ auth()->user()->inisial() }}</span>
                        @endif
                    </div>
                    <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                </a>
                <div class="w-px h-5 bg-gray-200"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden sm:block">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{-- Alert --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex items-center justify-between">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex items-center justify-between">
                    <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
