<?php

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;

test('image service optimizes and resizes image successfully', function () {
    $service = new ImageService();

    // Cria imagem falsa de 2000x1000
    $file = UploadedFile::fake()->image('recibo_ponto.jpg', 2000, 1000);

    $optimizedContent = $service->optimize($file);

    expect($optimizedContent)->toBeString();
    expect(strlen($optimizedContent))->toBeGreaterThan(0);

    // Verifica dimensões da imagem otimizada
    $size = getimagesizefromstring($optimizedContent);
    expect($size)->not->toBeFalse();
    expect($size[0])->toBeLessThanOrEqual(1600); // Max width configurada
});
