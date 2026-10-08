<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use Carbon\Carbon;

/**
 * Implementação fake do OCR para desenvolvimento e testes.
 * Retorna dados simulados com alta confiança.
 */
class FakeOcrService implements OcrServiceInterface
{
    public function extract(string $imagePath): OcrResult
    {
        // Simula um pequeno processamento
        usleep(100000); // 100ms

        $today = Carbon::today()->format('Y-m-d');
        $time = '08:00';
        $rawText = Carbon::today()->format('d/m/Y') . ' ' . $time;

        return new OcrResult(
            date: $today,
            time: $time,
            confidence: 0.95,
            rawText: $rawText,
        );
    }
}
