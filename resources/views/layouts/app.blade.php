<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PontoSync') }} - @yield('title', 'Registro de Ponto')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        },
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col justify-between" x-data="{ mobileMenuOpen: false }">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <span class="text-2xl">🕐</span>
                        <span class="font-extrabold text-xl tracking-tight text-slate-900">Ponto<span class="text-blue-600">Sync</span></span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex space-x-6 items-center">
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Dashboard</a>
                    <a href="{{ route('point-records.create') }}" class="text-sm font-medium {{ request()->routeIs('point-records.*') ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Registrar Ponto</a>
                    <a href="{{ route('history.index') }}" class="text-sm font-medium {{ request()->routeIs('history.*') ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Histórico</a>
                    <a href="{{ route('hour-bank.index') }}" class="text-sm font-medium {{ request()->routeIs('hour-bank.*') ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Banco de Horas</a>
                    <a href="{{ route('reports.monthly') }}" class="text-sm font-medium {{ request()->routeIs('reports.*') ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Relatórios</a>
                    <a href="{{ route('work-schedules.index') }}" class="text-sm font-medium {{ request()->routeIs('work-schedules.*') ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Jornadas</a>
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-3">
                    <span class="text-xs sm:text-sm font-medium text-slate-700 hidden sm:inline-block">Olá, <strong class="font-semibold">{{ Auth::user()->name ?? 'Usuário' }}</strong></span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-slate-500 hover:text-red-600 hover:bg-slate-100 rounded-lg transition" title="Sair">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Alerts -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-emerald-500 text-lg font-bold">✓</span>
                    <span class="text-sm font-medium text-emerald-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-rose-500 text-lg font-bold">⚠</span>
                    <span class="text-sm font-medium text-rose-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-md shadow-sm mb-4">
                <ul class="text-sm font-medium text-rose-800 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-4 mb-20 md:mb-8">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar (Fixed) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 shadow-lg pb-safe">
        <div class="grid grid-cols-5 h-16 text-center text-xs">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}">
                <span class="text-xl">🏠</span>
                <span class="mt-1 text-[10px]">Início</span>
            </a>
            <a href="{{ route('point-records.create', ['mode' => 'ocr']) }}" class="flex flex-col items-center justify-center text-blue-600 font-bold relative -top-3">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                    <span class="text-xl">📷</span>
                </div>
                <span class="text-[10px] text-blue-600">Ponto</span>
            </a>
            <a href="{{ route('history.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('history.*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}">
                <span class="text-xl">📅</span>
                <span class="mt-1 text-[10px]">Histórico</span>
            </a>
            <a href="{{ route('hour-bank.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('hour-bank.*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}">
                <span class="text-xl">⏱</span>
                <span class="mt-1 text-[10px]">Banco</span>
            </a>
            <a href="{{ route('reports.monthly') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('reports.*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}">
                <span class="text-xl">📊</span>
                <span class="mt-1 text-[10px]">Relatórios</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
