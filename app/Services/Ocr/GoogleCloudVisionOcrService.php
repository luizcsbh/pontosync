<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use RuntimeException;

/**
 * Integração com Google Cloud Vision API.
 * Configure a chave via GOOGLE_CLOUD_VISION_KEY no .env
 */
class GoogleCloudVisionOcrService implements OcrServiceInterface
{
    public function extract(string $imagePath): OcrResult
    {
        $apiKey = config('services.google_cloud_vision.key');

        if (empty($apiKey)) {
            throw new RuntimeException(
                'Google Cloud Vision não configurado. Defina GOOGLE_CLOUD_VISION_KEY no .env'
            );
        }

        // Lê a imagem e converte para base64
        $imageContent = base64_encode(file_get_contents($imagePath));

        $payload = [
            'requests' => [
                [
                    'image' => ['content' => $imageContent],
                    'features' => [
                        ['type' => 'TEXT_DETECTION', 'maxResults' => 1],
                    ],
                ],
            ],
        ];

        $response = $this->callApi($apiKey, $payload);
        return $this->parseResponse($response);
    }

    private function callApi(string $apiKey, array $payload): array
    {
        $url = "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException("Google Cloud Vision retornou HTTP {$httpCode}");
        }

        return json_decode($result, true);
    }

    private function parseResponse(array $response): OcrResult
    {
        $rawText = $response['responses'][0]['fullTextAnnotation']['text'] ?? '';
        $confidence = $response['responses'][0]['fullTextAnnotation']['pages'][0]['confidence'] ?? 0.0;

        [$date, $time] = $this->extractDateAndTime($rawText);

        return new OcrResult(
            date: $date,
            time: $time,
            confidence: (float) $confidence,
            rawText: trim($rawText),
        );
    }

    private function extractDateAndTime(string $text): array
    {
        $date = null;
        $time = null;

        // Tenta encontrar data no formato DD/MM/YYYY
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $text, $m)) {
            $date = "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        // Tenta encontrar horário no formato HH:MM
        if (preg_match('/\b([01]\d|2[0-3]):([0-5]\d)\b/', $text, $m)) {
            $time = "{$m[1]}:{$m[2]}";
        }

        return [$date, $time];
    }
}
