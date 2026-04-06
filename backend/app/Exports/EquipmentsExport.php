<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EquipmentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return array_map([$this, 'normalizeText'], [
            'N° inventaire',
            'Désignation',
            'N° de série',
            'Unité',
            'Secteur',
            'Description secteur',
            'Marque',
            'Modèle',
            'Marché',
            'Lot',
            'Article',
            'Date réception provisoire',
            'Durée garantie',
            'Date réception définitive',
            'Statut',
        ]);
    }

    public function map($equipment): array
    {
        $row = [
            $equipment->inventory_number_current ?: '-',
            $equipment->designation ?: '-',
            $equipment->serial_number ?: '-',
            $equipment->unit_name ?: ($equipment->service_name ?: '-'),
            $equipment->sector_name ?: '-',
            $equipment->sector_description ?: ($equipment->exact_location ?: '-'),
            $equipment->brand_name ?: '-',
            $equipment->model_name ?: '-',
            $equipment->market_label ?: '-',
            $equipment->lot_number ?: '-',
            $equipment->article ?: '-',
            optional($equipment->date_reception_provisoire)->toDateString() ?: '-',
            $equipment->duree_garantie ?: '-',
            optional($equipment->date_reception_definitive)->toDateString() ?: '-',
            $equipment->operational_status ?: '-',
        ];

        return array_map([$this, 'normalizeText'], $row);
    }

    private function normalizeText($value): string
    {
        $text = (string) $value;

        // Strip control chars that can break XML in XLSX payloads.
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';

        if ($text === '') {
            return '';
        }

        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        $converted = @mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        $converted = @mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        return @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: '';
    }
}
