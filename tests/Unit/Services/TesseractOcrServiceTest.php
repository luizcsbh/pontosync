<?php

use App\Services\Ocr\TesseractOcrService;

test('tesseract ocr service extracts valid date and 24h time from text', function () {
    $service = new TesseractOcrService();

    $rawText = "COMPROVANTE DE PONTO ELETRONICO\nDATA: 07/10/2026\nHORARIO: 08:03\nEMPRESA ABC LTDA";
    $result = $service->parseText($rawText);

    expect($result->isValid())->toBeTrue();
    expect($result->date)->toBe('2026-10-07');
    expect($result->time)->toBe('08:03');
    expect($result->isHighConfidence())->toBeTrue();
});

test('tesseract ocr service handles hyphen dates and various time formats', function () {
    $service = new TesseractOcrService();

    $rawText = "PONTO REGISTRADO EM 15-08-2026 AS 17h45";
    $result = $service->parseText($rawText);

    expect($result->isValid())->toBeTrue();
    expect($result->date)->toBe('2026-08-15');
    expect($result->time)->toBe('17:45');
});

test('tesseract ocr service marks low confidence when time is missing', function () {
    $service = new TesseractOcrService();

    $rawText = "APENAS TEXTO SEM NENHUM HORARIO DETECTADO";
    $result = $service->parseText($rawText);

    expect($result->isValid())->toBeFalse();
    expect($result->time)->toBeNull();
    expect($result->isLowConfidence())->toBeTrue();
});
