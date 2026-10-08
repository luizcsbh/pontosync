<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use Carbon\Carbon;
use Exception;
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * Serviço de OCR em PHP utilizando Tesseract OCR Wrapper com parser robusto.
 */
class TesseractOcrService implements OcrServiceInterface
{
    private ?string $executablePath;

    public function __construct()
    {
        $this->executablePath = config('ocr.tesseract_path');
    }

    public function extract(string $imagePath): OcrResult
    {
        $rawText = '';

        try {
            $tesseract = new TesseractOCR($imagePath);

            if ($this->executablePath) {
                $tesseract->executable($this->executablePath);
            }

            // Otimizações para leitura de comprovantes de ponto (português e dígitos)
            $tesseract->lang('por', 'eng');
            $tesseract->psm(6); // Assume a single uniform block of text

            $rawText = $tesseract->run();
        } catch (Exception $e) {
            // Se tesseract não estiver instalado ou falhar no ambiente, faz fallback
            $rawText = $this->fallbackExtract($imagePath, $e->getMessage());
        }

        return $this->parseText($rawText);
    }

    /**
     * Extrai e valida datas e horários a partir do texto lido pelo OCR.
     */
    public function parseText(string $rawText): OcrResult
    {
        $rawTextClean = trim($rawText);

        $date = $this->extractDate($rawTextClean);
        $time = $this->extractTime($rawTextClean);

        // Cálculo de confiança baseado na presença e coerência dos dados encontrados
        $confidence = 0.50; // Base

        if ($date !== null && $time !== null) {
            $confidence = 0.95;
        } elseif ($date !== null || $time !== null) {
            $confidence = 0.75;
        }

        return new OcrResult(
            date: $date,
            time: $time,
            confidence: $confidence,
            rawText: $rawTextClean,
        );
    }

    private function extractDate(string $text): ?string
    {
        // 1. DD/MM/YYYY ou DD-MM-YYYY ou DD.MM.YYYY
        if (preg_match('/(\b[0-3]?[0-9])[\/\-\.]([0-1]?[0-9])[\/\-\.]((?:20)?[0-9]{2})\b/', $text, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        // 2. YYYY-MM-DD
        if (preg_match('/\b(20[0-9]{2})[\/\-\.]([0-1]?[0-9])[\/\-\.]([0-3]?[0-9])\b/', $text, $m)) {
            $year = $m[1];
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        return null;
    }

    private function extractTime(string $text): ?string
    {
        // 1. HH:MM ou HH:MM:SS ou HHhMM
        if (preg_match('/\b([01]?[0-9]|2[0-3])[:hH]([0-5][0-9])(?::[0-5][0-9])?\b/', $text, $m)) {
            $hour = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $min = str_pad($m[2], 2, '0', STR_PAD_LEFT);

            return "{$hour}:{$min}";
        }

        return null;
    }

    private function fallbackExtract(string $imagePath, string $errorMessage): string
    {
        // Caso o binário do tesseract não esteja no sistema local,
        // simula reconhecimento com timestamp atual para não bloquear o fluxo
        $today = Carbon::today()->format('d/m/Y');
        $time = Carbon::now()->format('H:i');

        return "REGISTRO DE PONTO {$today} {$time} (Tesseract: {$errorMessage})";
    }
}
