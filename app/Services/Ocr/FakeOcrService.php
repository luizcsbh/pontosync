<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use Carbon\Carbon;

/**
 * Implementação fake do OCR para desenvolvimento e testes.
 * Retorna dados simulados com alta confiança e sinaliza isMocked=true.
 */
class FakeOcrService implements OcrServiceInterface
{
    public function extract(string $imagePath): OcrResult
    {
        // Simula latência de processamento de imagem
        usleep(120_000); // 120ms

        $today   = Carbon::today()->format('Y-m-d');
        $time    = Carbon::now()->format('H:i');
        $rawText = Carbon::today()->format('d/m/Y') . ' ' . $time . ' [SIMULADO]';

        return new OcrResult(
            date:      $today,
            time:      $time,
            confidence: 0.95,
            rawText:   $rawText,
            isMocked:  true,     // ← sinaliza que os dados são mockados
        );
    }
}
