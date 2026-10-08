<?php

namespace App\Contracts;

use App\Services\Ocr\OcrResult;

interface OcrServiceInterface
{
    /**
     * Extrai data e hora de uma imagem.
     *
     * @param string $imagePath Caminho absoluto para a imagem
     */
    public function extract(string $imagePath): OcrResult;
}
