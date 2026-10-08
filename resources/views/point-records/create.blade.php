@extends('layouts.app')

@section('title', 'Registrar Ponto')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="pointRegistrationApp()" x-init="init()">

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
            <span>Câmera HTML5 / OCR</span>
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
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm text-center relative overflow-hidden">
            
            <!-- State 1: Camera not open & no picture taken -->
            <div x-show="!isCameraActive && !imagePreview" class="py-6 space-y-4">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto text-3xl shadow-inner">
                    📷
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Câmera Nativa HTML5</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                        Aponte para o comprovante ou espelho de ponto para ler data e hora via OCR
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-2 pt-2">
                    <!-- Botão 1: Abrir Câmera ao Vivo (HTML5 getUserMedia) -->
                    <button type="button" @click="startLiveCamera()"
                            class="w-full sm:w-auto px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 active:scale-98">
                        <span>📹</span>
                        <span>Abrir Câmera ao Vivo</span>
                    </button>

                    <!-- Botão 2: Upload / Câmera Nativa do Sistema (capture="environment") -->
                    <label class="w-full sm:w-auto px-5 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer flex items-center justify-center space-x-2 active:scale-98">
                        <span>📁</span>
                        <span>Carregar da Galeria</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handleFileUpload($event)">
                    </label>
                </div>
            </div>

            <!-- State 2: Live HTML5 Camera Viewfinder Stream -->
            <div x-show="isCameraActive" class="space-y-3 relative" x-cloak>
                <div class="relative bg-black rounded-2xl overflow-hidden shadow-inner border border-slate-800">
                    <video id="cameraVideo" autoplay playsinline muted class="w-full h-72 sm:h-80 object-cover"></video>
                    
                    <!-- Viewfinder Overlay (Guia de Enquadramento do Ponto) -->
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-6">
                        <div class="w-full h-44 border-2 border-dashed border-emerald-400/80 rounded-xl bg-emerald-500/10 flex items-center justify-center relative">
                            <span class="text-[11px] font-bold text-emerald-200 bg-black/60 px-3 py-1 rounded-full backdrop-blur-sm">
                                Enquadre a data e horário aqui
                            </span>
                            <!-- Cantoneiras visuais -->
                            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-emerald-400"></div>
                            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-emerald-400"></div>
                            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-emerald-400"></div>
                            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-emerald-400"></div>
                        </div>
                    </div>

                    <!-- Camera Control Top Bar -->
                    <div class="absolute top-3 right-3 flex items-center space-x-2">
                        <!-- Switch Camera (Front/Rear) -->
                        <button type="button" @click="toggleCameraFacing()"
                                class="p-2 bg-black/60 hover:bg-black/80 text-white rounded-full transition text-xs" title="Trocar Câmera">
                            🔄
                        </button>
                        <!-- Close Camera -->
                        <button type="button" @click="stopLiveCamera()"
                                class="p-2 bg-black/60 hover:bg-black/80 text-white rounded-full transition text-xs" title="Fechar Câmera">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Snapshot Capture Trigger Button -->
                <div class="pt-2 flex items-center justify-center space-x-3">
                    <button type="button" @click="captureLiveSnapshot()"
                            class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-2xl shadow-lg transition flex items-center space-x-2 active:scale-95">
                        <span class="w-4 h-4 rounded-full bg-white animate-pulse"></span>
                        <span>FOTOGRAFAR & PROCESSAR OCR</span>
                    </button>
                </div>
            </div>

            <!-- State 3: Captured Image Preview & OCR Analysis -->
            <div x-show="imagePreview && !isCameraActive" class="space-y-4" x-cloak>
                <div class="relative rounded-2xl overflow-hidden max-h-72 border border-slate-200 bg-slate-950">
                    <img :src="imagePreview" class="w-full h-auto object-contain mx-auto max-h-72">
                    <button type="button" @click="resetImage()"
                            class="absolute top-3 right-3 bg-black/70 text-white px-3 py-1.5 rounded-full hover:bg-black/90 transition text-xs font-semibold">
                        ✕ Tirar Outra Foto
                    </button>
                </div>

                <!-- Processing Spinner -->
                <div x-show="isProcessingOcr" class="py-4 flex items-center justify-center space-x-2 text-blue-600">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-xs font-bold">Processando imagem com OCR em PHP...</span>
                </div>

                <!-- OCR Result Status Box -->
                <div x-show="ocrProcessed && !isProcessingOcr"
                     class="p-4 rounded-xl border text-left transition"
                     :class="{
                         'bg-emerald-50 border-emerald-200 text-emerald-900': ocrConfidence >= 0.90,
                         'bg-amber-50 border-amber-200 text-amber-900': ocrConfidence >= 0.70 && ocrConfidence < 0.90,
                         'bg-rose-50 border-rose-200 text-rose-900': ocrConfidence < 0.70
                     }">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span x-text="ocrConfidence >= 0.90 ? '✓ Reconhecido com Alta Confiança' : (ocrConfidence >= 0.70 ? '⚠ Confiança Média — Verifique os campos abaixo' : '⚠ Baixa Confiança — Ajuste manualmente')"></span>
                        <span class="px-2.5 py-1 rounded-full bg-white shadow-sm" x-text="ocrConfidencePercent + '% Confiança'"></span>
                    </div>
                    <p class="text-[11px] mt-1.5 opacity-80" x-show="ocrRawText">
                        Texto detectado: "<strong x-text="ocrRawText"></strong>"
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Registration Form (Shared for Camera/OCR & Manual) -->
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
                           :class="selectedType === '{{ $val }}' ? 'border-blue-600 bg-blue-50/60 text-blue-900 ring-1 ring-blue-600' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
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
            <textarea name="notes" id="notes" rows="2" placeholder="Notas sobre a marcação ou justificativa..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs text-slate-800"></textarea>
        </div>

        <!-- Submit CTA Button -->
        <div class="pt-4">
            <button type="submit"
                    class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center space-x-2 text-base active:scale-98">
                <span>✓ Salvar Registro de Ponto</span>
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
            isCameraActive: false,
            cameraFacing: 'environment', // Traseira por padrão no celular
            mediaStream: null,
            isProcessingOcr: false,
            ocrProcessed: false,
            ocrConfidence: null,
            ocrConfidencePercent: 0,
            ocrRawText: '',

            init() {
                // Se foi solicitado modo OCR direto na URL, abre a câmera se compatível
                if (this.mode === 'ocr') {
                    // Pronto para uso
                }
            },

            setMode(newMode) {
                this.mode = newMode;
                if (newMode === 'manual') {
                    this.stopLiveCamera();
                }
            },

            // --- HTML5 Native Camera (getUserMedia) ---
            async startLiveCamera() {
                this.resetImage();

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Seu navegador não suporta acesso à câmera via HTML5. Use o botão Carregar da Galeria.');
                    return;
                }

                try {
                    this.isCameraActive = true;
                    
                    const constraints = {
                        video: {
                            facingMode: this.cameraFacing,
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        },
                        audio: false
                    };

                    this.mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
                    
                    this.$nextTick(() => {
                        const video = document.getElementById('cameraVideo');
                        if (video) {
                            video.srcObject = this.mediaStream;
                            video.play();
                        }
                    });
                } catch (err) {
                    console.warn('Erro ao abrir câmera traseira, tentando fallback:', err);
                    // Fallback para qualquer câmera disponível
                    try {
                        this.mediaStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                        const video = document.getElementById('cameraVideo');
                        if (video) {
                            video.srcObject = this.mediaStream;
                            video.play();
                        }
                    } catch (fallbackErr) {
                        alert('Permissão de câmera negada ou dispositivo indisponível. Você pode selecionar uma foto da galeria.');
                        this.isCameraActive = false;
                    }
                }
            },

            stopLiveCamera() {
                if (this.mediaStream) {
                    this.mediaStream.getTracks().forEach(track => track.stop());
                    this.mediaStream = null;
                }
                this.isCameraActive = false;
            },

            toggleCameraFacing() {
                this.stopLiveCamera();
                this.cameraFacing = this.cameraFacing === 'environment' ? 'user' : 'environment';
                this.startLiveCamera();
            },

            captureLiveSnapshot() {
                const video = document.getElementById('cameraVideo');
                if (!video || !this.isCameraActive) return;

                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || 1280;
                canvas.height = video.videoHeight || 720;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                this.stopLiveCamera();

                // Converte canvas para Blob JPEG
                canvas.toBlob((blob) => {
                    if (!blob) return;

                    const file = new File([blob], `ponto_camera_${Date.now()}.jpg`, { type: 'image/jpeg' });
                    this.processCapturedFile(file);
                }, 'image/jpeg', 0.85);
            },

            handleFileUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.stopLiveCamera();
                this.processCapturedFile(file);
            },

            processCapturedFile(file) {
                // Preenche input do formulário
                const formInput = document.getElementById('formPhotoInput');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                formInput.files = dataTransfer.files;

                // Preview local
                this.imagePreview = URL.createObjectURL(file);
                this.runOcr(file);
            },

            resetImage() {
                this.stopLiveCamera();
                this.imagePreview = null;
                this.ocrProcessed = false;
                this.ocrConfidence = null;
                this.ocrRawText = '';
                const formInput = document.getElementById('formPhotoInput');
                if (formInput) formInput.value = '';
            },

            async runOcr(file) {
                this.isProcessingOcr = true;
                this.ocrProcessed = false;

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
                        alert('Aviso: Não foi possível identificar data/hora automaticamente. Por favor, insira os dados nos campos.');
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
