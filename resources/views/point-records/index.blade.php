@extends('layouts.app')

@section('title', 'Marcações do Dia')

@section('content')
<div class="space-y-6" x-data="{ photoModalOpen: false, modalImageUrl: '', modalTitle: '', modalDetails: '' }">
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
                <div class="flex items-center space-x-3.5">
                    <!-- Thumbnail da Imagem (se houver) ou Ícone -->
                    @if ($record->image)
                        <button type="button"
                                @click="modalImageUrl = '{{ $record->image->url }}'; modalTitle = '{{ $record->type_name }} ({{ $record->formatted_time }})'; modalDetails = 'Data: {{ $record->workDay->date->format('d/m/Y') }} | Origem: {{ $record->source_label }}'; photoModalOpen = true;"
                                class="relative group cursor-pointer focus:outline-none" title="Clique para ampliar o comprovante">
                            <img src="{{ $record->image->url }}" alt="Thumb" class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-sm group-hover:opacity-80 transition">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-white text-xs opacity-0 group-hover:opacity-100 rounded-xl transition">
                                🔍
                            </span>
                        </button>
                    @else
                        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-2xl">
                            {{ $record->type_icon }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-bold text-slate-900">{{ $record->type_name }}</span>
                            @if ($record->image)
                                <span class="text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-semibold">Com Foto</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                            <span>Origem: <strong class="font-medium text-slate-700">{{ $record->source_label }}</strong></span>
                            @if ($record->ocr_confidence)
                                <span>• Confiança OCR: <strong>{{ round($record->ocr_confidence * 100) }}%</strong></span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-lg font-black text-slate-800">{{ $record->formatted_time }}</span>
                    <a href="{{ route('point-records.edit', $record->id) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold p-1.5 hover:bg-blue-50 rounded-lg transition">
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

    <!-- Modal Lightbox da Foto -->
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
