@extends('layouts.app')

@section('title', 'Registrar Ponto')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="pointRegistrationApp()">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center space-x-1">
            <span>←</span>
            <span>Voltar ao painel</span>
        </a>
        <h1 class="text-lg font-bold text-slate-900">Registrar Marcação</h1>
        <div class="w-12"></div>
    </div>

    <!-- Mode Selector Tabs -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl">
        <button type="button" @click="setMode('ocr')"
                :class="mode === 'ocr' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 font-medium'"
                class="flex-1 py-2.5 text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
            <span>📷</span>
            <span>Foto / OCR</span>
        </button>
        <button type="button" @click="setMode('manual')"
                :class="mode === 'manual' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 font-medium'"
                class="flex-1 py-2.5 text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
            <span>✏️</span>
            <span>Manual</span>
        </button>
    </div>

    <!-- OCR Camera / Upload Section -->
    <div x-show="mode === 'ocr'" class="space-y-4">
        <div class="bg-white p-6 rounded-2xl border-2 border-dashed border-slate-200 text-center relative overflow-hidden">
            
            <template x-if="!imagePreview">
                <div class="py-6 space-y-4">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto text-3xl">
                        📷
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Fotografe seu cartão de ponto</h3>
                        <p class="text-xs text-slate-500 mt-1">O OCR identificará automaticamente data e horário</p>
                    </div>

                    <label class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md cursor-pointer transition active:scale-95 space-x-2">
                        <span>Tirar Foto / Carregar</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handleImageUpload($event)">
                    </label>
                </div>
            </template>

            <!-- Image Preview & OCR Status -->
            <template x-if="imagePreview">
                <div class="space-y-4">
                    <div class="relative rounded-xl overflow-hidden max-h-64 border border-slate-200 bg-slate-900">
                        <img :src="imagePreview" class="w-full h-auto object-contain mx-auto max-h-64">
                        <button type="button" @click="resetImage()"
                                class="absolute top-2 right-2 bg-black/60 text-white p-1.5 rounded-full hover:bg-black/80 transition text-xs">
                            ✕ Nova Foto
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div x-show="isProcessingOcr" class="py-3 flex items-center justify-center space-x-2 text-blue-600">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-xs font-semibold">Analisando imagem com OCR...</span>
                    </div>

                    <!-- OCR Result Badge -->
                    <template x-if="ocrProcessed && !isProcessingOcr">
                        <div class="p-3.5 rounded-xl border text-left"
                             :class="{
                                 'bg-emerald-50 border-emerald-200 text-emerald-900': ocrConfidence >= 0.90,
                                 'bg-amber-50 border-amber-200 text-amber-900': ocrConfidence >= 0.70 && ocrConfidence < 0.90,
                                 'bg-rose-50 border-rose-200 text-rose-900': ocrConfidence < 0.70
                             }">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span x-text="ocrConfidence >= 0.90 ? '✓ Reconhecimento de Alta Confiança' : (ocrConfidence >= 0.70 ? '⚠ Confiança Média — Verifique os dados' : '⚠ Baixa Confiança — Revisão Necessária')"></span>
                                <span class="px-2 py-0.5 rounded-full bg-white/70" x-text="ocrConfidencePercent + '%'"></span>
                            </div>
                            <p class="text-[11px] mt-1 opacity-90" x-show="ocrRawText">
                                Texto detectado: "<span x-text="ocrRawText"></span>"
                            </p>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Registration Form (Shared for OCR & Manual) -->
    <form action="{{ route('point-records.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
        @csrf

        <input type="hidden" name="source" :value="mode === 'ocr' ? 'ocr' : 'manual'">
        <input type="hidden" name="ocr_confidence" :value="ocrConfidence">
        <input type="hidden" name="ocr_raw_text" :value="ocrRawText">
        <input type="file" name="photo" id="formPhotoInput" class="hidden">

        <!-- Tipo de Marcação -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tipo de Marcação</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($typeOptions as $val => $label)
                    <label class="flex items-center p-3 rounded-xl border cursor-pointer transition text-xs font-semibold"
                           :class="selectedType === '{{ $val }}' ? 'border-blue-600 bg-blue-50/60 text-blue-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                        <input type="radio" name="type" value="{{ $val }}" x-model="selectedType" class="hidden">
                        <span class="mr-2">
                            @if($val === 'entry') 🟢 @elseif($val === 'lunch_start') 🟠 @elseif($val === 'lunch_end') 🔵 @else 🔴 @endif
                        </span>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Data e Horário em Grid -->
        <div class="grid grid-cols-2 gap-3 pt-2">
            <!-- Data -->
            <div>
                <label for="date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Data (DD/MM/AAAA)</label>
                <input type="text" name="date" id="date" x-model="dateInput" required
                       placeholder="DD/MM/AAAA"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-semibold text-slate-800">
            </div>

            <!-- Horário -->
            <div>
                <label for="time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Horário (24h)</label>
                <input type="text" name="time" id="time" x-model="timeInput" required
                       placeholder="HH:MM" maxlength="5"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-semibold text-slate-800">
            </div>
        </div>

        <!-- Observações Opcionais -->
        <div class="pt-2">
            <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Observações (opcional)</label>
            <textarea name="notes" id="notes" rows="2" placeholder="Motivo de ajuste ou notas sobre a marcação..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs text-slate-800"></textarea>
        </div>

        <!-- Submit CTA Button -->
        <div class="pt-4">
            <button type="submit"
                    class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center space-x-2 text-base active:scale-98">
                <span>✓ Confirmar e Salvar Marcação</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function pointRegistrationApp() {
        return {
            mode: '{{ $mode }}',
            selectedType: '{{ $defaultType }}',
            dateInput: '{{ $todayDate }}',
            timeInput: '{{ $currentTime }}',
            imagePreview: null,
            isProcessingOcr: false,
            ocrProcessed: false,
            ocrConfidence: null,
            ocrConfidencePercent: 0,
            ocrRawText: '',

            setMode(newMode) {
                this.mode = newMode;
            },

            resetImage() {
                this.imagePreview = null;
                this.ocrProcessed = false;
                this.ocrConfidence = null;
                this.ocrRawText = '';
                document.getElementById('formPhotoInput').value = '';
            },

            async handleImageUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                // Sync file with hidden form input
                const formInput = document.getElementById('formPhotoInput');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                formInput.files = dataTransfer.files;

                // Create local preview
                this.imagePreview = URL.createObjectURL(file);
                this.isProcessingOcr = true;
                this.ocrProcessed = false;

                // Send to OCR API endpoint
                const formData = new FormData();
                formData.append('photo', file);

                try {
                    const response = await fetch('{{ route('ocr.upload') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (res.success && res.data) {
                        this.dateInput = res.data.date;
                        this.timeInput = res.data.time;
                        this.ocrConfidence = res.data.confidence;
                        this.ocrConfidencePercent = res.data.confidence_percent;
                        this.ocrRawText = res.data.raw_text;
                        this.ocrProcessed = true;
                    } else {
                        alert('Aviso: Não foi possível identificar data/hora automaticamente. Por favor, insira manualmente.');
                    }
                } catch (e) {
                    console.error('OCR Error:', e);
                } finally {
                    this.isProcessingOcr = false;
                }
            }
        }
    }
</script>
@endpush
