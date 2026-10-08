<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_schedule_id',
        'date',
        'status',
        'expected_minutes',
        'worked_minutes',
        'balance_minutes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'expected_minutes' => 'integer',
            'worked_minutes' => 'integer',
            'balance_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function pointRecords(): HasMany
    {
        return $this->hasMany(PointRecord::class)->orderBy('recorded_at');
    }

    public function hourBankTransactions(): HasMany
    {
        return $this->hasMany(HourBankTransaction::class);
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    public function getFormattedBalanceAttribute(): string
    {
        if ($this->balance_minutes === null) {
            return '--:--';
        }
        $abs = abs($this->balance_minutes);
        $h = intdiv($abs, 60);
        $m = $abs % 60;
        $sign = $this->balance_minutes >= 0 ? '+' : '-';
        return sprintf('%s%02d:%02d', $sign, $h, $m);
    }

    public function getFormattedWorkedMinutesAttribute(): string
    {
        if ($this->worked_minutes === null) {
            return '--:--';
        }
        $h = intdiv($this->worked_minutes, 60);
        $m = $this->worked_minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }

    public function getFormattedExpectedMinutesAttribute(): string
    {
        $h = intdiv($this->expected_minutes, 60);
        $m = $this->expected_minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'incomplete' => '⚠ Jornada incompleta',
            'complete' => '✓ Jornada completa',
            'absent' => '✗ Falta',
            'holiday' => '🏖 Feriado',
            'off' => '🔴 Folga',
            default => $this->status,
        };
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeForWeek(Builder $query, string $startDate): Builder
    {
        $end = Carbon::parse($startDate)->addDays(6)->toDateString();
        return $query->whereBetween('date', [$startDate, $end]);
    }
}
