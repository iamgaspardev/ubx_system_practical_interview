<?php

namespace App\Http\Controllers;

use App\Jobs\ExportLargeDataJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\BigDataUploadRequest;
use App\Jobs\ProcessUploadJob;
use App\Models\Upload;
use App\Models\DiamondData;
use App\Exports\DiamondDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use League\Csv\Reader;
use Response;

class BigDataController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $query = DiamondData::with('upload');

        // Apply filters
        $filters = $request->only([
            'cut',
            'color',
            'clarity',
            'min_carat',
            'max_carat',
            'min_price',
            'max_price',
            'lab',
            'search'
        ]);

        $query->filter($filters);

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = [
            'created_at',
            'carat_weight',
            'total_sales_price',
            'cut',
            'color',
            'clarity',
            'lab'
        ];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $diamonds = $query->paginate(50)->withQueryString();

        // Get filter options for dropdowns
        $filterOptions = [
            'cuts' => DiamondData::distinct('cut')->whereNotNull('cut')->pluck('cut')->sort(),
            'colors' => DiamondData::distinct('color')->whereNotNull('color')->pluck('color')->sort(),
            'clarities' => DiamondData::distinct('clarity')->whereNotNull('clarity')->pluck('clarity')->sort(),
            'labs' => DiamondData::distinct('lab')->whereNotNull('lab')->pluck('lab')->sort(),
        ];

        return view('bigdata.index', compact('diamonds', 'filterOptions', 'filters'));
    }

    public function create()
    {
        return view('bigdata.upload');
    }

    public function store(BigDataUploadRequest $request)
    {
        $file = $request->file('csv_file');

        // Store file
        $filename = uniqid('upload_') . '.csv';
        $path = $file->storeAs('uploads', $filename, 'local');

        // Create upload record
        $upload = Upload::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        // Dispatch processing job
        ProcessUploadJob::dispatch($upload);

        return redirect()->route('bigdata.show', $upload)
            ->with('success', 'File uploaded successfully! Processing will begin shortly.');
    }

    public function show(Upload $upload)
    {
        $this->authorize('view', $upload);

        $upload->load(['chunks', 'errors']);

        // Get processing statistics
        $stats = [
            'total_chunks' => $upload->chunks()->count(),
            'completed_chunks' => $upload->chunks()->where('status', 'completed')->count(),
            'failed_chunks' => $upload->chunks()->where('status', 'failed')->count(),
            'processing_chunks' => $upload->chunks()->where('status', 'processing')->count(),
        ];

        return view('bigdata.show', compact('upload', 'stats'));
    }

    public function uploads()
    {
        $uploads = auth()->user()->uploads()
            ->latest()
            ->paginate(20);

        return view('bigdata.uploads', compact('uploads'));
    }

    public function export(Request $request)
    {
        Log::info('Starting memory-optimized export', [
            'user_id' => auth()->id(),
            'memory_before' => memory_get_usage(true),
            'memory_limit' => ini_get('memory_limit')
        ]);

        try {
            $filters = $request->only([
                'cut',
                'color',
                'clarity',
                'min_carat',
                'max_carat',
                'min_price',
                'max_price',
                'lab',
                'search'
            ]);

            // Check record count first
            $recordCount = DiamondData::filter($filters)->count();
            Log::info('Export record count', ['count' => $recordCount]);

            if ($recordCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found matching your filters.'
                ], 400);
            }

            // For extremely large datasets (100k+), use background job
            if ($recordCount > 95000) {
                // Create export job with progress tracking
                $jobId = uniqid('export_');
                // 1 hour cache
                cache()->put("export_job_{$jobId}", [
                    'status' => 'queued',
                    'total_records' => $recordCount,
                    'processed_records' => 0,
                    'user_id' => auth()->id(),
                    'started_at' => now()
                ], 3600);

                ExportLargeDataJob::dispatch(auth()->user(), $filters, $jobId);

                return response()->json([
                    'success' => true,
                    'message' => 'Export started! Processing ' . number_format($recordCount) . ' records in background.',
                    'queued' => true,
                    'job_id' => $jobId,
                    'estimated_time' => 'This may take 30-60 minutes for ' . number_format($recordCount) . ' records.'
                ], 202);
            }

            // For very large datasets (50k-100k), force CSV with immediate response
            if ($recordCount > 50000) {
                return $this->exportCSV($filters, $recordCount);
            }

            // For large datasets (25k-50k), try CSV first
            if ($recordCount > 25000) {
                return $this->exportCSV($filters, $recordCount);
            }

            // Try Excel for smaller datasets
            return $this->exportExcel($filters, $recordCount);

        } catch (\Exception $e) {
            Log::error('Export failed', [
                'error' => $e->getMessage(),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ]);

            // If memory error, fall back to CSV or queue
            if (str_contains($e->getMessage(), 'memory')) {
                Log::info('Memory error detected, checking fallback options');

                $recordCount = $recordCount ?? DiamondData::filter($filters)->count();

                // For very large datasets, queue the job
                if ($recordCount > 75000) {
                    $jobId = uniqid('export_');
                    cache()->put("export_job_{$jobId}", [
                        'status' => 'queued',
                        'total_records' => $recordCount,
                        'processed_records' => 0,
                        'user_id' => auth()->id(),
                        'started_at' => now()
                    ], 3600);

                    ExportLargeDataJob::dispatch(auth()->user(), $filters, $jobId);

                    return response()->json([
                        'success' => true,
                        'message' => 'Dataset too large for immediate export. Job queued - you will receive an email when complete.',
                        'queued' => true,
                        'job_id' => $jobId
                    ], 202);
                }

                // Try CSV fallback for smaller datasets
                try {
                    return $this->exportCSV($filters, $recordCount);
                } catch (\Exception $csvError) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Export failed even with CSV fallback. Try applying more specific filters to reduce the dataset size.'
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memory-efficient CSV export 
     */
    private function exportCSV($filters, $recordCount = null)
    {
        Log::info('Starting cursor-based CSV export');

        if ($recordCount === null) {
            $recordCount = DiamondData::filter($filters)->count();
            Log::info('Export record count', ['count' => $recordCount]);
        }

        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 3600);

        $filename = 'data_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache'
        ];

        return Response::stream(function () use ($filters, $recordCount) {
            $handle = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
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

            $chunkSize = 500;
            $totalProcessed = 0;
            $lastId = 0;

            // Use cursor-based pagination instead of offset/limit
            do {
                if ($totalProcessed > 0 && $totalProcessed % 10000 === 0) {
                    gc_collect_cycles();
                }

                $query = DiamondData::filter($filters)
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
                    ->limit($chunkSize);

                $diamonds = $query->get();
                $currentChunkSize = $diamonds->count();

                if ($currentChunkSize === 0) {
                    Log::info("No more records found after ID {$lastId}");
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
                    $lastId = $diamond->id;

                    // Flush output periodically
                    if ($totalProcessed % 100 === 0) {
                        fflush($handle);
                    }
                }

                // Clear memory
                unset($diamonds);

                // Log progress
                if ($totalProcessed % 5000 === 0) {
                    Log::info("CSV export progress: {$totalProcessed}/{$recordCount} records", [
                        'last_id' => $lastId,
                        'memory_usage' => memory_get_usage(true),
                        'percentage' => round(($totalProcessed / $recordCount) * 100, 2)
                    ]);
                }

            } while ($currentChunkSize === $chunkSize && $totalProcessed < $recordCount);

            fclose($handle);

            Log::info("CSV export completed", [
                'total_processed' => $totalProcessed,
                'target_count' => $recordCount,
                'last_id' => $lastId,
                'memory_peak' => memory_get_peak_usage(true)
            ]);

        }, 200, $headers);
    }

    /**
     * Excel export for smaller datasets
     */
    private function exportExcel($filters, $recordCount)
    {
        Log::info('Starting Excel export', ['record_count' => $recordCount]);

        // Set memory limits based on record count
        if ($recordCount > 15000) {
            ini_set('memory_limit', '1024M');
        } elseif ($recordCount > 5000) {
            ini_set('memory_limit', '512M');
        } else {
            ini_set('memory_limit', '256M');
        }

        ini_set('max_execution_time', 300);

        $filename = 'data_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return Excel::download(
            new DiamondDataExport($filters),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache'
            ]
        );
    }

    /**
     * Download export file with proper streaming
     */
    public function downloadExport($filename)
    {
        $userId = auth()->id();

        // Security: Check if filename contains user ID (more flexible pattern matching)
        if (!preg_match('/_(.*_)?' . $userId . '_/', $filename)) {
            Log::warning('Unauthorized export download attempt', [
                'filename' => $filename,
                'user_id' => $userId,
                'ip' => request()->ip()
            ]);
            abort(403, 'Unauthorized access to export file.');
        }

        // Check both possible locations
        $publicPath = storage_path('app/public/exports/' . $filename);
        $privatePath = storage_path('app/exports/' . $filename);

        $fullPath = null;
        if (file_exists($publicPath)) {
            $fullPath = $publicPath;
        } elseif (file_exists($privatePath)) {
            $fullPath = $privatePath;
        }

        if (!$fullPath || !file_exists($fullPath)) {
            // Log::warning('Export file not found', [
            //     'filename' => $filename,
            //     'public_path' => $publicPath,
            //     'private_path' => $privatePath,
            //     'user_id' => $userId,
            //     'public_exists' => file_exists($publicPath),
            //     'private_exists' => file_exists($privatePath),
            //     'public_dir_contents' => is_dir(dirname($publicPath)) ? scandir(dirname($publicPath)) : 'Directory not found',
            //     'private_dir_contents' => is_dir(dirname($privatePath)) ? scandir(dirname($privatePath)) : 'Directory not found'
            // ]);
            abort(404, 'Export file not found or has expired.');
        }

        $fileSize = filesize($fullPath);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);

        Log::info('Serving export file', [
            'filename' => $filename,
            'path' => $fullPath,
            'size_mb' => $fileSizeMB,
            'user_id' => $userId
        ]);

        // filename with data_export prefix for user-friendly naming
        $displayFilename = str_replace('diamond_export_', 'data_export_', $filename);

        // For large files, response()->file() for better memory efficiency
        if ($fileSize > 50 * 1024 * 1024) {
            return response()->file($fullPath, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $displayFilename . '"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ]);
        }

        // For smaller files, use download method
        return response()->download($fullPath, $displayFilename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Check export status and get download link
     */
    public function checkExportStatus(Request $request)
    {
        $userId = auth()->id();

        // Clear cache if requested (POST request)
        if ($request->isMethod('post') && $request->has('clear_cache')) {
            cache()->forget("export_ready_{$userId}");

            // Also clear any job progress caches
            $jobId = $request->get('job_id');
            if ($jobId) {
                cache()->forget("export_job_{$jobId}");
            }

            return response()->json(['status' => 'cache_cleared']);
        }

        // Check for job progress if job_id provided
        $jobId = $request->get('job_id');
        if ($jobId) {
            $jobProgress = cache()->get("export_job_{$jobId}");
            if ($jobProgress) {
                return response()->json([
                    'status' => $jobProgress['status'],
                    'progress' => [
                        'total_records' => $jobProgress['total_records'],
                        'processed_records' => $jobProgress['processed_records'] ?? 0,
                        'percentage' => $jobProgress['total_records'] > 0
                            ? round(($jobProgress['processed_records'] ?? 0) / $jobProgress['total_records'] * 100, 1)
                            : 0
                    ],
                    'started_at' => $jobProgress['started_at'] ?? null
                ]);
            }
        }

        // Check cache for recent completed exports
        $cachedExport = cache()->get("export_ready_{$userId}");

        if ($cachedExport) {

            return response()->json([
                'status' => 'ready',
                'filename' => $cachedExport['filename'],
                'file_size' => $cachedExport['file_size_mb'] . ' MB',
                'total_records' => number_format($cachedExport['total_records']),
                'created_at' => $cachedExport['created_at'],
                'download_url' => $cachedExport['download_url'],
                'is_recent' => true
            ]);
        }

        return response()->json([
            'status' => 'not_ready',
            'message' => 'Export not ready yet.'
        ]);
    }

    /**
     * Get export job progress
     */
    public function getExportProgress(Request $request)
    {
        $jobId = $request->get('job_id');
        if (!$jobId) {
            return response()->json(['error' => 'Job ID required'], 400);
        }

        $progress = cache()->get("export_job_{$jobId}");

        if (!$progress) {
            return response()->json(['error' => 'Job not found or expired'], 404);
        }

        // Ensure user can only see their own job progress
        if ($progress['user_id'] !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => $progress['status'],
            'total_records' => $progress['total_records'],
            'processed_records' => $progress['processed_records'] ?? 0,
            'percentage' => $progress['total_records'] > 0
                ? round(($progress['processed_records'] ?? 0) / $progress['total_records'] * 100, 1)
                : 0,
            'started_at' => $progress['started_at'],
            'estimated_completion' => $progress['estimated_completion'] ?? null
        ]);
    }

    public function getProgress(Upload $upload)
    {
        $this->authorize('view', $upload);

        $upload->updateProgress();

        return response()->json([
            'progress' => $upload->progress_percentage,
            'status' => $upload->status,
            'processed' => $upload->processed_records,
            'successful' => $upload->successful_records,
            'failed' => $upload->failed_records,
            'total' => $upload->total_records,
        ]);
    }
    public function destroy(Upload $upload)
    {
        $this->authorize('delete', $upload);

        // Delete file
        Storage::delete($upload->file_path);

        // Delete record (cascades to related data)
        $upload->delete();

        return back()->with('success', 'Upload deleted successfully.');
    }

    public function getDiamondDetails(DiamondData $diamond)
    {
        $this->authorize('view', $diamond);

        return response()->json([
            'id' => $diamond->id,
            'cut' => $diamond->cut,
            'color' => $diamond->color,
            'clarity' => $diamond->clarity,
            'carat_weight' => $diamond->carat_weight,
            'cut_quality' => $diamond->cut_quality,
            'lab' => $diamond->lab,
            'symmetry' => $diamond->symmetry,
            'polish' => $diamond->polish,
            'eye_clean' => $diamond->eye_clean,
            'culet_size' => $diamond->culet_size,
            'culet_condition' => $diamond->culet_condition,
            'depth_percent' => $diamond->depth_percent,
            'table_percent' => $diamond->table_percent,
            'meas_length' => $diamond->meas_length,
            'meas_width' => $diamond->meas_width,
            'meas_depth' => $diamond->meas_depth,
            'girdle_min' => $diamond->girdle_min,
            'girdle_max' => $diamond->girdle_max,
            'fluor_color' => $diamond->fluor_color,
            'fluor_intensity' => $diamond->fluor_intensity,
            'fancy_color_dominant_color' => $diamond->fancy_color_dominant_color,
            'fancy_color_secondary_color' => $diamond->fancy_color_secondary_color,
            'fancy_color_overtone' => $diamond->fancy_color_overtone,
            'fancy_color_intensity' => $diamond->fancy_color_intensity,
            'total_sales_price' => $diamond->total_sales_price,
            'created_at' => $diamond->created_at->format('M j, Y g:i A'),
        ]);
    }
}
