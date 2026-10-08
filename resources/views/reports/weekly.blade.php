@extends('layouts.app')

@section('title', 'Relatório Semanal')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Relatório Semanal</h1>
            <p class="text-xs text-slate-500 mt-0.5">Semana: {{ $weekLabel }}</p>
        </div>

        <form action="{{ route('reports.weekly') }}" method="GET" class="flex items-center space-x-2 pt-2 border-t border-slate-100">
            <input type="date" name="start_date" value="{{ $startDate }}" class="flex-1 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition">
                Filtrar
            </button>
        </form>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Horas Previstas</span>
            <span class="text-lg font-black text-slate-800 mt-1 block">{{ $totalExpectedFormatted }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Horas Trabalhadas</span>
            <span class="text-lg font-black text-slate-800 mt-1 block">{{ $totalWorkedFormatted }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Saldo da Semana</span>
            <span class="text-lg font-black mt-1 block {{ str_starts_with($weekBalanceFormatted, '+') ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $weekBalanceFormatted }}
            </span>
        </div>
    </div>
</div>
@endsection
