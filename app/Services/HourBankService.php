<?php

namespace App\Services;

use App\Models\HourBankTransaction;
use App\Models\User;
use App\Models\WorkDay;
use Carbon\Carbon;

class HourBankService
{
    /**
     * Retorna o saldo atual em minutos do usuário.
     */
    public function getBalance(User $user): int
    {
        $last = HourBankTransaction::forUser($user->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $last?->balance_minutes ?? 0;
    }

    /**
     * Retorna o saldo formatado em +/-HH:MM.
     */
    public function getFormattedBalance(User $user): string
    {
        return $this->minutesToFormatted($this->getBalance($user));
    }

    /**
     * Cria ou atualiza a transação diária do banco de horas.
     * Deve ser chamado quando o dia está completo.
     */
    public function syncDailyTransaction(WorkDay $workDay): HourBankTransaction
    {
        $existing = HourBankTransaction::where('work_day_id', $workDay->id)
            ->where('type', 'daily')
            ->first();

        $minutes = $workDay->balance_minutes ?? 0;
        $description = "Jornada {$workDay->date->format('d/m/Y')}";

        if ($existing) {
            // Recalcula: remove o efeito anterior e aplica o novo
            $diff = $minutes - $existing->minutes;

            // Atualiza todas as transações após essa
            if ($diff !== 0) {
                HourBankTransaction::where('user_id', $workDay->user_id)
                    ->where('date', '>=', $workDay->date)
                    ->where('id', '>=', $existing->id)
                    ->increment('balance_minutes', $diff);
            }

            $existing->update([
                'minutes' => $minutes,
                'description' => $description,
            ]);

            return $existing->fresh();
        }

        // Calcula o saldo anterior
        $previousBalance = HourBankTransaction::where('user_id', $workDay->user_id)
            ->where('date', '<', $workDay->date)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->value('balance_minutes') ?? 0;

        return HourBankTransaction::create([
            'user_id' => $workDay->user_id,
            'work_day_id' => $workDay->id,
            'date' => $workDay->date,
            'description' => $description,
            'minutes' => $minutes,
            'balance_minutes' => $previousBalance + $minutes,
            'type' => 'daily',
        ]);
    }

    /**
     * Resumo mensal do banco de horas.
     */
    public function getMonthSummary(User $user, int $year, int $month): array
    {
        $transactions = HourBankTransaction::forUser($user->id)
            ->forMonth($year, $month)
            ->orderBy('date')
            ->get();

        $totalMinutes = $transactions->sum('minutes');
        $lastTransaction = $transactions->last();

        return [
            'transactions' => $transactions,
            'total_minutes' => $totalMinutes,
            'total_formatted' => $this->minutesToFormatted($totalMinutes),
            'closing_balance' => $lastTransaction?->balance_minutes ?? 0,
            'closing_balance_formatted' => $this->minutesToFormatted($lastTransaction?->balance_minutes ?? 0),
        ];
    }

    /**
     * Resumo semanal do banco de horas.
     */
    public function getWeekSummary(User $user, string $startDate): array
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $transactions = HourBankTransaction::forUser($user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $totalMinutes = $transactions->sum('minutes');

        return [
            'transactions' => $transactions,
            'total_minutes' => $totalMinutes,
            'total_formatted' => $this->minutesToFormatted($totalMinutes),
            'start_date' => $start->format('d/m/Y'),
            'end_date' => $end->format('d/m/Y'),
        ];
    }

    /**
     * Converte minutos para formato +/-HH:MM.
     */
    public function minutesToFormatted(int $minutes): string
    {
        $abs = abs($minutes);
        $h = intdiv($abs, 60);
        $m = $abs % 60;
        $sign = $minutes >= 0 ? '+' : '-';
        return sprintf('%s%02d:%02d', $sign, $h, $m);
    }

    /**
     * Converte formato HH:MM para minutos.
     */
    public function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return ((int) $hours * 60) + (int) $minutes;
    }
}
