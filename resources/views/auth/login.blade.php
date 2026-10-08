@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-slate-100">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-3 text-3xl">
            🕐
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Ponto<span class="text-blue-600">Sync</span></h1>
        <p class="text-xs text-slate-500 mt-1">Registro de Ponto Inteligente com OCR</p>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 p-3 rounded-md mb-6">
            <p class="text-xs font-medium text-rose-800">{{ $errors->first() }}</p>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">E-mail</label>
            <input type="email" name="email" id="email" value="{{ old('email', 'luiz@pontosync.test') }}" required autofocus
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Senha</label>
            <input type="password" name="password" id="password" value="password" required
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
        </div>

        <div class="flex items-center justify-between text-xs text-slate-600">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>Lembrar de mim</span>
            </label>
        </div>

        <button type="submit"
                class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all text-sm flex items-center justify-center space-x-2">
            <span>Entrar no Sistema</span>
            <span>→</span>
        </button>
    </form>

    <!-- Credentials Hint -->
    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-400 font-medium">Usuários de teste padrão:</p>
        <div class="mt-2 text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-left space-y-1">
            <p>👤 <strong>luiz@pontosync.test</strong> / <code class="bg-white px-1 py-0.5 rounded">password</code></p>
            <p>👤 <strong>maria@pontosync.test</strong> / <code class="bg-white px-1 py-0.5 rounded">password</code></p>
        </div>
    </div>
</div>
@endsection
