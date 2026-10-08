<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourBankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_day_id',
        'date',
        'description',
        'minutes',
        'balance_minutes',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'minutes' => 'integer',
            'balance_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workDay(): BelongsTo
    {
        return $this->belongsTo(WorkDay::class);
    }

    public function getFormattedMinutesAttribute(): string
    {
        $abs = abs($this->minutes);
        $h = intdiv($abs, 60);
        $m = $abs % 60;
        $sign = $this->minutes >= 0 ? '+' : '-';
        return sprintf('%s%02d:%02d', $sign, $h, $m);
    }

    public function getFormattedBalanceMinutesAttribute(): string
    {
        $abs = abs($this->balance_minutes);
        $h = intdiv($abs, 60);
        $m = $abs % 60;
        $sign = $this->balance_minutes >= 0 ? '+' : '-';
        return sprintf('%s%02d:%02d', $sign, $h, $m);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
