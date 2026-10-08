<?php

namespace App\Services;

use App\Models\PointRecord;
use App\Models\User;
use App\Models\WorkDay;
use Carbon\Carbon;

class WorkDayService
{
    /**
     * Busca ou cria um WorkDay para o usuário na data especificada.
     *
     * @param string $date formato Y-m-d ou d/m/Y
     */
    public function getOrCreateWorkDay(User $user, string $date): WorkDay
    {
        $dateYmd = Carbon::parse($date)->format('Y-m-d');

        $workDay = WorkDay::where('user_id', $user->id)
            ->whereDate('date', $dateYmd)
            ->first();

        if ($workDay) {
            return $workDay;
        }

        // Busca a jornada ativa do usuário
        $workSchedule = $user->activeWorkSchedule();
        $expectedMinutes = 480; // padrão 8h

        if ($workSchedule) {
            $dayOfWeek = Carbon::parse($dateYmd)->dayOfWeek;
            $scheduleDay = $workSchedule->days()->where('day_of_week', $dayOfWeek)->first();

            if ($scheduleDay && $scheduleDay->is_workday) {
                $expectedMinutes = $scheduleDay->expected_minutes ?? 480;
            }
        }

        return WorkDay::firstOrCreate(
            [
                'user_id' => $user->id,
                'date' => $dateYmd,
            ],
            [
                'work_schedule_id' => $workSchedule?->id,
                'status' => 'incomplete',
                'expected_minutes' => $expectedMinutes,
            ]
        );
    }

    /**
     * Calcula os minutos trabalhados baseado nas marcações do dia.
     * Retorna null se a jornada estiver incompleta (sem entrada e saída).
     */
    public function calculateWorkedMinutes(WorkDay $workDay): ?int
    {
        $records = $workDay->pointRecords->keyBy('type');

        $entry = $records->get(PointRecord::TYPE_ENTRY);
        $exit = $records->get(PointRecord::TYPE_EXIT);

        if (!$entry || !$exit) {
            return null;
        }

        $lunchStart = $records->get(PointRecord::TYPE_LUNCH_START);
        $lunchEnd = $records->get(PointRecord::TYPE_LUNCH_END);

        $totalMinutes = 0;

        if ($lunchStart && $lunchEnd) {
            // Período manhã: entrada → saída almoço
            $morning = abs($lunchStart->recorded_at->diffInMinutes($entry->recorded_at));
            // Período tarde: retorno almoço → saída
            $afternoon = abs($exit->recorded_at->diffInMinutes($lunchEnd->recorded_at));
            $totalMinutes = $morning + $afternoon;
        } else {
            // Sem almoço: entrada → saída
            $totalMinutes = abs($exit->recorded_at->diffInMinutes($entry->recorded_at));
        }

        return $totalMinutes;
    }

    /**
     * Calcula o saldo de minutos do dia.
     * Positivo = horas extras, Negativo = horas faltantes.
     */
    public function calculateBalance(WorkDay $workDay): ?int
    {
        $worked = $this->calculateWorkedMinutes($workDay);

        if ($worked === null) {
            return null;
        }

        return $worked - $workDay->expected_minutes;
    }

    /**
     * Atualiza as estatísticas do WorkDay (worked_minutes, balance_minutes, status).
     */
    public function updateWorkDayStats(WorkDay $workDay): void
    {
        $workDay->load('pointRecords');

        $records = $workDay->pointRecords->keyBy('type');
        $workedMinutes = $this->calculateWorkedMinutes($workDay);
        $balanceMinutes = $this->calculateBalance($workDay);

        $hasEntry = $records->has(PointRecord::TYPE_ENTRY);
        $hasExit = $records->has(PointRecord::TYPE_EXIT);

        $status = match (true) {
            $hasEntry && $hasExit => 'complete',
            default => 'incomplete',
        };

        $workDay->update([
            'worked_minutes' => $workedMinutes,
            'balance_minutes' => $balanceMinutes,
            'status' => $status,
        ]);
    }
}
