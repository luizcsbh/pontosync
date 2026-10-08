@extends('layouts.app')

@section('title', 'Editar Jornada')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('work-schedules.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center space-x-1">
            <span>←</span>
            <span>Voltar para jornadas</span>
        </a>
        <h1 class="text-lg font-bold text-slate-900">Editar Jornada: {{ $schedule->name }}</h1>
        <div class="w-12"></div>
    </div>

    <form action="{{ route('work-schedules.update', $schedule->id) }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nome da Jornada</label>
            <input type="text" name="name" id="name" value="{{ old('name', $schedule->name) }}" required
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 text-sm font-semibold text-slate-800">
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ $schedule->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <label for="is_active" class="text-xs font-medium text-slate-700">Definir como jornada ativa principal</label>
        </div>

        <div class="space-y-4 pt-2 border-t border-slate-100">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Configuração por Dia da Semana</h2>

            @foreach ($daysOfWeek as $dayIndex => $dayName)
                @php
                    $d = $scheduleDays->get($dayIndex);
                    $isWorkday = $d ? $d->is_workday : in_array($dayIndex, [1,2,3,4,5]);
                @endphp
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3" x-data="{ isWorkday: {{ $isWorkday ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="hidden" name="days[{{ $dayIndex }}][is_workday]" :value="isWorkday ? 1 : 0">
                            <input type="checkbox" x-model="isWorkday" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-bold text-slate-900">{{ $dayName }}</span>
                        </label>
                        <span class="text-[11px] font-medium" :class="isWorkday ? 'text-emerald-600' : 'text-slate-400'" x-text="isWorkday ? 'Dia de Trabalho' : 'Descanso / Folga'"></span>
                    </div>

                    <div x-show="isWorkday" class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-200">
                        <div>
                            <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Entrada</label>
                            <input type="text" name="days[{{ $dayIndex }}][entry]" value="{{ $d && $d->entry_time !== '--:--' ? $d->entry_time : '' }}" placeholder="08:00" maxlength="5"
                                   class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-center font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Saída Almoço</label>
                            <input type="text" name="days[{{ $dayIndex }}][lunch_start]" value="{{ $d && $d->lunch_start_time !== '--:--' ? $d->lunch_start_time : '' }}" placeholder="12:00" maxlength="5"
                                   class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-center font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Retorno Almoço</label>
                            <input type="text" name="days[{{ $dayIndex }}][lunch_end]" value="{{ $d && $d->lunch_end_time !== '--:--' ? $d->lunch_end_time : '' }}" placeholder="13:00" maxlength="5"
                                   class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-center font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Saída</label>
                            <input type="text" name="days[{{ $dayIndex }}][exit]" value="{{ $d && $d->exit_time !== '--:--' ? $d->exit_time : '' }}" placeholder="18:00" maxlength="5"
                                   class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 text-center font-semibold">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition text-sm">
            Salvar Alterações
        </button>
    </form>
</div>
@endsection
