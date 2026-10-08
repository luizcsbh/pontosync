<?php

namespace App\Services\Ocr;

class OcrResult
{
    public function __construct(
        public readonly ?string $date,
        public readonly ?string $time,
        public readonly float $confidence,
        public readonly string $rawText,
    ) {}

    public function isHighConfidence(): bool
    {
        return $this->confidence >= (float) config('ocr.confidence_high', 0.90);
    }

    public function isMediumConfidence(): bool
    {
        return $this->confidence >= (float) config('ocr.confidence_medium', 0.70);
    }

    public function isLowConfidence(): bool
    {
        return !$this->isMediumConfidence();
    }

    public function isValid(): bool
    {
        return $this->date !== null && $this->time !== null;
    }

    public function getConfidencePercent(): int
    {
        return (int) round($this->confidence * 100);
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'time' => $this->time,
            'confidence' => $this->confidence,
            'raw_text' => $this->rawText,
        ];
    }
}
