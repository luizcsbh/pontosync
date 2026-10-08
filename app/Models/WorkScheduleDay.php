<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkScheduleDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_schedule_id',
        'day_of_week',
        'is_workday',
        'entry_time_minutes',
        'lunch_start_minutes',
        'lunch_end_minutes',
        'exit_time_minutes',
        'expected_minutes',
    ];

    protected function casts(): array
    {
        return [
            'is_workday' => 'boolean',
            'day_of_week' => 'integer',
            'entry_time_minutes' => 'integer',
            'lunch_start_minutes' => 'integer',
            'lunch_end_minutes' => 'integer',
            'exit_time_minutes' => 'integer',
            'expected_minutes' => 'integer',
        ];
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            default => 'Desconhecido',
        };
    }

    public function getShortDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            0 => 'Dom',
            1 => 'Seg',
            2 => 'Ter',
            3 => 'Qua',
            4 => 'Qui',
            5 => 'Sex',
            6 => 'Sáb',
            default => '???',
        };
    }

    /**
     * Converte minutos desde 00:00 para formato HH:MM
     */
    public function minutesToTime(?int $minutes): string
    {
        if ($minutes === null) {
            return '--:--';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }

    public function getEntryTimeAttribute(): string
    {
        return $this->minutesToTime($this->entry_time_minutes);
    }

    public function getLunchStartTimeAttribute(): string
    {
        return $this->minutesToTime($this->lunch_start_minutes);
    }

    public function getLunchEndTimeAttribute(): string
    {
        return $this->minutesToTime($this->lunch_end_minutes);
    }

    public function getExitTimeAttribute(): string
    {
        return $this->minutesToTime($this->exit_time_minutes);
    }

    public function getExpectedTimeAttribute(): string
    {
        return $this->minutesToTime($this->expected_minutes);
    }
}
