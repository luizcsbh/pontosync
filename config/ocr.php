<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Provedor de OCR
    |--------------------------------------------------------------------------
    | Opções disponíveis: 'tesseract', 'fake', 'google_vision'
    */
    'provider' => env('OCR_PROVIDER', 'tesseract'),

    /*
    | Caminho personalizado do executável Tesseract (caso não esteja no PATH global)
    | Exemplo no Linux/Mac: '/usr/local/bin/tesseract' ou '/opt/homebrew/bin/tesseract'
    | Exemplo no Windows: 'C:\Program Files\Tesseract-OCR\tesseract.exe'
    */
    'tesseract_path' => env('TESSERACT_PATH', null),

    'confidence_high' => (float) env('OCR_CONFIDENCE_HIGH', 0.90),
    'confidence_medium' => (float) env('OCR_CONFIDENCE_MEDIUM', 0.70),
    'auto_confirm' => (bool) env('OCR_AUTO_CONFIRM', false),
    'image_max_width' => (int) env('IMAGE_MAX_WIDTH', 1600),
    'image_jpeg_quality' => (int) env('IMAGE_JPEG_QUALITY', 75),
];
