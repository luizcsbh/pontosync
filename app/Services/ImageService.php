<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class ImageService
{
    private int $maxWidth;
    private int $jpegQuality;
    private string $disk;

    // Qualidade de saída para o arquivo temporário de OCR (alta — Tesseract precisa de clareza)
    private const OCR_QUALITY = 90;

    public function __construct()
    {
        $this->maxWidth   = max(100, (int) config('ocr.image_max_width', 1200));
        $this->jpegQuality = max(10, min(100, (int) config('ocr.image_jpeg_quality', 65)));
        $this->disk = 'public';
    }

    // ─────────────────────────────────────────────
    // PARTE 1 — SALVAR IMAGEM COMPRIMIDA (exibição)
    // ─────────────────────────────────────────────

    /**
     * Salva a imagem otimizada (comprimida, colorida) no disco para exibição/thumbnail.
     * Retorna o caminho relativo dentro do disco.
     *
     * @param string $date formato Y-m-d
     * @param string $type entry|lunch_start|lunch_end|exit
     */
    public function store(UploadedFile $file, string $date, string $type): string
    {
        $dateParts = explode('-', $date);
        $directory = 'point-records/' . $dateParts[0] . '/' . $dateParts[1] . '/' . $dateParts[2];
        $filename = "{$date}_" . date('His') . '_' . uniqid() . "_{$type}.jpg";
        $path = "{$directory}/{$filename}";

        $compressed = $this->compressForStorage($file->getPathname());

        Storage::disk($this->disk)->put($path, $compressed);

        return $path;
    }

    /**
     * Comprime a imagem para armazenamento (menor tamanho, mantém cor).
     */
    public function compressForStorage(string $filePath): string
    {
        try {
            $manager = ImageManager::usingDriver(GdDriver::class);
            $image = $manager->decodePath($filePath);

            if ($image->width() > $this->maxWidth) {
                $image->scaleDown(width: $this->maxWidth);
            }

            return (string) $image->encode(new JpegEncoder(quality: $this->jpegQuality));
        } catch (Exception) {
            return $this->nativeCompress($filePath, $this->maxWidth, $this->jpegQuality);
        }
    }

    // Mantém compatibilidade com código anterior
    public function optimize(UploadedFile $file): string
    {
        return $this->compressForStorage($file->getPathname());
    }

    // ─────────────────────────────────────────────
    // PARTE 2 — GERAR IMAGEM P&B PARA OCR
    // ─────────────────────────────────────────────

    /**
     * Gera uma versão pré-processada da imagem exclusivamente para OCR:
     *   1. Amplia a imagem se pequena (mais pixels → mais acurácia Tesseract)
     *   2. Converte para ESCALA DE CINZA
     *   3. Aplica aumento de contraste / nitidez
     *   4. Binarização suave (realça texto escuro sobre fundo claro)
     *
     * Salva em arquivo temporário e retorna o path absoluto.
     * O chamador é responsável por excluir o arquivo após o uso.
     */
    public function prepareForOcr(string $sourceFilePath): string
    {
        $tmpPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.jpg';

        try {
            $this->prepareWithIntervention($sourceFilePath, $tmpPath);
        } catch (Exception) {
            $this->prepareWithNativeGd($sourceFilePath, $tmpPath);
        }

        return $tmpPath;
    }

    /**
     * Pré-processamento via Intervention Image v4.
     * Mantém a resolução original — ampliar para 2400px esgota memória no GD.
     * Grayscale + contraste é suficiente para o Tesseract reconhecer texto.
     */
    private function prepareWithIntervention(string $src, string $dest): void
    {
        $prevMemory = ini_set('memory_limit', '256M');

        try {
            $manager = ImageManager::usingDriver(GdDriver::class);
            $image = $manager->decodePath($src);

            // 1. Converte para escala de cinza
            $image->grayscale();

            // 2. Aumenta contraste (realça bordas de texto)
            $image->contrast(30);

            // Salva com alta qualidade para o Tesseract
            file_put_contents($dest, (string) $image->encode(new JpegEncoder(quality: self::OCR_QUALITY)));
        } finally {
            if ($prevMemory !== false) {
                ini_set('memory_limit', $prevMemory);
            }
        }
    }

    /**
     * Pré-processamento via GD nativo do PHP (fallback).
     * Converte para P&B real com ajuste de brilho e contraste via imagefilter.
     */
    private function prepareWithNativeGd(string $src, string $dest): void
    {
        $imageInfo = @getimagesize($src);
        if (!$imageInfo) {
            copy($src, $dest);
            return;
        }

        $mime = $imageInfo['mime'] ?? '';
        $origW = $imageInfo[0];
        $origH = $imageInfo[1];

        $srcGd = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($src),
            'image/png'               => @imagecreatefrompng($src),
            'image/webp'              => @imagecreatefromwebp($src),
            default                   => null,
        };

        if (!$srcGd) {
            copy($src, $dest);
            return;
        }

        // 1. Converter para Escala de Cinza
        imagefilter($srcGd, IMG_FILTER_GRAYSCALE);

        // 2. Aumentar Contraste (-50 em GD aumenta o contraste)
        imagefilter($srcGd, IMG_FILTER_CONTRAST, -50);

        ob_start();
        imagejpeg($srcGd, null, self::OCR_QUALITY);
        $data = ob_get_clean();
        imagedestroy($srcGd);

        file_put_contents($dest, $data ?: file_get_contents($src));
    }

    // ─────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────

    private function nativeCompress(string $filePath, int $maxW, int $quality): string
    {
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return (string) file_get_contents($filePath);
        }

        $origW  = $imageInfo[0];
        $origH  = $imageInfo[1];
        $mime   = $imageInfo['mime'] ?? '';

        $src = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($filePath),
            'image/png'               => @imagecreatefrompng($filePath),
            'image/webp'              => @imagecreatefromwebp($filePath),
            default                   => null,
        };

        if (!$src) {
            return (string) file_get_contents($filePath);
        }

        $targetW = min($origW, $maxW);
        $targetH = (int) round(($origH / $origW) * $targetW);

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, $quality);
        $output = ob_get_clean();
        imagedestroy($dst);

        return $output ?: (string) file_get_contents($filePath);
    }
}
