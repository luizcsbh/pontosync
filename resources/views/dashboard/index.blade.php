@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6" x-data="{ photoModalOpen: false, modalImageUrl: '', modalTitle: '', modalDetails: '' }">

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

        <!-- 4 Marcações Diárias com Thumbnails -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $entry = $records->get('entry');
                $lunchStart = $records->get('lunch_start');
                $lunchEnd = $records->get('lunch_end');
                $exit = $records->get('exit');
                $list = [
                    ['rec' => $entry, 'color' => 'emerald', 'label' => '🟢 Entrada'],
                    ['rec' => $lunchStart, 'color' => 'amber', 'label' => '🟠 Saída Almoço'],
                    ['rec' => $lunchEnd, 'color' => 'blue', 'label' => '🔵 Retorno Almoço'],
                    ['rec' => $exit, 'color' => 'rose', 'label' => '🔴 Saída'],
                ];
            @endphp

            @foreach ($list as $item)
                @php $r = $item['rec']; @endphp
                <div class="p-3.5 rounded-xl border {{ $r ? 'border-'.$item['color'].'-200 bg-'.$item['color'].'-50/40' : 'border-slate-200 bg-slate-50/50' }} transition relative group">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="font-semibold text-{{ $item['color'] }}-600">{{ $item['label'] }}</span>
                        @if($r)
                            <a href="{{ route('point-records.edit', $r->id) }}" class="text-[11px] text-blue-600 hover:underline">Editar</a>
                        @endif
                    </div>
                    
                    <div class="mt-2 text-xl font-black {{ $r ? 'text-slate-900' : 'text-slate-300' }}">
                        {{ $r ? $r->formatted_time : '--:--' }}
                    </div>

                    @if($r)
                        <div class="mt-1.5 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 capitalize">{{ $r->source_label }}</span>
                            
                            <!-- Thumbnail da Foto (quando existir) -->
                            @if ($r->image)
                                <button type="button"
                                        @click="modalImageUrl = '{{ $r->image->url }}'; modalTitle = '{{ $item['label'] }} ({{ $r->formatted_time }})'; modalDetails = 'Origem: {{ $r->source_label }}'; photoModalOpen = true;"
                                        class="inline-flex items-center space-x-1 p-0.5 bg-white border border-slate-200 rounded-lg hover:border-blue-400 shadow-sm transition"
                                        title="Ver foto do comprovante">
                                    <img src="{{ $r->image->url }}" alt="Thumb" class="w-6 h-6 object-cover rounded">
                                    <span class="text-[9px] text-slate-500 pr-1">📷</span>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
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

    <!-- Modal Lightbox de Visualização da Foto em Alta Resolução -->
    <div x-show="photoModalOpen"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-cloak
         @keydown.escape.window="photoModalOpen = false">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 animate-fadeIn"
             @click.away="photoModalOpen = false">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm" x-text="modalTitle"></h3>
                    <p class="text-[11px] text-slate-500" x-text="modalDetails"></p>
                </div>
                <button type="button" @click="photoModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold p-1">
                    ✕
                </button>
            </div>
            <div class="p-4 bg-slate-950 flex items-center justify-center min-h-[250px]">
                <img :src="modalImageUrl" alt="Comprovante de Ponto" class="max-h-96 w-auto object-contain rounded-xl shadow">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="photoModalOpen = false"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold text-xs rounded-xl transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
