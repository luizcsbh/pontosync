@extends('layouts.app')

@section('title', 'Marcações do Dia')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Marcações de Ponto</h1>
            <p class="text-xs text-slate-500 mt-0.5">Data: {{ $selectedDate->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('point-records.create') }}" class="px-3.5 py-2 bg-blue-600 text-white font-semibold text-xs rounded-xl hover:bg-blue-700 transition">
            + Nova Marcação
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-100">
        @forelse ($records as $record)
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">{{ $record->type_icon }}</span>
                    <div>
                        <span class="text-sm font-bold text-slate-900 block">{{ $record->type_name }}</span>
                        <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                            <span>Origem: <strong class="font-medium text-slate-700">{{ $record->source_label }}</strong></span>
                            @if ($record->ocr_confidence)
                                <span>• Confiança: {{ round($record->ocr_confidence * 100) }}%</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-lg font-black text-slate-800">{{ $record->formatted_time }}</span>
                    <a href="{{ route('point-records.edit', $record->id) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold p-1 hover:bg-blue-50 rounded">
                        Editar
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400 text-xs">
                Nenhuma marcação registrada para esta data.
            </div>
        @endforelse
    </div>
</div>
@endsection
