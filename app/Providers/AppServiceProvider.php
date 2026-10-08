<?php

namespace App\Providers;

use App\Contracts\FileStorageInterface;
use App\Contracts\OcrServiceInterface;
use App\Services\Ocr\FakeOcrService;
use App\Services\Ocr\GoogleCloudVisionOcrService;
use App\Services\Ocr\TesseractOcrService;
use App\Services\Storage\LocalFileStorageService;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind OCR service based on env configuration
        $this->app->bind(OcrServiceInterface::class, function () {
            return match (config('ocr.provider')) {
                'tesseract'    => new TesseractOcrService(),
                'google_vision' => new GoogleCloudVisionOcrService(),
                default        => new FakeOcrService(),
            };
        });

        // Bind file storage service
        $this->app->bind(FileStorageInterface::class, LocalFileStorageService::class);
    }

    public function boot(): void
    {
        // Garante que Carbon use sempre America/Sao_Paulo como timezone padrão,
        // independente de `date.timezone` do php.ini do servidor
        $timezone = config('app.timezone', 'America/Sao_Paulo');
        Carbon::setLocale('pt_BR');
        date_default_timezone_set($timezone);
    }
}
