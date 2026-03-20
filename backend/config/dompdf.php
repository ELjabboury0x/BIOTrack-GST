<?php

return [
    'show_warnings' => false,

    'options' => [
        'defaultFont' => 'DejaVu Serif',
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => true,
        'isRemoteEnabled' => false,
        'dpi' => 200,
        'defaultPaperSize' => 'a4',
        'defaultMediaType' => 'print',
        'tempDir' => storage_path('app/dompdf-temp'),
        'fontDir' => storage_path('fonts'),
        'fontCache' => storage_path('fonts'),
        'chroot' => [
            base_path(),
            public_path(),
            storage_path(),
        ],
    ],
];
