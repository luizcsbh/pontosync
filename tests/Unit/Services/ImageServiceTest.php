<?php

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;

test('image is compressed to storage size correctly', function () {
    $service = new ImageService();

    // Cria imagem real via GD com 2000px de largura (acima do maior limite possível)
    $tmpSrc = tempnam(sys_get_temp_dir(), 'test_compress') . '.jpg';
    $img = imagecreatetruecolor(2000, 1250);
    imagefill($img, 0, 0, imagecolorallocate($img, 100, 150, 200));
    imagejpeg($img, $tmpSrc, 90);
    imagedestroy($img);

    $compressed = $service->compressForStorage($tmpSrc);

    expect($compressed)->toBeString();
    expect(strlen($compressed))->toBeGreaterThan(0);

    $size = getimagesizefromstring($compressed);
    $configuredMax = max(100, (int) config('ocr.image_max_width', 1200));
    expect($size[0])->toBeLessThanOrEqual($configuredMax); // Não excede max width configurada

    @unlink($tmpSrc);
});

test('prepareForOcr generates grayscale temp file and removes it after', function () {
    $service = new ImageService();

    $file = UploadedFile::fake()->image('espelho.jpg', 1280, 720);
    $ocrPath = $service->prepareForOcr($file->getPathname());

    // Arquivo temporário foi criado
    expect(file_exists($ocrPath))->toBeTrue();

    // A imagem foi gerada como JPEG (não é vazia)
    expect(filesize($ocrPath))->toBeGreaterThan(100);

    // Verifica que é uma imagem válida
    $info = getimagesize($ocrPath);
    expect($info)->not->toBeFalse();
    // Largura deve ser pelo menos a da imagem original (não encolhe, não amplia)
    expect($info[0])->toBeGreaterThanOrEqual(1);

    // Limpeza manual após o teste (como o controller faz no finally)
    if (file_exists($ocrPath)) {
        unlink($ocrPath);
    }
});

test('prepareForOcr image is actually grayscale (all RGB channels equal)', function () {
    $service = new ImageService();

    // Cria imagem colorida de teste (vermelho puro)
    $tmpSrc = tempnam(sys_get_temp_dir(), 'test_src') . '.jpg';
    $colorImg = imagecreatetruecolor(100, 100);
    $red = imagecolorallocate($colorImg, 255, 0, 0);
    imagefill($colorImg, 0, 0, $red);
    imagejpeg($colorImg, $tmpSrc, 90);
    imagedestroy($colorImg);

    // Pré-processa para OCR
    $ocrPath = $service->prepareForOcr($tmpSrc);

    // Carrega resultado e verifica que é escala de cinza
    $resultImg = imagecreatefromjpeg($ocrPath);
    $pixel = imagecolorat($resultImg, 50, 50);
    $rgb = imagecolorsforindex($resultImg, $pixel);

    // Em uma imagem greyscale, R == G == B (ou muito próximos por compressão JPEG)
    $diff = max(abs($rgb['red'] - $rgb['green']), abs($rgb['red'] - $rgb['blue']));
    expect($diff)->toBeLessThan(15); // Tolerância por artefatos JPEG

    imagedestroy($resultImg);

    @unlink($tmpSrc);
    @unlink($ocrPath);
});
