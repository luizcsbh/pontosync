@extends('layouts.app')

@section('title', 'Jornadas de Trabalho')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Jornadas de Trabalho</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure seus horários previstos de trabalho por dia da semana</p>
        </div>
        <a href="{{ route('work-schedules.create') }}" class="px-3.5 py-2 bg-blue-600 text-white font-semibold text-xs rounded-xl hover:bg-blue-700 transition">
            + Nova Jornada
        </a>
    </div>

    <div class="space-y-4">
        @forelse ($schedules as $sched)
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <h2 class="font-bold text-slate-900 text-base">{{ $sched->name }}</h2>
                        @if ($sched->is_active)
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Ativa</span>
                        @endif
                    </div>
                    <a href="{{ route('work-schedules.edit', $sched->id) }}" class="text-xs text-blue-600 hover:underline font-semibold">Editar</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-7 gap-2 text-center text-xs">
                    @foreach ($sched->days as $d)
                        <div class="p-2.5 rounded-xl border {{ $d->is_workday ? 'border-blue-100 bg-blue-50/30' : 'border-slate-100 bg-slate-50 text-slate-400' }}">
                            <span class="font-bold block text-[11px] {{ $d->is_workday ? 'text-slate-800' : 'text-slate-400' }}">{{ $d->short_day_name }}</span>
                            @if ($d->is_workday)
                                <span class="text-[10px] text-slate-600 block mt-1">{{ $d->entry_time }} - {{ $d->exit_time }}</span>
                                <span class="text-[10px] font-bold text-blue-600 block mt-0.5">{{ $d->expected_time }}</span>
                            @else
                                <span class="text-[10px] block mt-1">Folga</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-white rounded-2xl border border-slate-100 text-slate-400 text-xs">
                Nenhuma jornada cadastrada.
            </div>
        @endforelse
    </div>
</div>
@endsection
