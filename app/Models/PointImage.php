<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_record_id',
        'user_id',
        'path',
        'disk',
        'original_name',
        'mime_type',
        'size',
        'ocr_processed',
        'ocr_result',
    ];

    protected function casts(): array
    {
        return [
            'ocr_processed' => 'boolean',
            'ocr_result' => 'array',
            'size' => 'integer',
        ];
    }

    public function pointRecord(): BelongsTo
    {
        return $this->belongsTo(PointRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->size === null) {
            return '--';
        }
        if ($this->size < 1024) {
            return $this->size . ' B';
        }
        if ($this->size < 1048576) {
            return round($this->size / 1024, 1) . ' KB';
        }
        return round($this->size / 1048576, 1) . ' MB';
    }
}
