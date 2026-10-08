@extends('layouts.app')

@section('title', 'Corrigir Marcação')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center space-x-1">
            <span>←</span>
            <span>Voltar ao painel</span>
        </a>
        <h1 class="text-lg font-bold text-slate-900">Corrigir Marcação</h1>
        <div class="w-12"></div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('point-records.update', $record->id) }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
        @csrf
        @method('PUT')

        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Detalhes da Marcação</span>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-800">{{ $record->type_label }}</span>
                <span class="text-xs font-semibold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200">{{ $workDay->date->format('d/m/Y') }}</span>
            </div>
            @if ($record->original_recorded_at)
                <p class="text-[11px] text-amber-700 mt-2 bg-amber-50 p-2 rounded border border-amber-200">
                    ⚠ Horário original registrado: <strong>{{ $record->original_recorded_at->format('H:i') }}</strong>
                </p>
            @endif
        </div>

        <div>
            <label for="time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Novo Horário (24h)</label>
            <input type="text" name="time" id="time" value="{{ old('time', $record->formatted_time) }}" required maxlength="5"
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-semibold text-slate-800">
        </div>

        <div>
            <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Motivo do Ajuste</label>
            <textarea name="notes" id="notes" rows="3" required placeholder="Justifique o motivo da correção..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs text-slate-800">{{ old('notes', $record->notes) }}</textarea>
        </div>

        <div class="pt-3 flex space-x-3">
            <button type="submit"
                    class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition text-sm">
                Salvar Correção
            </button>
        </div>
    </form>

    <!-- Delete Record Option -->
    <div class="pt-4 text-center">
        <form action="{{ route('point-records.destroy', $record->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover esta marcação?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline">
                🗑 Remover esta marcação
            </button>
        </form>
    </div>
</div>
@endsection
