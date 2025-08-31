<?php

namespace App\Exports;

use App\Models\DiamondData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DiamondDataExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize, WithStyles, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to fetch diamond data with applied filters
     */
    public function query()
    {
        return DiamondData::filter($this->filters)
            ->with('upload')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Excel column headings - matches all your CSV columns exactly
     */
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
            'Depth Percent (%)',
            'Table Percent (%)',
            'Measurement Length (mm)',
            'Measurement Width (mm)',
            'Measurement Depth (mm)',
            'Girdle Min',
            'Girdle Max',
            'Fluorescence Color',
            'Fluorescence Intensity',
            'Fancy Color Dominant Color',
            'Fancy Color Secondary Color',
            'Fancy Color Overtone',
            'Fancy Color Intensity',
            'Total Sales Price',
            'Upload Filename',
            'Date Added',
            'Last Updated'
        ];
    }

    /**
     * Map each diamond record to Excel row with all columns preserved
     */
    public function map($diamond): array
    {
        return [
            $diamond->id ?? '',
            $diamond->cut ?? '',
            $diamond->color ?? '',
            $diamond->clarity ?? '',
            $diamond->carat_weight ?? '',
            $diamond->cut_quality ?? '',
            $diamond->lab ?? '',
            $diamond->symmetry ?? '',
            $diamond->polish ?? '',
            $diamond->eye_clean ?? '',
            $diamond->culet_size ?? '',
            $diamond->culet_condition ?? '',
            $diamond->depth_percent ?? '',
            $diamond->table_percent ?? '',
            $diamond->meas_length ?? '',
            $diamond->meas_width ?? '',
            $diamond->meas_depth ?? '',
            $diamond->girdle_min ?? '',
            $diamond->girdle_max ?? '',
            $diamond->fluor_color ?? '',
            $diamond->fluor_intensity ?? '',
            $diamond->fancy_color_dominant_color ?? '',
            $diamond->fancy_color_secondary_color ?? '',
            $diamond->fancy_color_overtone ?? '',
            $diamond->fancy_color_intensity ?? '',
            $diamond->total_sales_price ?? '',
            $diamond->upload->original_filename ?? 'Unknown',
            $diamond->created_at ? $diamond->created_at->format('Y-m-d H:i:s') : '',
            $diamond->updated_at ? $diamond->updated_at->format('Y-m-d H:i:s') : ''
        ];
    }

    /**
     * Process data in chunks for memory efficiency with large datasets
     */
    public function chunkSize(): int
    {
        return 1000; // Process 1000 rows at a time to handle large datasets efficiently
    }

    /**
     * Apply professional styling to the Excel file
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row and column
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        return [
            // Style the header row with green theme matching your UI
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '22C55E'], // Green-500 to match your UBX System theme
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '16A34A'], // Darker green for borders
                    ],
                ],
            ],

            // Add borders to all data cells
            "A1:{$highestColumn}{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'E5E7EB'], // Light gray borders
                    ],
                ],
            ],

            // Alternate row coloring for better readability
            "A2:{$highestColumn}{$highestRow}" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'F9FAFB'], // Very light gray
                ],
            ],

            // Right-align numeric columns
            'E:E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]], // Carat Weight
            'M:O' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]], // Depth, Table, Measurements
            'Z:Z' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]], // Total Sales Price
        ];
    }

    /**
     * Set the worksheet title
     */
    public function title(): string
    {
        return 'Diamond Data Export';
    }

    /**
     * Configure the worksheet after it's created
     */
    public function afterSheet($event)
    {
        $sheet = $event->sheet->getDelegate();

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Freeze the header row
        $sheet->freezePane('A2');

        // Set specific column widths for better display
        $columnWidths = [
            'A' => 8,   // ID
            'B' => 12,  // Cut
            'C' => 10,  // Color
            'D' => 12,  // Clarity
            'E' => 14,  // Carat Weight
            'F' => 15,  // Cut Quality
            'G' => 15,  // Lab
            'H' => 12,  // Symmetry
            'I' => 12,  // Polish
            'J' => 12,  // Eye Clean
            'K' => 12,  // Culet Size
            'L' => 15,  // Culet Condition
            'M' => 12,  // Depth %
            'N' => 12,  // Table %
            'O' => 16,  // Meas Length
            'P' => 16,  // Meas Width
            'Q' => 16,  // Meas Depth
            'R' => 12,  // Girdle Min
            'S' => 12,  // Girdle Max
            'T' => 16,  // Fluor Color
            'U' => 18,  // Fluor Intensity
            'V' => 20,  // Fancy Color Dominant
            'W' => 20,  // Fancy Color Secondary
            'X' => 18,  // Fancy Color Overtone
            'Y' => 18,  // Fancy Color Intensity
            'Z' => 16,  // Total Sales Price
            'AA' => 20, // Upload Filename
            'AB' => 18, // Date Added
            'AC' => 18, // Last Updated
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Add filter to header row
        $sheet->setAutoFilter("A1:{$sheet->getHighestColumn()}1");

        // Format price column as currency if there's data
        if ($sheet->getHighestRow() > 1) {
            $sheet->getStyle("Z2:Z{$sheet->getHighestRow()}")
                ->getNumberFormat()
                ->setFormatCode('$#,##0');
        }

        // Format date columns
        if ($sheet->getHighestRow() > 1) {
            $sheet->getStyle("AB2:AC{$sheet->getHighestRow()}")
                ->getNumberFormat()
                ->setFormatCode('yyyy-mm-dd hh:mm:ss');
        }
    }
}