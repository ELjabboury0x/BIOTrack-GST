<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$dir = __DIR__ . '/../Bilan_Activites';
$files = glob($dir . '/*.xlsx');
$result = [];

foreach ($files as $file) {
    $entry = [
        'file' => basename($file),
        'sheets' => [],
    ];

    $spreadsheet = IOFactory::load($file);
    foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
        $title = $sheet->getTitle();
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = (int) $sheet->getHighestDataRow();

        $headerRowNum = 1;
        $header = [];

        for ($r = 1; $r <= min(10, max(1, $highestRow)); $r++) {
            $row = $sheet->rangeToArray("A{$r}:{$highestColumn}{$r}", null, true, true, false);
            $row = isset($row[0]) ? $row[0] : [];

            $nonEmpty = [];
            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    $nonEmpty[] = $value;
                }
            }

            if (count($nonEmpty) >= 2) {
                $headerRowNum = $r;
                foreach ($row as $value) {
                    $header[] = trim((string) $value);
                }
                break;
            }
        }

        $sampleRows = [];
        for ($r = $headerRowNum + 1; $r <= min($headerRowNum + 4, $highestRow); $r++) {
            $row = $sheet->rangeToArray("A{$r}:{$highestColumn}{$r}", null, true, true, false);
            $row = isset($row[0]) ? $row[0] : [];
            $clean = [];

            foreach ($row as $value) {
                $clean[] = is_scalar($value) ? trim((string) $value) : '';
            }

            $sampleRows[] = $clean;
        }

        $entry['sheets'][] = [
            'name' => $title,
            'header_row' => $headerRowNum,
            'columns' => $header,
            'rows' => $highestRow,
            'sample_rows' => $sampleRows,
        ];
    }

    $result[] = $entry;
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
