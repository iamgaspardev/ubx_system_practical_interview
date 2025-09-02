<?php

namespace App\Jobs;

use App\Models\DiamondData;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExportLargeDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 2;
    public $maxExceptions = 1;

    protected $user;
    protected $filters;
    protected $userEmail;

    public function __construct(User $user, array $filters = [])
    {
        $this->user = $user;
        $this->filters = $filters;
        $this->userEmail = $user->email;
    }

    public function handle()
    {
        Log::info('Starting large dataset export job', [
            'user_id' => $this->user->id,
            'filters' => $this->filters
        ]);

        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 3600);

        $filename = 'diamond_export_' . $this->user->id . '_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $filepath = 'exports/' . $filename;

        $recordCount = DiamondData::filter($this->filters)->count();
        Log::info('Export job record count', ['count' => $recordCount]);

        // Create the CSV file
        Storage::put($filepath, '');
        $handle = Storage::disk('local')->readStream($filepath);

        if (!$handle) {
            throw new \Exception('Could not create export file');
        }

        // Write BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($handle, [
            'Record ID',
            'Cut',
            'Color Grade',
            'Clarity Grade',
            'Carat Weight',
            'Cut Quality',
            'Lab',
            'Symmetry',
            'Polish',
            'Eye Clean',
            'Culet Size',
            'Culet Condition',
            'Depth %',
            'Table %',
            'Length (mm)',
            'Width (mm)',
            'Depth (mm)',
            'Girdle Min',
            'Girdle Max',
            'Fluor Color',
            'Fluor Intensity',
            'Fancy Dominant',
            'Fancy Secondary',
            'Fancy Overtone',
            'Fancy Intensity',
            'Total Price (USD)',
            'Upload File',
            'Date Added',
            'Last Modified'
        ]);

        $chunkSize = 500; // Very conservative for background processing
        $offset = 0;
        $totalProcessed = 0;

        while ($totalProcessed < $recordCount) {
            $diamonds = DiamondData::filter($this->filters)
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
                ->with('upload:id,original_filename')
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($chunkSize)
                ->get();

            if ($diamonds->isEmpty()) {
                break;
            }

            foreach ($diamonds as $diamond) {
                fputcsv($handle, [
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
                ]);
                $totalProcessed++;
            }

            unset($diamonds);
            $offset += $chunkSize;

            // Log progress
            if ($totalProcessed % 5000 === 0) {
                Log::info("Export job progress: {$totalProcessed}/{$recordCount} records", [
                    'memory_usage' => memory_get_usage(true),
                    'percentage' => round(($totalProcessed / $recordCount) * 100, 2)
                ]);
            }
        }

        fclose($handle);

        // Send email notification
        $downloadUrl = Storage::url($filepath);
        $fileSize = Storage::size($filepath);

        Log::info('Export job completed', [
            'total_processed' => $totalProcessed,
            'file_size' => $fileSize,
            'memory_peak' => memory_get_peak_usage(true)
        ]);

        // Send notification email
        Mail::raw("Your diamond data export is ready for download.\n\nRecords: {$totalProcessed}\nFile: {$filename}\nDownload: " . url($downloadUrl), function ($message) {
            $message->to($this->userEmail)
                ->subject('Diamond Data Export Complete');
        });
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Export job failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
            'memory_peak' => memory_get_peak_usage(true)
        ]);

        Mail::raw("Your diamond data export failed with error: " . $exception->getMessage(), function ($message) {
            $message->to($this->userEmail)
                ->subject('Diamond Data Export Failed');
        });
    }
}