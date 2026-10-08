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

    public function __construct()
    {
        // Qualidade otimizada mais leve (padrão 65%) e largura máx 1200px para economizar espaço
        $this->maxWidth = (int) config('ocr.image_max_width', 1200);
        $this->jpegQuality = (int) config('ocr.image_jpeg_quality', 65);
        $this->disk = 'public';
    }

    /**
     * Armazena a imagem otimizada/comprimida e retorna o caminho relativo.
     *
     * @param string $date formato Y-m-d
     * @param string $type tipo da marcação (entry, lunch_start, etc.)
     */
    public function store(UploadedFile $file, string $date, string $type): string
    {
        $dateParts = explode('-', $date);
        $directory = 'point-records/' . $dateParts[0] . '/' . $dateParts[1] . '/' . $dateParts[2];

        $timestamp = str_replace('-', '', $date) . '_' . date('His') . '_' . uniqid();
        $filename = "{$date}_{$timestamp}_{$type}.jpg";
        $path = "{$directory}/{$filename}";

        $optimizedContent = $this->optimize($file);

        Storage::disk($this->disk)->put($path, $optimizedContent);

        return $path;
    }

    /**
     * Otimiza a imagem: redimensiona mantendo proporção e comprime em JPEG leve.
     */
    public function optimize(UploadedFile $file): string
    {
        $filePath = $file->getPathname();

        try {
            // Processamento via Intervention Image v4
            $manager = ImageManager::usingDriver(GdDriver::class);
            $image = $manager->decodePath($filePath);

            if ($image->width() > $this->maxWidth) {
                $image->scaleDown(width: $this->maxWidth);
            }

            return (string) $image->encode(new JpegEncoder(quality: $this->jpegQuality));
        } catch (Exception $e) {
            // Fallback nativo via GD PHP
            return $this->optimizeWithNativeGd($filePath);
        }
    }

    /**
     * Redimensionamento e compressão nativa com qualidade mais baixa para economia de banco/disco.
     */
    private function optimizeWithNativeGd(string $filePath): string
    {
        $imageInfo = @getimagesize($filePath);

        if (!$imageInfo) {
            return (string) file_get_contents($filePath);
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $mime = $imageInfo['mime'] ?? '';

        $srcImage = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($filePath),
            'image/png' => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            default => null,
        };

        if (!$srcImage) {
            return (string) file_get_contents($filePath);
        }

        // Redimensiona proporcionalmente para largura máxima
        $targetWidth = min($origWidth, $this->maxWidth);
        $targetHeight = (int) round(($origHeight / $origWidth) * $targetWidth);

        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);
        imagedestroy($srcImage);

        // Captura o stream JPEG comprimido com qualidade configurada
        ob_start();
        imagejpeg($dstImage, null, $this->jpegQuality);
        $output = ob_get_clean();
        imagedestroy($dstImage);

        return $output ?: (string) file_get_contents($filePath);
    }
}
