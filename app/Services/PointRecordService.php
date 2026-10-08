<?php

namespace App\Services;

use App\Models\PointRecord;
use App\Models\User;
use App\Models\WorkDay;
use Carbon\Carbon;
use InvalidArgumentException;

class PointRecordService
{
    public function __construct(
        private readonly WorkDayService $workDayService,
        private readonly HourBankService $hourBankService,
    ) {}

    /**
     * Registra uma nova marcação de ponto.
     *
     * @param array{date: string, time: string, type: string, source: string, notes?: string, ocr_confidence?: float, ocr_raw_text?: string} $data
     */
    public function register(User $user, array $data): PointRecord
    {
        // Converte data do formato d/m/Y para Y-m-d
        $date = Carbon::createFromFormat('d/m/Y', $data['date'])->format('Y-m-d');
        $recordedAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$data['time']}");

        // Busca ou cria o WorkDay
        $workDay = $this->workDayService->getOrCreateWorkDay($user, $date);

        // Verifica duplicidade
        $this->checkDuplicateType($workDay, $data['type']);

        // Valida a sequência cronológica
        $this->validateChronologicalOrder($workDay, $data['type'], $recordedAt);

        // Cria o registro
        $record = PointRecord::create([
            'user_id' => $user->id,
            'work_day_id' => $workDay->id,
            'type' => $data['type'],
            'recorded_at' => $recordedAt,
            'source' => $data['source'],
            'ocr_confidence' => $data['ocr_confidence'] ?? null,
            'ocr_raw_text' => $data['ocr_raw_text'] ?? null,
            'notes' => $data['notes'] ?? null,
            'confirmed_at' => now(),
        ]);

        // Atualiza estatísticas do dia
        $this->workDayService->updateWorkDayStats($workDay);

        // Se o dia ficou completo, atualiza o banco de horas
        $workDay->refresh();
        if ($workDay->isComplete()) {
            $this->hourBankService->syncDailyTransaction($workDay);
        }

        return $record;
    }

    /**
     * Corrige uma marcação existente (mantém auditoria).
     */
    public function correct(PointRecord $record, array $data): PointRecord
    {
        $date = $record->workDay->date->format('Y-m-d');
        $recordedAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$data['time']}");

        // Valida sequência sem incluir o tipo atual
        $this->validateChronologicalOrder($record->workDay, $record->type, $recordedAt, excludeId: $record->id);

        $record->update([
            'original_recorded_at' => $record->original_recorded_at ?? $record->recorded_at,
            'recorded_at' => $recordedAt,
            'source' => PointRecord::SOURCE_CORRECTION,
            'notes' => $data['notes'] ?? $record->notes,
            'edited_at' => now(),
        ]);

        // Recalcula estatísticas
        $this->workDayService->updateWorkDayStats($record->workDay);

        $record->workDay->refresh();
        if ($record->workDay->isComplete()) {
            $this->hourBankService->syncDailyTransaction($record->workDay);
        }

        return $record;
    }

    /**
     * Retorna o próximo tipo esperado de marcação para o dia.
     */
    public function getNextExpectedType(WorkDay $workDay): ?string
    {
        $existing = $workDay->pointRecords->pluck('type')->toArray();

        foreach (PointRecord::TYPES as $type) {
            if (!in_array($type, $existing)) {
                return $type;
            }
        }

        return null; // Todos os 4 tipos já registrados
    }

    /**
     * Verifica se já existe marcação do mesmo tipo no dia.
     */
    private function checkDuplicateType(WorkDay $workDay, string $type): void
    {
        $exists = $workDay->pointRecords()->where('type', $type)->exists();

        if ($exists) {
            $typeName = PointRecord::typeOptions()[$type] ?? $type;
            throw new InvalidArgumentException(
                "Já existe uma marcação de '{$typeName}' para este dia."
            );
        }
    }

    /**
     * Valida a ordem cronológica das marcações.
     */
    private function validateChronologicalOrder(
        WorkDay $workDay,
        string $type,
        Carbon $recordedAt,
        ?int $excludeId = null
    ): void {
        $query = $workDay->pointRecords();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $records = $query->get()->keyBy('type');

        $entry = $records->get(PointRecord::TYPE_ENTRY);
        $lunchStart = $records->get(PointRecord::TYPE_LUNCH_START);
        $lunchEnd = $records->get(PointRecord::TYPE_LUNCH_END);
        $exit = $records->get(PointRecord::TYPE_EXIT);

        switch ($type) {
            case PointRecord::TYPE_LUNCH_START:
                if ($entry && $recordedAt->lte($entry->recorded_at)) {
                    throw new InvalidArgumentException(
                        'Saída para almoço deve ser após a entrada.'
                    );
                }
                break;

            case PointRecord::TYPE_LUNCH_END:
                if (!$lunchStart) {
                    throw new InvalidArgumentException(
                        'Registre a saída para almoço antes do retorno.'
                    );
                }
                if ($recordedAt->lte($lunchStart->recorded_at)) {
                    throw new InvalidArgumentException(
                        'Retorno do almoço deve ser após a saída para almoço.'
                    );
                }
                break;

            case PointRecord::TYPE_EXIT:
                if ($entry && $recordedAt->lte($entry->recorded_at)) {
                    throw new InvalidArgumentException(
                        'Saída deve ser após a entrada.'
                    );
                }
                if ($lunchEnd && $recordedAt->lte($lunchEnd->recorded_at)) {
                    throw new InvalidArgumentException(
                        'Saída deve ser após o retorno do almoço.'
                    );
                }
                break;
        }
    }
}
