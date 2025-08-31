<?php

namespace App\Services;

use App\Models\Upload;
use App\Models\DiamondData;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class BigDataUploadService
{
    public function validateCsvStructure($filePath)
    {
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            $headers = $csv->getHeader();
            $requiredColumns = [
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
                'total_sales_price'
            ];

            $normalizedHeaders = array_map(function ($header) {
                return trim(strtolower(str_replace(' ', '_', $header)));
            }, $headers);

            $missingColumns = array_diff($requiredColumns, $normalizedHeaders);

            if (!empty($missingColumns)) {
                return [
                    'valid' => false,
                    'message' => 'Missing required columns: ' . implode(', ', $missingColumns),
                    'missing_columns' => $missingColumns
                ];
            }

            return ['valid' => true, 'headers' => $normalizedHeaders];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Error reading CSV file: ' . $e->getMessage()
            ];
        }
    }

    public function getUploadStatistics($userId = null)
    {
        $query = Upload::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return [
            'total_uploads' => $query->count(),
            'completed_uploads' => $query->where('status', 'completed')->count(),
            'processing_uploads' => $query->where('status', 'processing')->count(),
            'failed_uploads' => $query->where('status', 'failed')->count(),
            'total_records' => DiamondData::count(),
            'total_file_size' => $query->sum('file_size'),
        ];
    }

    public function cleanupOldUploads($daysOld = 30)
    {
        $oldUploads = Upload::where('created_at', '<', now()->subDays($daysOld))
            ->where('status', 'completed')
            ->get();

        foreach ($oldUploads as $upload) {
            Storage::delete($upload->file_path);
            $upload->delete();
        }

        return $oldUploads->count();
    }
}
