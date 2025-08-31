<?php

namespace App\Exports;

use App\Models\DiamondData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LargeDiamondDataExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return DiamondData::filter($this->filters)->with('upload');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cut',
            'Color',
            'Clarity',
            'Carat Weight',
            'Cut Quality',
            'Lab',
            'Symmetry',
            'Polish',
            'Eye Clean',
            'Culet Size',
            'Culet Condition',
            'Depth Percent',
            'Table Percent',
            'Measurement Length',
            'Measurement Width',
            'Measurement Depth',
            'Girdle Min',
            'Girdle Max',
            'Fluorescence Color',
            'Fluorescence Intensity',
            'Fancy Color Dominant',
            'Fancy Color Secondary',
            'Fancy Color Overtone',
            'Fancy Color Intensity',
            'Total Sales Price',
            'Upload Filename',
            'Date Added'
        ];
    }

    public function map($diamond): array
    {
        return [
            $diamond->id,
            $diamond->cut,
            $diamond->color,
            $diamond->clarity,
            $diamond->carat_weight,
            $diamond->cut_quality,
            $diamond->lab,
            $diamond->symmetry,
            $diamond->polish,
            $diamond->eye_clean,
            $diamond->culet_size,
            $diamond->culet_condition,
            $diamond->depth_percent,
            $diamond->table_percent,
            $diamond->meas_length,
            $diamond->meas_width,
            $diamond->meas_depth,
            $diamond->girdle_min,
            $diamond->girdle_max,
            $diamond->fluor_color,
            $diamond->fluor_intensity,
            $diamond->fancy_color_dominant_color,
            $diamond->fancy_color_secondary_color,
            $diamond->fancy_color_overtone,
            $diamond->fancy_color_intensity,
            $diamond->total_sales_price,
            $diamond->upload->original_filename ?? 'Unknown',
            $diamond->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function chunkSize(): int
    {
        return 2000; // Process 2000 rows at a time for export
    }
}