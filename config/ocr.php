<?php

return [
    'provider' => env('OCR_PROVIDER', 'fake'),
    'confidence_high' => (float) env('OCR_CONFIDENCE_HIGH', 0.90),
    'confidence_medium' => (float) env('OCR_CONFIDENCE_MEDIUM', 0.70),
    'auto_confirm' => (bool) env('OCR_AUTO_CONFIRM', false),
    'image_max_width' => (int) env('IMAGE_MAX_WIDTH', 1600),
    'image_jpeg_quality' => (int) env('IMAGE_JPEG_QUALITY', 75),
];
