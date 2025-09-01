<?php

namespace App\Exports;

use App\Models\DiamondData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;

class DiamondDataExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithTitle, WithEvents
{
    protected $filters;
    protected $recordCount;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        // Get record count for optimization decisions
        $this->recordCount = DiamondData::filter($filters)->count();

        Log::info('DiamondDataExport initialized', [
            'record_count' => $this->recordCount,
            'filters' => $filters
        ]);
    }

    public function query()
    {
        Log::info('Building export query');

        // Use minimal select to reduce memory usage
        return DiamondData::filter($this->filters)
            ->select([
                'id',
                'cut',
                'color',
                'clarity',
                'carat_weight',
                'cut_quality',
                'lab',
                'symmetry',
                'polish',
                'eye_clean',
                'culet_size',
                'culet_condition',
                'depth_percent',
                'table_percent',
                'meas_length',
                'meas_width',
                'meas_depth',
                'girdle_min',
                'girdle_max',
                'fluor_color',
                'fluor_intensity',
                'fancy_color_dominant_color',
                'fancy_color_secondary_color',
                'fancy_color_overtone',
                'fancy_color_intensity',
                'total_sales_price',
                'upload_id',
                'created_at',
                'updated_at'
            ])
            ->with([
                'upload' => function ($query) {
                    $query->select('id', 'original_filename');
                }
            ])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Record ID',
            'Cut',
            'Color Grade',
            'Clarity Grade',
            'Carat Weight',
            'Cut Quality',
            'Certification Lab',
            'Symmetry',
            'Polish',
            'Eye Clean',
            'Culet Size',
            'Culet Condition',
            'Depth Percentage',
            'Table Percentage',
            'Length (mm)',
            'Width (mm)',
            'Depth (mm)',
            'Girdle Minimum',
            'Girdle Maximum',
            'Fluorescence Color',
            'Fluorescence Intensity',
            'Fancy Color - Dominant',
            'Fancy Color - Secondary',
            'Fancy Color - Overtone',
            'Fancy Color - Intensity',
            'Total Sales Price (USD)',
            'Source Upload File',
            'Date Added',
            'Last Modified'
        ];
    }

    public function map($diamond): array
    {
        // Optimized mapping with minimal processing
        static $rowCount = 0;
        $rowCount++;

        // Log progress every 1000 rows to monitor memory usage
        if ($rowCount % 1000 === 0) {
            Log::info("Export progress: {$rowCount} rows processed", [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ]);
        }

        return [
            $diamond->id,
            $diamond->cut ?: 'N/A',
            $diamond->color ?: 'N/A',
            $diamond->clarity ?: 'N/A',
            $diamond->carat_weight ?: '',
            $diamond->cut_quality ?: 'N/A',
            $diamond->lab ?: 'N/A',
            $diamond->symmetry ?: 'N/A',
            $diamond->polish ?: 'N/A',
            $diamond->eye_clean ?: 'N/A',
            $diamond->culet_size ?: 'N/A',
            $diamond->culet_condition ?: 'N/A',
            $diamond->depth_percent ?: '',
            $diamond->table_percent ?: '',
            $diamond->meas_length ?: '',
            $diamond->meas_width ?: '',
            $diamond->meas_depth ?: '',
            $diamond->girdle_min ?: 'N/A',
            $diamond->girdle_max ?: 'N/A',
            $diamond->fluor_color ?: 'N/A',
            $diamond->fluor_intensity ?: 'N/A',
            $diamond->fancy_color_dominant_color ?: 'N/A',
            $diamond->fancy_color_secondary_color ?: 'N/A',
            $diamond->fancy_color_overtone ?: 'N/A',
            $diamond->fancy_color_intensity ?: 'N/A',
            $diamond->total_sales_price ?: 0,
            $diamond->upload?->original_filename ?: 'Unknown',
            $diamond->created_at?->format('Y-m-d H:i:s') ?: '',
            $diamond->updated_at?->format('Y-m-d H:i:s') ?: ''
        ];
    }

    public function chunkSize(): int
    {
        // Very conservative chunk sizes for memory efficiency
        if ($this->recordCount > 50000) {
            return 100;  // Very small chunks for huge datasets
        } elseif ($this->recordCount > 25000) {
            return 250;
        } elseif ($this->recordCount > 10000) {
            return 500;
        } else {
            return 1000;
        }
    }

    public function title(): string
    {
        return 'Diamond Data Export';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                Log::info('Applying sheet formatting', ['rows' => $highestRow]);

                try {
                    // Only apply minimal formatting to avoid memory issues
                    $sheet->getRowDimension(1)->setRowHeight(25);
                    $sheet->freezePane('A2');

                    // Only auto-size columns for smaller datasets
                    if ($this->recordCount <= 5000) {
                        $sheet->getColumnDimension('A')->setAutoSize(true);
                        $sheet->getColumnDimension('B')->setAutoSize(true);
                        $sheet->getColumnDimension('C')->setAutoSize(true);
                        $sheet->getColumnDimension('Z')->setAutoSize(true); // Price column
                    } else {
                        // Set fixed widths for large datasets
                        $sheet->getColumnDimension('A')->setWidth(10);
                        $sheet->getColumnDimension('B')->setWidth(12);
                        $sheet->getColumnDimension('C')->setWidth(10);
                        $sheet->getColumnDimension('Z')->setWidth(16);
                    }

                    // Add basic header styling only
                    $sheet->getStyle('A1:AC1')->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => '22C55E']
                        ]
                    ]);

                    Log::info('Sheet formatting completed successfully');

                } catch (\Exception $e) {
                    Log::warning('Sheet formatting failed, continuing without formatting', [
                        'error' => $e->getMessage()
                    ]);
                }
            },
        ];
    }
}