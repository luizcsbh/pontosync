<?php

namespace App\Http\Controllers;

use App\Contracts\OcrServiceInterface;
use App\Services\ImageService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    private string $tz;

    public function __construct(
        private readonly OcrServiceInterface $ocrService,
        private readonly ImageService $imageService,
    ) {
        $this->tz = config('app.timezone', 'America/Sao_Paulo');
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ], [
            'photo.required' => 'Envie uma fotografia para processamento OCR.',
            'photo.image'    => 'O arquivo enviado deve ser uma imagem.',
            'photo.max'      => 'A imagem não pode ultrapassar 10MB.',
        ]);

        $photo = $request->file('photo');
        $originalPath = $photo->getPathname();

        // Caminho da imagem P&B pré-processada (arquivo temporário)
        $ocrImagePath = null;

        try {
            // ── ETAPA 1: converte para escala de cinza + alto contraste em arquivo temp
            $ocrImagePath = $this->imageService->prepareForOcr($originalPath);

            // ── ETAPA 2: roda OCR sobre a imagem P&B tratada
            $ocrResult = $this->ocrService->extract($ocrImagePath);

            // Formata a data extraída para DD/MM/YYYY (America/Sao_Paulo)
            $formattedDate = null;
            if ($ocrResult->date) {
                try {
                    $formattedDate = Carbon::parse($ocrResult->date, $this->tz)->format('d/m/Y');
                } catch (Exception) {
                    $formattedDate = null;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'date'                 => $formattedDate ?? Carbon::today($this->tz)->format('d/m/Y'),
                    'time'                 => $ocrResult->time ?? Carbon::now($this->tz)->format('H:i'),
                    'confidence'           => $ocrResult->confidence,
                    'confidence_percent'   => $ocrResult->getConfidencePercent(),
                    'is_high_confidence'   => $ocrResult->isHighConfidence(),
                    'is_medium_confidence' => $ocrResult->isMediumConfidence(),
                    'is_low_confidence'    => $ocrResult->isLowConfidence(),
                    'raw_text'             => $ocrResult->rawText,
                    'auto_confirm'         => config('ocr.auto_confirm', false) && $ocrResult->isHighConfidence(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar imagem: ' . $e->getMessage(),
            ], 422);
        } finally {
            // ── ETAPA 3: remove sempre o arquivo temporário P&B
            if ($ocrImagePath && file_exists($ocrImagePath)) {
                @unlink($ocrImagePath);
            }
        }
    }
}
