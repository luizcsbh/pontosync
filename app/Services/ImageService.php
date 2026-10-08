<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
     * Otimiza a imagem: redimensiona e comprime.
     */
    public function optimize(UploadedFile $file): string
    {
        $image = Image::read($file->getPathname());

        // Redimensiona mantendo proporção se maior que o máximo
        if ($image->width() > $this->maxWidth) {
            $image->scaleDown(width: $this->maxWidth);
        }

        return $image->toJpeg($this->jpegQuality)->toString();
    }
}
