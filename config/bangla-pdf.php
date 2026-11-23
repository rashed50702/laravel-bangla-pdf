<?php

return [

    'bengali_font' => env('BANGLA_PDF_BENGALI_FONT', 'freeserifbangla'),

    'latin_font' => env('BANGLA_PDF_LATIN_FONT', 'freeserif'),

    'bengali_scale' => (float) env('BANGLA_PDF_BENGALI_SCALE', 1.15),

    'font_path' => storage_path('fonts/bangla-pdf'),

    'fonts' => [
        // 'solaimanlipi' => ['R' => 'SolaimanLipi.ttf', 'B' => 'SolaimanLipi_Bold.ttf'],
        // 'kalpurush'    => ['R' => 'kalpurush.ttf'],
        // 'nikosh'       => ['R' => 'Nikosh.ttf'],
    ],

    'protect_punctuation' => true,

    'temp_dir' => storage_path('app/bangla-pdf/temp'),

    'format' => 'A4',

    'orientation' => 'P',

    'margins' => [
        'top' => 10,
        'right' => 8,
        'bottom' => 15,
        'left' => 8,
        'header' => 5,
        'footer' => 5,
    ],

    'stylesheet' => null,

    'footer' => null,

    'mpdf' => [],

];
