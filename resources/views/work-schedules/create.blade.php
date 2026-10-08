@extends('layouts.app')

@section('title', 'Cadastrar Jornada')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="workScheduleApp()">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('work-schedules.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center space-x-1">
            <span>←</span>
            <span>Voltar para jornadas</span>
        </a>
        <h1 class="text-lg font-bold text-slate-900">Cadastrar Nova Jornada</h1>
        <div class="w-12"></div>
    </div>

    <!-- Presets & Quick Template Bar -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center space-x-1.5">
                <span>⚡</span>
                <span>Modelos Rápidos de Jornada</span>
            </span>
            <span class="text-[11px] text-blue-600 font-medium">Clique para preencher e ajuste manualmente se desejar</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button type="button" @click="applyPreset('comercial_44h')"
                    class="p-2.5 bg-white hover:bg-blue-600 hover:text-white border border-blue-200 text-blue-900 rounded-xl text-left transition shadow-sm group">
                <span class="font-bold text-xs block group-hover:text-white">💼 Comercial 44h</span>
                <span class="text-[10px] text-slate-500 group-hover:text-blue-100 block mt-0.5">Seg-Qui 9h, Sex 8h</span>
            </button>

            <button type="button" @click="applyPreset('padrao_40h')"
                    class="p-2.5 bg-white hover:bg-blue-600 hover:text-white border border-blue-200 text-blue-900 rounded-xl text-left transition shadow-sm group">
                <span class="font-bold text-xs block group-hover:text-white">🏢 Padrão 40h</span>
                <span class="text-[10px] text-slate-500 group-hover:text-blue-100 block mt-0.5">Seg-Sex 8h/dia</span>
            </button>

            <button type="button" @click="applyPreset('estagio_30h')"
                    class="p-2.5 bg-white hover:bg-blue-600 hover:text-white border border-blue-200 text-blue-900 rounded-xl text-left transition shadow-sm group">
                <span class="font-bold text-xs block group-hover:text-white">⏱️ Estágio 30h</span>
                <span class="text-[10px] text-slate-500 group-hover:text-blue-100 block mt-0.5">Seg-Sex 6h direto</span>
            </button>

            <button type="button" @click="applyPreset('plantao_12x36')"
                    class="p-2.5 bg-white hover:bg-blue-600 hover:text-white border border-blue-200 text-blue-900 rounded-xl text-left transition shadow-sm group">
                <span class="font-bold text-xs block group-hover:text-white">🏥 Plantão 12h</span>
                <span class="text-[10px] text-slate-500 group-hover:text-blue-100 block mt-0.5">07:00 às 19:00</span>
            </button>
        </div>
    </div>

    <!-- Quick Replication Toolbar (Repetir para outros dias) -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-2.5">
        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
            🔁 Ferramenta de Repetição Rápida
        </span>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="replicateMondayToWeekdays()"
                    class="px-3.5 py-2 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 active:scale-95">
                <span>📋</span>
                <span>Copiar Segunda p/ Ter-Sex</span>
            </button>

            <button type="button" @click="replicateSourceToAll(1)"
                    class="px-3.5 py-2 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 active:scale-95">
                <span>🔄</span>
                <span>Copiar Segunda p/ Todos os Dias (Seg-Dom)</span>
            </button>

            <button type="button" @click="clearAllDays()"
                    class="px-3 py-2 bg-slate-50 hover:bg-rose-50 hover:text-rose-700 border border-slate-200 text-slate-500 font-semibold text-xs rounded-xl transition ml-auto">
                Limpar Todos
            </button>
        </div>
    </div>

    <!-- Main Schedule Form -->
    <form action="{{ route('work-schedules.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Schedule Name & Active Toggle -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 space-y-4">
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nome da Jornada</label>
                <input type="text" name="name" id="name" x-model="scheduleName" required
                       placeholder="Ex: Jornada Padrão 44h, Horário de Verão, etc."
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 text-sm font-semibold text-slate-800">
            </div>

            <div class="flex items-center space-x-2 pt-2 border-t border-slate-100">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-xs font-medium text-slate-700">Definir como jornada ativa principal do usuário</label>
            </div>
        </div>

        <!-- Weekly Hours Summary Box (Real-time Calculation) -->
        <div class="bg-slate-900 text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-md">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Cálculo Semanal Previsto</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400" x-text="calculateTotalWeeklyHours()"></span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-300 block font-medium" x-text="countWorkdays() + ' dias de trabalho'"></span >
                <span class="text-[11px] text-slate-500 block" x-text="(7 - countWorkdays()) + ' dias de folga'"></span>
            </div>
        </div>

        <!-- Day by Day Configuration (Segunda a Domingo) -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase text-slate-500 tracking-wider px-1">
                📅 Configuração Diária (Segunda a Domingo)
            </h2>

            <!-- We iterate through display order: Seg(1), Ter(2), Qua(3), Qui(4), Sex(5), Sab(6), Dom(0) -->
            <template x-for="dayIndex in [1, 2, 3, 4, 5, 6, 0]" :key="dayIndex">
                <div class="p-4 sm:p-5 rounded-2xl border transition-all duration-150"
                     :class="days[dayIndex].is_workday ? 'bg-white border-slate-200 shadow-sm' : 'bg-slate-50/80 border-slate-200/60 opacity-85'">
                    
                    <!-- Hidden input for form submission -->
                    <input type="hidden" :name="'days[' + dayIndex + '][is_workday]'" :value="days[dayIndex].is_workday ? 1 : 0">

                    <!-- Day Header: Title, Toggle & Computed Day Hours -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <!-- Toggle switch -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="days[dayIndex].is_workday" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                            
                            <div>
                                <h3 class="text-sm font-bold text-slate-900" x-text="getDayName(dayIndex)"></h3>
                                <span class="text-[10px] font-semibold"
                                      :class="days[dayIndex].is_workday ? 'text-emerald-600' : 'text-slate-400'"
                                      x-text="days[dayIndex].is_workday ? 'Dia de Trabalho' : 'Descanso / Folga'"></span>
                            </div>
                        </div>

                        <!-- Computed Day Total & Copy Button -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-black px-2.5 py-1 rounded-lg"
                                  :class="days[dayIndex].is_workday ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-slate-100 text-slate-400'"
                                  x-text="calculateDayHours(dayIndex)"></span>

                            <button type="button" x-show="days[dayIndex].is_workday"
                                    @click="replicateToNext(dayIndex)"
                                    class="text-[11px] text-slate-500 hover:text-blue-600 font-semibold p-1 hover:bg-slate-100 rounded transition"
                                    title="Copiar este horário para o dia seguinte">
                                ↳ Copiar p/ seguinte
                            </button>
                        </div>
                    </div>

                    <!-- Day Time Fields (Visible when is_workday is true) -->
                    <div x-show="days[dayIndex].is_workday" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-3.5" x-transition>
                        <!-- 1. Entrada -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Entrada</label>
                            <input type="text" :name="'days[' + dayIndex + '][entry]'" x-model="days[dayIndex].entry"
                                   placeholder="08:00" maxlength="5"
                                   class="w-full px-3 py-2 text-xs font-bold text-slate-800 rounded-xl border border-slate-200 text-center focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- 2. Saída Almoço -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Saída Almoço</label>
                            <input type="text" :name="'days[' + dayIndex + '][lunch_start]'" x-model="days[dayIndex].lunch_start"
                                   placeholder="12:00" maxlength="5"
                                   class="w-full px-3 py-2 text-xs font-bold text-slate-800 rounded-xl border border-slate-200 text-center focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- 3. Retorno Almoço -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Retorno Almoço</label>
                            <input type="text" :name="'days[' + dayIndex + '][lunch_end]'" x-model="days[dayIndex].lunch_end"
                                   placeholder="13:00" maxlength="5"
                                   class="w-full px-3 py-2 text-xs font-bold text-slate-800 rounded-xl border border-slate-200 text-center focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- 4. Saída -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Saída</label>
                            <input type="text" :name="'days[' + dayIndex + '][exit]'" x-model="days[dayIndex].exit"
                                   placeholder="18:00" maxlength="5"
                                   class="w-full px-3 py-2 text-xs font-bold text-slate-800 rounded-xl border border-slate-200 text-center focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition flex items-center justify-center space-x-2 text-base active:scale-98">
                <span>✓ Salvar Jornada Semanal</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function workScheduleApp() {
        return {
            scheduleName: 'Jornada Padrão 44h',
            days: {
                0: { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' }, // Domingo
                1: { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '18:00' }, // Segunda
                2: { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '18:00' }, // Terça
                3: { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '18:00' }, // Quarta
                4: { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '18:00' }, // Quinta
                5: { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '17:00' }, // Sexta (8h)
                6: { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' }, // Sábado
            },

            getDayName(index) {
                const names = {
                    0: 'Domingo',
                    1: 'Segunda-feira',
                    2: 'Terça-feira',
                    3: 'Quarta-feira',
                    4: 'Quinta-feira',
                    5: 'Sexta-feira',
                    6: 'Sábado',
                };
                return names[index] || '';
            },

            // --- Quick Replication Methods ---
            replicateMondayToWeekdays() {
                const mon = this.days[1];
                [2, 3, 4, 5].forEach(dayIdx => {
                    this.days[dayIdx].is_workday = mon.is_workday;
                    this.days[dayIdx].entry = mon.entry;
                    this.days[dayIdx].lunch_start = mon.lunch_start;
                    this.days[dayIdx].lunch_end = mon.lunch_end;
                    this.days[dayIdx].exit = mon.exit;
                });
            },

            replicateSourceToAll(sourceIdx) {
                const src = this.days[sourceIdx];
                [0, 1, 2, 3, 4, 5, 6].forEach(dayIdx => {
                    if (dayIdx !== sourceIdx) {
                        this.days[dayIdx].is_workday = src.is_workday;
                        this.days[dayIdx].entry = src.entry;
                        this.days[dayIdx].lunch_start = src.lunch_start;
                        this.days[dayIdx].lunch_end = src.lunch_end;
                        this.days[dayIdx].exit = src.exit;
                    }
                });
            },

            replicateToNext(currentIdx) {
                const nextOrder = { 1: 2, 2: 3, 3: 4, 4: 5, 5: 6, 6: 0, 0: 1 };
                const nextIdx = nextOrder[currentIdx];
                const src = this.days[currentIdx];

                this.days[nextIdx].is_workday = src.is_workday;
                this.days[nextIdx].entry = src.entry;
                this.days[nextIdx].lunch_start = src.lunch_start;
                this.days[nextIdx].lunch_end = src.lunch_end;
                this.days[nextIdx].exit = src.exit;
            },

            clearAllDays() {
                [0, 1, 2, 3, 4, 5, 6].forEach(i => {
                    this.days[i].is_workday = false;
                    this.days[i].entry = '';
                    this.days[i].lunch_start = '';
                    this.days[i].lunch_end = '';
                    this.days[i].exit = '';
                });
            },

            // --- Preset Templates ---
            applyPreset(type) {
                if (type === 'comercial_44h') {
                    this.scheduleName = 'Jornada Comercial 44h';
                    [1, 2, 3, 4].forEach(i => {
                        this.days[i] = { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '18:00' };
                    });
                    this.days[5] = { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '17:00' };
                    this.days[6] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                    this.days[0] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                } else if (type === 'padrao_40h') {
                    this.scheduleName = 'Jornada Padrão 40h';
                    [1, 2, 3, 4, 5].forEach(i => {
                        this.days[i] = { is_workday: true, entry: '08:00', lunch_start: '12:00', lunch_end: '13:00', exit: '17:00' };
                    });
                    this.days[6] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                    this.days[0] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                } else if (type === 'estagio_30h') {
                    this.scheduleName = 'Estágio / Meio Período 30h';
                    [1, 2, 3, 4, 5].forEach(i => {
                        this.days[i] = { is_workday: true, entry: '08:00', lunch_start: '', lunch_end: '', exit: '14:00' };
                    });
                    this.days[6] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                    this.days[0] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                } else if (type === 'plantao_12x36') {
                    this.scheduleName = 'Plantão 12h Diurno';
                    [1, 3, 5].forEach(i => {
                        this.days[i] = { is_workday: true, entry: '07:00', lunch_start: '12:00', lunch_end: '13:00', exit: '19:00' };
                    });
                    [0, 2, 4, 6].forEach(i => {
                        this.days[i] = { is_workday: false, entry: '', lunch_start: '', lunch_end: '', exit: '' };
                    });
                }
            },

            // --- Calculation Helpers ---
            timeToMin(t) {
                if (!t || !t.includes(':')) return null;
                const [h, m] = t.split(':').map(Number);
                return (h * 60) + m;
            },

            calculateDayMinutes(dayIndex) {
                const d = this.days[dayIndex];
                if (!d.is_workday) return 0;

                const entry = this.timeToMin(d.entry);
                const lStart = this.timeToMin(d.lunch_start);
                const lEnd = this.timeToMin(d.lunch_end);
                const exit = this.timeToMin(d.exit);

                if (entry === null || exit === null || exit <= entry) return 0;

                if (lStart !== null && lEnd !== null && lEnd > lStart) {
                    const morning = lStart - entry;
                    const afternoon = exit - lEnd;
                    return Math.max(0, morning + afternoon);
                }

                return Math.max(0, exit - entry);
            },

            calculateDayHours(dayIndex) {
                if (!this.days[dayIndex].is_workday) return 'Folga';
                const min = this.calculateDayMinutes(dayIndex);
                if (min === 0) return '0h 00m';
                const h = Math.floor(min / 60);
                const m = min % 60;
                return `${h}h ${String(m).padStart(2, '0')}m`;
            },

            calculateTotalWeeklyHours() {
                let totalMin = 0;
                [0, 1, 2, 3, 4, 5, 6].forEach(i => {
                    totalMin += this.calculateDayMinutes(i);
                });
                const h = Math.floor(totalMin / 60);
                const m = totalMin % 60;
                return `${h}h ${String(m).padStart(2, '0')}m`;
            },

            countWorkdays() {
                return Object.values(this.days).filter(d => d.is_workday).length;
            }
        }
    }
</script>
@endpush
