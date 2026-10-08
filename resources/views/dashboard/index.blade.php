@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Card / Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-blue-200 text-xs font-semibold uppercase tracking-wider">Painel Principal</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold mt-0.5">Olá, {{ explode(' ', $user->name)[0] }}!</h1>
                </div>
                <div class="text-right">
                    <span class="text-xs text-blue-200 block">Hoje</span>
                    <span class="text-base font-bold bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10">
                        {{ $todayDate->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <!-- Total Hour Bank Highlight -->
            <div class="mt-6 pt-4 border-t border-white/20 flex items-center justify-between">
                <div>
                    <span class="text-xs text-blue-200 block">Saldo Banco de Horas</span>
                    <span class="text-2xl sm:text-3xl font-black tracking-tight {{ $hourBankBalance >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                        {{ $hourBankFormatted }}
                    </span>
                </div>
                <a href="{{ route('hour-bank.index') }}" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-2 rounded-xl transition text-white font-medium flex items-center space-x-1">
                    <span>Ver Extrato</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Big Action Buttons (Mobile First CTA) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- 📷 Registrar Ponto com Foto (OCR) -->
        <a href="{{ route('point-records.create', ['mode' => 'ocr']) }}"
           class="group relative flex items-center justify-center p-5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-2xl shadow-md hover:shadow-lg transition-all duration-200 transform active:scale-98">
            <div class="flex items-center space-x-3">
                <span class="text-2xl sm:text-3xl group-hover:scale-110 transition-transform">📷</span>
                <div class="text-left">
                    <span class="font-bold text-base block tracking-wide">REGISTRAR COM FOTO</span>
                    <span class="text-xs text-emerald-100 font-normal">Identificação via OCR</span>
                </div>
            </div>
        </a>

        <!-- ✏️ Registrar Manualmente -->
        <a href="{{ route('point-records.create', ['mode' => 'manual']) }}"
           class="flex items-center justify-center p-5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-2xl shadow-sm hover:shadow transition-all duration-200 active:scale-98">
            <div class="flex items-center space-x-3">
                <span class="text-2xl text-blue-600">✏️</span>
                <div class="text-left">
                    <span class="font-bold text-base block text-slate-900">Registrar manualmente</span>
                    <span class="text-xs text-slate-500 font-normal">Lançamento direto de horário</span>
                </div>
            </div>
        </a>
    </div>

    <!-- Jornada de Hoje -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <span>⏱</span>
                <span>Jornada de Hoje</span>
            </h2>
            <!-- Status Badge -->
            @if ($workDay->status === 'complete')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ✓ Jornada completa
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    ⚠ Incompleta
                </span>
            @endif
        </div>

        <!-- 4 Marcações Diárias -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $entry = $records->get('entry');
                $lunchStart = $records->get('lunch_start');
                $lunchEnd = $records->get('lunch_end');
                $exit = $records->get('exit');
            @endphp

            <!-- 1. Entrada -->
            <div class="p-3.5 rounded-xl border {{ $entry ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/50' }} transition">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="font-semibold text-emerald-600">🟢 Entrada</span>
                    @if($entry)
                        <a href="{{ route('point-records.edit', $entry->id) }}" class="text-[11px] text-blue-600 hover:underline">Editar</a>
                    @endif
                </div>
                <div class="mt-2 text-xl font-black {{ $entry ? 'text-slate-900' : 'text-slate-300' }}">
                    {{ $entry ? $entry->formatted_time : '--:--' }}
                </div>
                @if($entry)
                    <span class="text-[10px] text-slate-400 block mt-0.5 capitalize">{{ $entry->source_label }}</span>
                @endif
            </div>

            <!-- 2. Saída Almoço -->
            <div class="p-3.5 rounded-xl border {{ $lunchStart ? 'border-amber-200 bg-amber-50/40' : 'border-slate-200 bg-slate-50/50' }} transition">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="font-semibold text-amber-600">🟠 Saída Almoço</span>
                    @if($lunchStart)
                        <a href="{{ route('point-records.edit', $lunchStart->id) }}" class="text-[11px] text-blue-600 hover:underline">Editar</a>
                    @endif
                </div>
                <div class="mt-2 text-xl font-black {{ $lunchStart ? 'text-slate-900' : 'text-slate-300' }}">
                    {{ $lunchStart ? $lunchStart->formatted_time : '--:--' }}
                </div>
                @if($lunchStart)
                    <span class="text-[10px] text-slate-400 block mt-0.5 capitalize">{{ $lunchStart->source_label }}</span>
                @endif
            </div>

            <!-- 3. Retorno Almoço -->
            <div class="p-3.5 rounded-xl border {{ $lunchEnd ? 'border-blue-200 bg-blue-50/40' : 'border-slate-200 bg-slate-50/50' }} transition">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="font-semibold text-blue-600">🔵 Retorno Almoço</span>
                    @if($lunchEnd)
                        <a href="{{ route('point-records.edit', $lunchEnd->id) }}" class="text-[11px] text-blue-600 hover:underline">Editar</a>
                    @endif
                </div>
                <div class="mt-2 text-xl font-black {{ $lunchEnd ? 'text-slate-900' : 'text-slate-300' }}">
                    {{ $lunchEnd ? $lunchEnd->formatted_time : '--:--' }}
                </div>
                @if($lunchEnd)
                    <span class="text-[10px] text-slate-400 block mt-0.5 capitalize">{{ $lunchEnd->source_label }}</span>
                @endif
            </div>

            <!-- 4. Saída -->
            <div class="p-3.5 rounded-xl border {{ $exit ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-slate-50/50' }} transition">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="font-semibold text-rose-600">🔴 Saída</span>
                    @if($exit)
                        <a href="{{ route('point-records.edit', $exit->id) }}" class="text-[11px] text-blue-600 hover:underline">Editar</a>
                    @endif
                </div>
                <div class="mt-2 text-xl font-black {{ $exit ? 'text-slate-900' : 'text-slate-300' }}">
                    {{ $exit ? $exit->formatted_time : '--:--' }}
                </div>
                @if($exit)
                    <span class="text-[10px] text-slate-400 block mt-0.5 capitalize">{{ $exit->source_label }}</span>
                @endif
            </div>
        </div>

        <!-- Indicador de Próxima Ação -->
        @if ($nextExpectedType)
            <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between">
                <span class="text-xs text-blue-800 font-medium">
                    👉 Próxima marcação sugerida: <strong>{{ \App\Models\PointRecord::typeOptions()[$nextExpectedType] }}</strong>
                </span>
                <a href="{{ route('point-records.create', ['type' => $nextExpectedType, 'mode' => 'ocr']) }}"
                   class="text-xs bg-blue-600 text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-blue-700 transition">
                    Bater Ponto
                </a>
            </div>
        @endif
    </div>

    <!-- Resumo dos Cálculos do Dia -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 text-center shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Trabalhadas</span>
            <span class="text-lg sm:text-xl font-black text-slate-800 mt-1 block">
                {{ $workDay->formatted_worked_minutes }}
            </span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 text-center shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Previstas</span>
            <span class="text-lg sm:text-xl font-black text-slate-800 mt-1 block">
                {{ $workDay->formatted_expected_minutes }}
            </span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 text-center shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Saldo Hoje</span>
            <span class="text-lg sm:text-xl font-black mt-1 block {{ ($workDay->balance_minutes ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $workDay->formatted_balance }}
            </span>
        </div>
    </div>

</div>
@endsection
