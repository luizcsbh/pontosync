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

    public function __construct()
    {
        $this->maxWidth = (int) config('ocr.image_max_width', 1600);
        $this->jpegQuality = (int) config('ocr.image_jpeg_quality', 75);
    }

    /**
     * Armazena a imagem otimizada e retorna o caminho relativo.
     *
     * @param string $date formato Y-m-d
     * @param string $type tipo da marcação (entry, lunch_start, etc.)
     */
    public function store(UploadedFile $file, string $date, string $type): string
    {
        $dateParts = explode('-', $date);
        $directory = 'point-records/' . $dateParts[0] . '/' . $dateParts[1] . '/' . $dateParts[2];

        $timestamp = str_replace('-', '', $date) . '_' . date('Hi');
        $filename = "{$date}_{$timestamp}_{$type}.jpg";
        $path = "{$directory}/{$filename}";

        $optimizedContent = $this->optimize($file);

        Storage::disk('local')->put($path, $optimizedContent);

        return $path;
    }

    /**
     * Otimiza a imagem: redimensiona mantendo proporção e comprime em JPEG.
     */
    public function optimize(UploadedFile $file): string
    {
        $filePath = $file->getPathname();

        try {
            // Tenta via Intervention Image v4
            $manager = ImageManager::usingDriver(GdDriver::class);
            $image = $manager->decodePath($filePath);

            if ($image->width() > $this->maxWidth) {
                $image->scaleDown(width: $this->maxWidth);
            }

            return (string) $image->encode(new JpegEncoder(quality: $this->jpegQuality));
        } catch (Exception $e) {
            // Fallback robusto nativo via GD PHP
            return $this->optimizeWithNativeGd($filePath);
        }
    }

    /**
     * Redimensionamento e compressão nativa via extensão GD do PHP.
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

        // Calcula novas dimensões se exceder a largura máxima
        if ($origWidth > $this->maxWidth) {
            $newWidth = $this->maxWidth;
            $newHeight = (int) round(($origHeight / $origWidth) * $newWidth);

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($srcImage);
            $srcImage = $dstImage;
        }

        // Captura o stream JPEG comprimido
        ob_start();
        imagejpeg($srcImage, null, $this->jpegQuality);
        $output = ob_get_clean();
        imagedestroy($srcImage);

        return $output ?: (string) file_get_contents($filePath);
    }
}
