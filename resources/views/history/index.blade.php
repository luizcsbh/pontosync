@extends('layouts.app')

@section('title', 'Histórico de Ponto')

@section('content')
<div class="space-y-6">

    <!-- Header & Filters -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-slate-900">Histórico de Ponto</h1>
            <a href="{{ route('reports.monthly', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
               class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg transition">
                Exportar / Relatório →
            </a>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('history.index') }}" method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 border-t border-slate-100">
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Mês</label>
                <select name="month" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-white">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->locale('pt_BR')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Ano</label>
                <select name="year" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-white">
                    @for ($y = 2025; $y <= 2027; $y++)
                        <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition">
                    Filtrar Histórico
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Badges -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Previsto Total</span>
            <span class="text-base font-black text-slate-800 mt-0.5 block">{{ $totalExpectedFormatted }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Trabalhado Total</span>
            <span class="text-base font-black text-slate-800 mt-0.5 block">{{ $totalWorkedFormatted }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Saldo do Período</span>
            <span class="text-base font-black mt-0.5 block {{ str_starts_with($totalBalanceFormatted, '+') ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $totalBalanceFormatted }}
            </span>
        </div>
    </div>

    <!-- History Table (Responsive Card layout on mobile, table on desktop) -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Data</th>
                        <th class="py-3 px-2 text-center">Entrada</th>
                        <th class="py-3 px-2 text-center">Almoço</th>
                        <th class="py-3 px-2 text-center">Retorno</th>
                        <th class="py-3 px-2 text-center">Saída</th>
                        <th class="py-3 px-2 text-center">Trabalhadas</th>
                        <th class="py-3 px-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($workDays as $wd)
                        @php
                            $records = $wd->pointRecords->keyBy('type');
                            $entry = $records->get('entry');
                            $lStart = $records->get('lunch_start');
                            $lEnd = $records->get('lunch_end');
                            $exit = $records->get('exit');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('point-records.index', ['date' => $wd->date->format('Y-m-d')]) }}" class="hover:text-blue-600 hover:underline">
                                    {{ $wd->date->format('d/m') }}
                                    <span class="text-[10px] text-slate-400 font-normal block">{{ $wd->date->locale('pt_BR')->translatedFormat('D') }}</span>
                                </a>
                            </td>
                            <td class="py-3 px-2 text-center text-slate-700 font-medium whitespace-nowrap">
                                {{ $entry ? $entry->formatted_time : '--:--' }}
                            </td>
                            <td class="py-3 px-2 text-center text-slate-700 font-medium whitespace-nowrap">
                                {{ $lStart ? $lStart->formatted_time : '--:--' }}
                            </td>
                            <td class="py-3 px-2 text-center text-slate-700 font-medium whitespace-nowrap">
                                {{ $lEnd ? $lEnd->formatted_time : '--:--' }}
                            </td>
                            <td class="py-3 px-2 text-center text-slate-700 font-medium whitespace-nowrap">
                                {{ $exit ? $exit->formatted_time : '--:--' }}
                            </td>
                            <td class="py-3 px-2 text-center font-bold text-slate-800 whitespace-nowrap">
                                {{ $wd->formatted_worked_minutes }}
                            </td>
                            <td class="py-3 px-3 text-right font-black whitespace-nowrap {{ ($wd->balance_minutes ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $wd->formatted_balance }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Nenhum registro encontrado para os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($workDays->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $workDays->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
