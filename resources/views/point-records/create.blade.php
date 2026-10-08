@extends('layouts.app')

@section('title', 'Registrar Ponto')

@section('content')
<div class="max-w-xl mx-auto space-y-5" x-data="pointRegistrationApp()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center space-x-1">
            <span>←</span>
            <span>Voltar ao painel</span>
        </a>
        <h1 class="text-lg font-bold text-slate-900">Registrar Marcação</h1>
        <div class="w-16"></div>
    </div>

    {{-- ═══════════════════════════════════════════════
         BLOCO 1 — CALENDÁRIO DE SELEÇÃO DE DATA
    ══════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Cabeçalho do calendário --}}
        <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
            <button type="button" @click="prevMonth()" class="p-1.5 hover:bg-white/20 rounded-lg transition text-sm font-bold">‹</button>
            <div class="text-center">
                <p class="text-sm font-extrabold tracking-wide" x-text="monthLabel"></p>
                <p class="text-[11px] text-blue-200 font-medium" x-text="selectedDateLabel"></p>
            </div>
            <button type="button" @click="nextMonth()" class="p-1.5 hover:bg-white/20 rounded-lg transition text-sm font-bold">›</button>
        </div>

        {{-- Nomes dos dias da semana: Dom (0) a Sáb (6) --}}
        <div class="grid grid-cols-7 text-center border-b border-slate-100 bg-slate-50">
            <template x-for="d in ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb']" :key="d">
                <div class="py-2 text-[11px] font-bold uppercase"
                     :class="d === 'Dom' || d === 'Sáb' ? 'text-rose-400' : (d === 'Qui' ? 'text-blue-700 font-extrabold' : 'text-slate-500')"
                     x-text="d"></div>
            </template>
        </div>

        {{-- Grid de dias (alinhado rigorosamente pelo dia da semana) --}}
        <div class="grid grid-cols-7 p-2 gap-1">
            <template x-for="cell in calendarCells" :key="cell.key">
                <div class="h-9 w-full flex items-center justify-center">
                    <button
                        x-show="cell.type === 'day'"
                        type="button"
                        @click="selectDay(cell.day)"
                        :disabled="cell.type === 'day' && isFutureDay(cell.day)"
                        :class="{
                            'bg-blue-600 text-white font-black shadow-md scale-105': cell.type === 'day' && isSelectedDay(cell.day),
                            'bg-blue-50 text-blue-700 font-bold ring-1 ring-blue-300': cell.type === 'day' && isTodayDay(cell.day) && !isSelectedDay(cell.day),
                            'text-slate-300 cursor-not-allowed': cell.type === 'day' && isFutureDay(cell.day),
                            'hover:bg-slate-100 text-slate-700 font-semibold': cell.type === 'day' && !isFutureDay(cell.day) && !isSelectedDay(cell.day),
                            'text-rose-500': cell.type === 'day' && isWeekend(cell.day) && !isSelectedDay(cell.day) && !isFutureDay(cell.day),
                        }"
                        class="h-9 w-full rounded-xl text-xs transition-all duration-100 flex items-center justify-center"
                        x-text="cell.day || ''">
                    </button>
                </div>
            </template>
        </div>

        {{-- Rodapé: atalhos rápidos --}}
        <div class="flex items-center gap-2 px-4 py-3 border-t border-slate-100 bg-slate-50/60">
            <span class="text-[11px] text-slate-500 font-semibold">Acesso rápido:</span>
            <button type="button" @click="goToday()" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-[11px] font-bold hover:bg-blue-200 transition">Hoje</button>
            <button type="button" @click="goYesterday()" class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-[11px] font-semibold hover:bg-slate-200 transition">Ontem</button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         BLOCO 2 — MODO (Câmera/OCR ↔ Manual)
    ══════════════════════════════════════════════════ --}}
    <div class="flex p-1 bg-slate-200/70 rounded-2xl">
        <button type="button" @click="setMode('ocr')"
                :class="mode === 'ocr' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 font-medium'"
                class="flex-1 py-2.5 text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
            <span>📷</span>
            <span>Câmera / Foto</span>
        </button>
        <button type="button" @click="setMode('manual')"
                :class="mode === 'manual' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 font-medium'"
                class="flex-1 py-2.5 text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
            <span>✏️</span>
            <span>Manual</span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════
         BLOCO 3 — CÂMERA / OCR
    ══════════════════════════════════════════════════ --}}
    <div x-show="mode === 'ocr'" class="space-y-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-center relative overflow-hidden">

            {{-- Estado 1: sem foto --}}
            <div x-show="!isCameraActive && !imagePreview" class="py-4 space-y-4">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner">📷</div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Fotografe o Espelho de Ponto</h3>
                    <p class="text-xs text-slate-500 mt-0.5">O OCR vai extrair data e hora automaticamente</p>
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2 pt-1">
                    <button type="button" @click="startLiveCamera()"
                            class="w-full sm:w-auto px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center space-x-2 active:scale-95">
                        <span>📹</span><span>Abrir Câmera ao Vivo</span>
                    </button>
                    <label class="w-full sm:w-auto px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl cursor-pointer flex items-center justify-center space-x-2 active:scale-95 transition">
                        <span>📁</span><span>Carregar da Galeria</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handleFileUpload($event)">
                    </label>
                </div>
            </div>

            {{-- Estado 2: câmera ao vivo --}}
            <div x-show="isCameraActive" class="space-y-3" x-cloak>
                <div class="relative bg-black rounded-2xl overflow-hidden border border-slate-800">
                    <video id="cameraVideo" autoplay playsinline muted class="w-full h-64 object-cover"></video>
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-6">
                        <div class="w-full h-40 border-2 border-dashed border-emerald-400/80 rounded-xl bg-emerald-500/10 flex items-center justify-center relative">
                            <span class="text-[10px] font-bold text-emerald-200 bg-black/60 px-3 py-1 rounded-full">Enquadre a data e horário aqui</span>
                            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-emerald-400"></div>
                            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-emerald-400"></div>
                            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-emerald-400"></div>
                            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-emerald-400"></div>
                        </div>
                    </div>
                    <div class="absolute top-3 right-3 flex space-x-2">
                        <button type="button" @click="toggleCameraFacing()" class="p-2 bg-black/60 hover:bg-black/80 text-white rounded-full text-xs">🔄</button>
                        <button type="button" @click="stopLiveCamera()" class="p-2 bg-black/60 hover:bg-black/80 text-white rounded-full text-xs">✕</button>
                    </div>
                </div>
                <button type="button" @click="captureLiveSnapshot()"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-2xl shadow-lg flex items-center justify-center space-x-2 active:scale-95">
                    <span class="w-3 h-3 rounded-full bg-white animate-pulse"></span>
                    <span>TIRAR FOTO DO PONTO</span>
                </button>
            </div>

            {{-- Estado 3: foto capturada + botão OCR --}}
            <div x-show="imagePreview && !isCameraActive" class="space-y-4" x-cloak>
                <div class="relative rounded-2xl overflow-hidden max-h-64 border border-slate-200 bg-slate-950">
                    <img :src="imagePreview" class="w-full h-auto object-contain mx-auto max-h-64">
                    <button type="button" @click="resetImage()"
                            class="absolute top-3 right-3 bg-black/70 hover:bg-black/90 text-white px-3 py-1.5 rounded-full text-xs font-semibold">
                        ✕ Outra Foto
                    </button>
                </div>

                {{-- Botão OCR principal --}}
                <button type="button" @click="executeOcrExtraction()"
                        :disabled="isProcessingOcr"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 text-white font-black text-sm rounded-2xl shadow flex items-center justify-center space-x-2 active:scale-95 transition">
                    <span x-show="!isProcessingOcr">🔍</span>
                    <svg x-show="isProcessingOcr" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isProcessingOcr ? 'Processando imagem...' : '🔍 Ler OCR — Extrair Data e Hora'"></span>
                </button>

                {{-- Card de resultado OCR --}}
                <div x-show="ocrProcessed && !isProcessingOcr"
                     class="p-4 rounded-xl border text-left animate-pulse-once"
                     :class="{
                         'bg-emerald-50 border-emerald-200': ocrConfidence >= 0.90,
                         'bg-amber-50 border-amber-200': ocrConfidence >= 0.70 && ocrConfidence < 0.90,
                         'bg-rose-50 border-rose-200': ocrConfidence < 0.70
                     }">
                    <div class="flex items-center justify-between text-xs font-bold"
                         :class="{
                             'text-emerald-900': ocrConfidence >= 0.90,
                             'text-amber-900': ocrConfidence >= 0.70 && ocrConfidence < 0.90,
                             'text-rose-900': ocrConfidence < 0.70
                         }">
                        <span class="flex items-center space-x-1.5">
                            <span x-text="ocrConfidence >= 0.90 ? '✓' : '⚠'"></span>
                            <span x-text="ocrConfidence >= 0.90 ? 'Data e hora extraídas com sucesso!' : (ocrConfidence >= 0.70 ? 'Extraído — Confirme os campos abaixo' : 'Baixa confiança — Verifique manualmente')"></span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-white shadow-sm font-black text-slate-700" x-text="ocrConfidencePercent + '% conf.'"></span>
                    </div>

                    {{-- Preview dos dados extraídos --}}
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 bg-white rounded-lg px-3 py-2 text-center border border-slate-200 shadow-sm">
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Data</p>
                            <p class="text-base font-black text-blue-700" x-text="dateInput"></p>
                        </div>
                        <div class="flex-1 bg-white rounded-lg px-3 py-2 text-center border border-slate-200 shadow-sm">
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Horário</p>
                            <p class="text-base font-black text-blue-700" x-text="timeInput"></p>
                        </div>
                    </div>

                    <p class="text-[11px] mt-2.5 text-slate-500" x-show="ocrRawText">
                        Texto bruto detectado: "<strong class="text-slate-700" x-text="ocrRawText"></strong>"
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         BLOCO 4 — FORMULÁRIO
    ══════════════════════════════════════════════════ --}}
    <form action="{{ route('point-records.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
        @csrf

        <input type="hidden" name="source" :value="mode === 'ocr' ? 'ocr' : 'manual'">
        <input type="hidden" name="ocr_confidence" :value="ocrConfidence">
        <input type="hidden" name="ocr_raw_text" :value="ocrRawText">
        <input type="file" name="photo" id="formPhotoInput" class="hidden">

        {{-- Tipo de Marcação --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tipo de Marcação</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($typeOptions as $val => $label)
                    <label class="flex items-center p-3 rounded-xl border cursor-pointer transition text-xs font-semibold"
                           :class="selectedType === '{{ $val }}' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-1 ring-blue-600' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                        <input type="radio" name="type" value="{{ $val }}" x-model="selectedType" class="hidden">
                        <span class="mr-2">
                            @if($val === 'entry') 🟢 @elseif($val === 'lunch_start') 🟠 @elseif($val === 'lunch_end') 🔵 @else 🔴 @endif
                        </span>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Data e Horário (preenchidos pelo calendário e/ou OCR) --}}
        <div class="grid grid-cols-2 gap-3 pt-1">
            <div>
                <label for="date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                    <span>Data (DD/MM/AAAA)</span>
                    <span x-show="ocrProcessed" class="text-[10px] text-indigo-600 font-bold">✓ OCR</span>
                    <span x-show="!ocrProcessed" class="text-[10px] text-blue-600 font-bold">📅 Calendário</span>
                </label>
                <input type="text" name="date" id="date" x-model="dateInput" required
                       placeholder="DD/MM/AAAA"
                       class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-bold text-slate-800 transition"
                       :class="ocrProcessed ? 'border-indigo-300 bg-indigo-50/30' : 'border-blue-300 bg-blue-50/20'">
            </div>
            <div>
                <label for="time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                    <span>Horário (HH:MM)</span>
                    <span x-show="ocrProcessed" class="text-[10px] text-indigo-600 font-bold">✓ OCR</span>
                </label>
                <input type="text" name="time" id="time" x-model="timeInput" required
                       placeholder="HH:MM" maxlength="5"
                       class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-bold text-slate-800 transition"
                       :class="ocrProcessed ? 'border-indigo-300 bg-indigo-50/30' : 'border-slate-200'">
            </div>
        </div>

        {{-- Observações --}}
        <div class="pt-1">
            <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Observações (opcional)</label>
            <textarea name="notes" id="notes" rows="2" placeholder="Notas sobre a marcação ou justificativa..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs text-slate-800"></textarea>
        </div>

        {{-- Submit --}}
        <div class="pt-3">
            <button type="submit"
                    class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-center space-x-2 text-base active:scale-98">
                <span>✓ Salvar Marcação de Ponto</span>
            </button>
            <p class="text-center text-[11px] text-slate-500 mt-2">
                Registrando para: <strong class="text-slate-700" x-text="selectedDateLabel"></strong>
            </p>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function pointRegistrationApp() {
    // Constantes de data/hora vindas do PHP (timezone America/Sao_Paulo garantido)
    const SERVER_DATE_BR   = '{{ $todayDate }}';
    const SERVER_TIME      = '{{ $currentTime }}';
    const TODAY_YEAR       = {{ $todayYear }};
    const TODAY_MONTH_IDX  = {{ $todayMonth - 1 }}; // 0-indexed para JS
    const TODAY_DAY        = {{ $todayDay }};
    const YESTERDAY_YEAR   = {{ $yesterdayYear }};
    const YESTERDAY_M_IDX  = {{ $yesterdayMonth - 1 }};
    const YESTERDAY_DAY    = {{ $yesterdayDay }};

    // Nomes dos dias da semana e meses em pt-BR
    const WEEKDAYS_PT = [
        'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'
    ];
    const MONTHS_PT = [
        'Janeiro','Fevereiro','Março','Abril','Maio','Junho',
        'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'
    ];

    return {
        // ── Modo ──
        mode: '{{ $mode }}',
        selectedType: '{{ $defaultType }}',

        // ── Campos do formulário ──
        dateInput: SERVER_DATE_BR,
        timeInput: SERVER_TIME,

        // ── Câmera ──
        imagePreview: null,
        currentFile: null,
        isCameraActive: false,
        cameraFacing: 'environment',
        mediaStream: null,

        // ── OCR ──
        isProcessingOcr: false,
        ocrProcessed: false,
        ocrConfidence: null,
        ocrConfidencePercent: 0,
        ocrRawText: '',

        // ── Calendário ──
        calendarYear: TODAY_YEAR,
        calendarMonth: TODAY_MONTH_IDX,   // 0-indexed
        selectedYear: TODAY_YEAR,
        selectedMonth: TODAY_MONTH_IDX,
        selectedDay: TODAY_DAY,

        // ──────────────────────────────────────────
        init() {
            this.calendarYear  = TODAY_YEAR;
            this.calendarMonth = TODAY_MONTH_IDX;
            this.selectedYear  = TODAY_YEAR;
            this.selectedMonth = TODAY_MONTH_IDX;
            this.selectedDay   = TODAY_DAY;
        },

        // ──────── CALENDÁRIO ────────────────────

        get monthLabel() {
            return MONTHS_PT[this.calendarMonth] + ' ' + this.calendarYear;
        },

        get daysInMonth() {
            return new Date(this.calendarYear, this.calendarMonth + 1, 0).getDate();
        },

        /**
         * Gera matriz plana de células alinhadas com as 7 colunas (Dom a Sáb).
         * Garante que Quinta-feira (08/10/2026) fique exatamente na coluna "Qui".
         */
        get calendarCells() {
            const cells = [];
            // getDay() retorna 0 (Dom) a 6 (Sáb). Para 01/10/2026 = 4 (Quinta-feira).
            const firstDow = new Date(this.calendarYear, this.calendarMonth, 1).getDay();
            const totalDays = new Date(this.calendarYear, this.calendarMonth + 1, 0).getDate();

            // Células vazias de padding antes do dia 1 com chave única garantida
            for (let i = 0; i < firstDow; i++) {
                cells.push({
                    type: 'empty',
                    key: `pad-${this.calendarYear}-${this.calendarMonth}-${i}`,
                    day: null
                });
            }

            // Dias válidos do mês
            for (let day = 1; day <= totalDays; day++) {
                cells.push({
                    type: 'day',
                    key: `day-${this.calendarYear}-${this.calendarMonth}-${day}`,
                    day: day
                });
            }

            return cells;
        },

        get selectedDateLabel() {
            const d = String(this.selectedDay).padStart(2, '0');
            const m = String(this.selectedMonth + 1).padStart(2, '0');
            const dow = new Date(this.selectedYear, this.selectedMonth, this.selectedDay).getDay();
            const dayName = WEEKDAYS_PT[dow];
            return `${dayName}, ${d}/${m}/${this.selectedYear}`;
        },

        isSelectedDay(day) {
            return day === this.selectedDay
                && this.calendarMonth === this.selectedMonth
                && this.calendarYear  === this.selectedYear;
        },

        isTodayDay(day) {
            return this.calendarYear  === TODAY_YEAR
                && this.calendarMonth === TODAY_MONTH_IDX
                && day === TODAY_DAY;
        },

        isFutureDay(day) {
            if (this.calendarYear > TODAY_YEAR) return true;
            if (this.calendarYear < TODAY_YEAR) return false;
            if (this.calendarMonth > TODAY_MONTH_IDX) return true;
            if (this.calendarMonth < TODAY_MONTH_IDX) return false;
            return day > TODAY_DAY;
        },

        isWeekend(day) {
            const dow = new Date(this.calendarYear, this.calendarMonth, day).getDay();
            return dow === 0 || dow === 6;
        },

        selectDay(day) {
            if (this.isFutureDay(day)) return;
            this.selectedYear  = this.calendarYear;
            this.selectedMonth = this.calendarMonth;
            this.selectedDay   = day;

            // Atualiza o campo data do formulário no padrão DD/MM/AAAA
            const d = String(day).padStart(2, '0');
            const m = String(this.calendarMonth + 1).padStart(2, '0');
            this.dateInput = `${d}/${m}/${this.calendarYear}`;

            if (this.ocrProcessed) this.ocrProcessed = false;
        },

        prevMonth() {
            if (this.calendarMonth === 0) {
                this.calendarMonth = 11;
                this.calendarYear--;
            } else {
                this.calendarMonth--;
            }
        },

        nextMonth() {
            const isCurrentMonth = this.calendarYear === TODAY_YEAR
                                && this.calendarMonth === TODAY_MONTH_IDX;
            if (isCurrentMonth) return; // Não navega para mês futuro
            if (this.calendarMonth === 11) {
                this.calendarMonth = 0;
                this.calendarYear++;
            } else {
                this.calendarMonth++;
            }
        },

        goToday() {
            this.calendarYear  = TODAY_YEAR;
            this.calendarMonth = TODAY_MONTH_IDX;
            this.selectDay(TODAY_DAY);
        },

        goYesterday() {
            this.calendarYear  = YESTERDAY_YEAR;
            this.calendarMonth = YESTERDAY_M_IDX;
            this.selectDay(YESTERDAY_DAY);
        },

        // ──────── MODO ──────────────────────────

        setMode(newMode) {
            this.mode = newMode;
            if (newMode === 'manual') this.stopLiveCamera();
        },

        // ──────── CÂMERA ────────────────────────

        async startLiveCamera() {
            this.resetImage();
            if (!navigator.mediaDevices?.getUserMedia) {
                alert('Câmera HTML5 não disponível. Use "Carregar da Galeria".');
                return;
            }
            try {
                this.isCameraActive = true;
                this.mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: this.cameraFacing, width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: false
                });
                this.$nextTick(() => {
                    const v = document.getElementById('cameraVideo');
                    if (v) { v.srcObject = this.mediaStream; v.play(); }
                });
            } catch (err) {
                try {
                    this.mediaStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    const v = document.getElementById('cameraVideo');
                    if (v) { v.srcObject = this.mediaStream; v.play(); }
                } catch {
                    alert('Permissão de câmera negada. Use "Carregar da Galeria".');
                    this.isCameraActive = false;
                }
            }
        },

        stopLiveCamera() {
            this.mediaStream?.getTracks().forEach(t => t.stop());
            this.mediaStream = null;
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
            canvas.width  = video.videoWidth  || 1280;
            canvas.height = video.videoHeight || 720;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            this.stopLiveCamera();
            canvas.toBlob(blob => {
                if (!blob) return;
                this.setCapturedFile(new File([blob], `ponto_${Date.now()}.jpg`, { type: 'image/jpeg' }));
            }, 'image/jpeg', 0.85);
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.stopLiveCamera();
            this.setCapturedFile(file);
        },

        setCapturedFile(file) {
            this.currentFile = file;
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('formPhotoInput').files = dt.files;
            this.imagePreview = URL.createObjectURL(file);
            this.ocrProcessed = false;
        },

        resetImage() {
            this.stopLiveCamera();
            this.imagePreview = null;
            this.currentFile  = null;
            this.ocrProcessed = false;
            this.ocrConfidence = null;
            this.ocrRawText   = '';
            const fi = document.getElementById('formPhotoInput');
            if (fi) fi.value = '';
        },

        // ──────── OCR ───────────────────────────

        async executeOcrExtraction() {
            if (!this.currentFile) {
                alert('Tire uma foto ou selecione uma imagem primeiro.');
                return;
            }

            this.isProcessingOcr = true;
            this.ocrProcessed    = false;

            const fd = new FormData();
            fd.append('photo', this.currentFile);

            try {
                const response = await fetch('{{ route('ocr.upload') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: fd
                });

                const res = await response.json();

                if (res.success && res.data) {
                    // Usa a data do OCR mas mantém a do calendário se OCR não trouxer
                    this.dateInput            = res.data.date || this.dateInput;
                    this.timeInput            = res.data.time || this.timeInput;
                    this.ocrConfidence        = res.data.confidence;
                    this.ocrConfidencePercent = res.data.confidence_percent;
                    this.ocrRawText           = res.data.raw_text;
                    this.ocrProcessed         = true;

                    // Sincroniza o calendário com a data extraída pelo OCR
                    this._syncCalendarFromDateInput();
                } else {
                    alert('Não foi possível identificar data/hora na foto. Preencha os campos manualmente.');
                }
            } catch (e) {
                console.error('OCR Error:', e);
                alert('Erro de comunicação com o serviço OCR. Preencha manualmente.');
            } finally {
                this.isProcessingOcr = false;
            }
        },

        /** Sincroniza o estado do calendário a partir de dateInput (DD/MM/AAAA) */
        _syncCalendarFromDateInput() {
            const parts = this.dateInput.split('/');
            if (parts.length !== 3) return;
            const day   = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1;
            const year  = parseInt(parts[2], 10);
            if (isNaN(day) || isNaN(month) || isNaN(year)) return;
            this.selectedDay   = day;
            this.selectedMonth = month;
            this.selectedYear  = year;
            this.calendarMonth = month;
            this.calendarYear  = year;
        }
    };
}
</script>
@endpush
