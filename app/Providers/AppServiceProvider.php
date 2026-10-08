<?php

namespace App\Providers;

use App\Contracts\FileStorageInterface;
use App\Contracts\OcrServiceInterface;
use App\Services\Ocr\FakeOcrService;
use App\Services\Ocr\GoogleCloudVisionOcrService;
use App\Services\Storage\LocalFileStorageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind OCR service based on env configuration
        $this->app->bind(OcrServiceInterface::class, function () {
            return match (config('ocr.provider')) {
                'google_vision' => new GoogleCloudVisionOcrService(),
                default => new FakeOcrService(),
            };
        });

        // Bind file storage service
        $this->app->bind(FileStorageInterface::class, LocalFileStorageService::class);
    }

    public function boot(): void
    {
        //
    }
}
