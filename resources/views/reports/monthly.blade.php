@extends('layouts.app')

@section('title', 'Relatório Mensal')

@section('content')
<div class="space-y-6">

    <!-- Header & Actions -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Relatório Mensal de Ponto</h1>
                <p class="text-xs text-slate-500 mt-0.5">Espelho detalhado com fechamento de horas e exportação</p>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.export.pdf', ['month' => $month, 'year' => $year]) }}"
                   class="inline-flex items-center space-x-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                    <span>📄</span>
                    <span>Exportar PDF</span>
                </a>
                <a href="{{ route('reports.export.csv', ['month' => $month, 'year' => $year]) }}"
                   class="inline-flex items-center space-x-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                    <span>📊</span>
                    <span>Exportar CSV / Excel</span>
                </a>
            </div>
        </div>

        <!-- Month Selector Form -->
        <form action="{{ route('reports.monthly') }}" method="GET" class="flex items-center space-x-2 pt-2 border-t border-slate-100">
            <select name="month" class="flex-1 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-white">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2026, $m, 1)->locale('pt_BR')->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="w-24 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-white">
                @for ($y = 2025; $y <= 2027; $y++)
                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition">
                Visualizar
            </button>
        </form>
    </div>

    <!-- Monthly Metric Cards (Section 18 requirement) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dias Trabalhados</span>
            <span class="text-xl font-black text-slate-800 mt-1 block">{{ $workedDaysCount }} dias</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Horas Previstas</span>
            <span class="text-xl font-black text-slate-800 mt-1 block">{{ $totalExpectedFormatted }}</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Horas Trabalhadas</span>
            <span class="text-xl font-black text-slate-800 mt-1 block">{{ $totalWorkedFormatted }}</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Saldo do Mês</span>
            <span class="text-xl font-black mt-1 block {{ str_starts_with($monthBalanceFormatted, '+') ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $monthBalanceFormatted }}
            </span>
        </div>
    </div>

    <!-- Accumulated Bank Highlight -->
    <div class="p-4 bg-slate-900 text-white rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="text-xl">🏦</span>
            <span class="text-xs text-slate-300 font-medium">Banco de Horas Acumulado:</span>
        </div>
        <span class="text-lg font-black {{ str_starts_with($accumulatedBalanceFormatted, '+') ? 'text-emerald-400' : 'text-rose-400' }}">
            {{ $accumulatedBalanceFormatted }}
        </span>
    </div>

    <!-- Monthly Detailed Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Data</th>
                        <th class="py-3 px-2 text-center">Entrada</th>
                        <th class="py-3 px-2 text-center">Saída Alm.</th>
                        <th class="py-3 px-2 text-center">Ret. Alm.</th>
                        <th class="py-3 px-2 text-center">Saída</th>
                        <th class="py-3 px-2 text-center">Previsto</th>
                        <th class="py-3 px-2 text-center">Trabalhado</th>
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
                                {{ $wd->date->format('d/m/Y') }}
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
                            <td class="py-3 px-2 text-center text-slate-500 whitespace-nowrap">
                                {{ $wd->formatted_expected_minutes }}
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
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                Nenhum registro para este mês.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
