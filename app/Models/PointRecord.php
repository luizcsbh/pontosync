<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PointRecord extends Model
{
    use HasFactory;

    public const TYPE_ENTRY = 'entry';
    public const TYPE_LUNCH_START = 'lunch_start';
    public const TYPE_LUNCH_END = 'lunch_end';
    public const TYPE_EXIT = 'exit';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_OCR = 'ocr';
    public const SOURCE_CORRECTION = 'correction';

    public const TYPES = [
        self::TYPE_ENTRY,
        self::TYPE_LUNCH_START,
        self::TYPE_LUNCH_END,
        self::TYPE_EXIT,
    ];

    protected $fillable = [
        'user_id',
        'work_day_id',
        'type',
        'recorded_at',
        'source',
        'ocr_confidence',
        'ocr_raw_text',
        'notes',
        'confirmed_at',
        'edited_at',
        'original_recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'edited_at' => 'datetime',
            'original_recorded_at' => 'datetime',
            'ocr_confidence' => 'decimal:2',
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

    public function image(): HasOne
    {
        return $this->hasOne(PointImage::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ENTRY => '🟢 Entrada',
            self::TYPE_LUNCH_START => '🟠 Saída para almoço',
            self::TYPE_LUNCH_END => '🔵 Retorno do almoço',
            self::TYPE_EXIT => '🔴 Saída',
            default => $this->type,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ENTRY => '🟢',
            self::TYPE_LUNCH_START => '🟠',
            self::TYPE_LUNCH_END => '🔵',
            self::TYPE_EXIT => '🔴',
            default => '⚪',
        };
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ENTRY => 'Entrada',
            self::TYPE_LUNCH_START => 'Saída para almoço',
            self::TYPE_LUNCH_END => 'Retorno do almoço',
            self::TYPE_EXIT => 'Saída',
            default => $this->type,
        };
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->recorded_at ? $this->recorded_at->format('H:i') : '--:--';
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_MANUAL => 'Manual',
            self::SOURCE_OCR => 'OCR (foto)',
            self::SOURCE_CORRECTION => 'Correção',
            default => $this->source,
        };
    }

    /**
     * Retorna os tipos de marcação disponíveis com labels
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_ENTRY => 'Entrada',
            self::TYPE_LUNCH_START => 'Saída para almoço',
            self::TYPE_LUNCH_END => 'Retorno do almoço',
            self::TYPE_EXIT => 'Saída',
        ];
    }
}
