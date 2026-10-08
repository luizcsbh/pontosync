@extends('layouts.app')

@section('title', 'Banco de Horas')

@section('content')
<div class="space-y-6">

    <!-- Header & Accumulated Balance Card -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Banco de Horas</span>
            <span class="text-xs bg-white/10 px-3 py-1 rounded-full text-slate-300">
                {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->locale('pt_BR')->translatedFormat('F / Y') }}
            </span>
        </div>

        <div class="mt-4">
            <span class="text-xs text-slate-400 block">Saldo Acumulado Atual</span>
            <span class="text-3xl sm:text-4xl font-black tracking-tight {{ $currentBalance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $currentBalanceFormatted }}
            </span>
        </div>

        <div class="mt-6 pt-4 border-t border-white/10 grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px]">Movimentação do Mês</span>
                <span class="font-bold text-sm {{ ($monthSummary['total_minutes'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $monthSummary['total_formatted'] }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Fechamento do Mês</span>
                <span class="font-bold text-sm text-slate-200">
                    {{ $monthSummary['closing_balance_formatted'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Month Selector Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <form action="{{ route('hour-bank.index') }}" method="GET" class="flex items-center space-x-2">
            <select name="month" class="flex-1 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-white">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2026, $m, 1)->locale('pt_BR')->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="w-24 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-white">
                @for ($y = 2025; $y <= 2027; $y++)
                    <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition">
                Ver
            </button>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Extrato de Movimentações</h2>
            <span class="text-xs text-slate-400">{{ count($transactions) }} lançamentos</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Data</th>
                        <th class="py-3 px-4">Descrição</th>
                        <th class="py-3 px-3 text-center">Saldo do Dia</th>
                        <th class="py-3 px-4 text-right">Saldo Acumulado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $tx->date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">
                                {{ $tx->description }}
                            </td>
                            <td class="py-3.5 px-3 text-center font-bold whitespace-nowrap {{ $tx->minutes >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $tx->formatted_minutes }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black whitespace-nowrap {{ $tx->balance_minutes >= 0 ? 'text-slate-900' : 'text-rose-600' }}">
                                {{ $tx->formatted_balance_minutes }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                Nenhuma movimentação registrada no período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
