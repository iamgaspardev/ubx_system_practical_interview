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
    protected $jobId;

    public function __construct(User $user, array $filters = [], $jobId = null)
    {
        $this->user = $user;
        $this->filters = $filters;
        $this->userEmail = $user->email;
        $this->jobId = $jobId ?: uniqid('export_');

        // Set queue name for high priority
        $this->onQueue('exports');
    }

    public function handle()
    {
        // Log::info('Starting large dataset export job', [
        //     'user_id' => $this->user->id,
        //     'job_id' => $this->jobId,
        //     'filters' => $this->filters
        // ]);

        // Update job status to processing
        $this->updateJobProgress('processing', 0);

        // Configure MySQL connection to use buffered queries
        config([
            'database.connections.mysql.options' => [
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ]
        ]);

        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 3600);

        $filename = 'diamond_export_' . $this->user->id . '_' . now()->format('Y_m_d_H_i_s') . '.csv';

        // Store in both locations for compatibility
        $publicPath = 'public/exports/' . $filename;
        $privatePath = 'exports/' . $filename;

        $recordCount = DiamondData::filter($this->filters)->count();
        Log::info('Export job record count', ['count' => $recordCount, 'job_id' => $this->jobId]);

        // Update total records in job progress
        $this->updateJobProgress('processing', 0, $recordCount);

        // Create the file in public storage for direct access
        $tempPath = storage_path('app/' . $publicPath);

        // Ensure directory exists
        $directory = dirname($tempPath);
        if (!is_dir($directory)) {
            Log::info('Creating export directory', ['directory' => $directory]);
            mkdir($directory, 0755, true);
        }

        Log::info('Creating export file', [
            'filename' => $filename,
            'temp_path' => $tempPath,
            'directory_exists' => is_dir($directory),
            'directory_writable' => is_writable($directory),
        ]);

        $stream = fopen($tempPath, 'w');

        if (!$stream) {
            throw new \Exception('Could not create export file at: ' . $tempPath);
        }

        // Write BOM for Excel compatibility
        fwrite($stream, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($stream, [
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

        $chunkSize = 500;
        $totalProcessed = 0;
        $lastId = 0;

        // Use cursor-based pagination like the working direct export
        do {
            // Force garbage collection periodically
            if ($totalProcessed > 0 && $totalProcessed % 10000 === 0) {
                gc_collect_cycles();
                $this->updateJobProgress('processing', $totalProcessed, $recordCount);
            }

            try {
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
                    ->where('id', '>', $lastId)
                    ->orderBy('id', 'asc')
                    ->limit($chunkSize)
                    ->get();

                $currentChunkSize = $diamonds->count();

                if ($currentChunkSize === 0) {
                    Log::info("No more records found after ID {$lastId}");
                    break;
                }

                foreach ($diamonds as $diamond) {
                    fputcsv($stream, [
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
                    $lastId = $diamond->id;
                }

                // Clear memory
                unset($diamonds);

                // Update progress every 2500 records
                if ($totalProcessed % 2500 === 0) {
                    $this->updateJobProgress('processing', $totalProcessed, $recordCount);
                }

                // Log progress
                if ($totalProcessed % 5000 === 0) {
                    // Log::info("Export job progress: {$totalProcessed}/{$recordCount} records", [
                    //     'job_id' => $this->jobId,
                    //     'last_id' => $lastId,
                    //     'memory_usage' => memory_get_usage(true),
                    //     'percentage' => round(($totalProcessed / $recordCount) * 100, 2)
                    // ]);
                }

            } catch (\Exception $e) {
                Log::error("Error in export job chunk", [
                    'job_id' => $this->jobId,
                    'last_id' => $lastId,
                    'total_processed' => $totalProcessed,
                    'error' => $e->getMessage()
                ]);

                // continue with next chunk by incrementing lastId
                $lastId += $chunkSize;
                continue;
            }

        } while ($currentChunkSize === $chunkSize && $totalProcessed < $recordCount);

        fclose($stream);

        // Verify file was created successfully
        if (!file_exists($tempPath)) {
            throw new \Exception('Export file was not created successfully at: ' . $tempPath);
        }

        $fileSize = filesize($tempPath);
        if ($fileSize === 0) {
            throw new \Exception('Export file is empty: ' . $tempPath);
        }

        Log::info('Export file created successfully', [
            'filename' => $filename,
            'path' => $tempPath,
            'file_size' => $fileSize,
            'file_exists' => file_exists($tempPath),
            'file_readable' => is_readable($tempPath),
        ]);

        // Move file to private storage for backup
        try {
            Storage::copy($publicPath, $privatePath);
            Log::info('Backup copy created', ['private_path' => $privatePath]);
        } catch (\Exception $e) {
            Log::warning('Could not create backup copy', ['error' => $e->getMessage()]);
        }

        // Get file info
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);

        // Generate CORRECT download URL using the route
        $downloadUrl = route('bigdata.download-export', $filename);

        $exportData = [
            'filename' => $filename,
            'file_size_mb' => $fileSizeMB,
            'total_records' => $totalProcessed,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'download_url' => $downloadUrl,
            'completed_at' => time(),
            'job_id' => $this->jobId
        ];

        // Cache for 30 minutes so frontend can pick it up
        cache()->put("export_ready_{$this->user->id}", $exportData, 1800);

        // Mark job as completed with final progress update
        $this->updateJobProgress('completed', $totalProcessed, $recordCount);


        // Send notification email
        // $displayFilename = str_replace('diamond_export_', 'data_export_', $filename);

        // $emailMessage = "Your diamond data export has been completed!\n\n";
        // $emailMessage .= "Export Details:\n";
        // $emailMessage .= "- Records exported: " . number_format($totalProcessed) . "\n";
        // $emailMessage .= "- File size: {$fileSizeMB} MB\n";
        // $emailMessage .= "- Filename: {$displayFilename}\n\n";
        // $emailMessage .= "Download your file here: " . $downloadUrl . "\n\n";
        // $emailMessage .= "Note: This download link will be available for 7 days.";

        // Mail::raw($emailMessage, function ($message) use ($displayFilename) {
        //     $message->to($this->userEmail)
        //         ->subject('Diamond Data Export Complete - ' . $displayFilename);
        // });

        Log::info('Export job completed and notifications sent', [
            'job_id' => $this->jobId,
            'download_url' => $downloadUrl,
            'cache_key' => "export_ready_{$this->user->id}"
        ]);
    }

    protected function updateJobProgress($status, $processedRecords, $totalRecords = null)
    {
        $progressData = [
            'status' => $status,
            'processed_records' => $processedRecords,
            'user_id' => $this->user->id,
            'job_id' => $this->jobId,
            'updated_at' => now()
        ];

        if ($totalRecords !== null) {
            $progressData['total_records'] = $totalRecords;

            // Calculate estimated completion time
            if ($processedRecords > 0 && $status === 'processing') {
                $existingData = cache()->get("export_job_{$this->jobId}", []);
                $startedAt = $existingData['started_at'] ?? now();

                $elapsed = now()->diffInSeconds($startedAt);
                $rate = $processedRecords / max($elapsed, 1);
                $remaining = $totalRecords - $processedRecords;
                $estimatedSeconds = $remaining / max($rate, 1);
                $progressData['estimated_completion'] = now()->addSeconds($estimatedSeconds);
            }
        }

        // Get existing data to preserve started_at
        $existingData = cache()->get("export_job_{$this->jobId}", []);
        $progressData = array_merge($existingData, $progressData);

        if (!isset($progressData['started_at'])) {
            $progressData['started_at'] = now();
        }

        cache()->put("export_job_{$this->jobId}", $progressData, 3600);


    }

    public function failed(\Throwable $exception)
    {
        Log::error('Export job failed', [
            'user_id' => $this->user->id,
            'job_id' => $this->jobId,
            'filters' => $this->filters,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'memory_peak' => memory_get_peak_usage(true)
        ]);

        // Update job status to failed
        $this->updateJobProgress('failed', 0);

        $errorMessage = "Your diamond data export job has failed.\n\n";
        $errorMessage .= "Error: " . $exception->getMessage() . "\n\n";
        $errorMessage .= "Please try again with more specific filters to reduce the dataset size, ";
        $errorMessage .= "or contact support if the problem persists.";

        Mail::raw($errorMessage, function ($message) {
            $message->to($this->userEmail)
                ->subject('Diamond Data Export Failed');
        });
    }
}