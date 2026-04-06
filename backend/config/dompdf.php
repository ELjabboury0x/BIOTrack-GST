<?php

return [
    'show_warnings' => false,

    // DomPDF resolves this path via realpath(); point it to workspace-level public/.
    'public_path' => dirname(base_path()) . DIRECTORY_SEPARATOR . 'public',

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
