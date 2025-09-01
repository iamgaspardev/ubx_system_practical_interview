<?php

namespace App\Http\Controllers;

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

            if ($recordCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found matching your filters.'
                ], 400);
            }

            Log::info('Export record count', ['count' => $recordCount]);

            // For very large datasets, force CSV export
            if ($recordCount > 25000) {
                return $this->exportCSV($filters, $recordCount);
            }

            // Try Excel first for smaller datasets
            return $this->exportExcel($filters, $recordCount);

        } catch (\Exception $e) {
            Log::error('Export failed', [
                'error' => $e->getMessage(),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ]);

            // If memory error, fall back to CSV
            if (str_contains($e->getMessage(), 'memory')) {
                Log::info('Falling back to CSV due to memory error');
                try {
                    return $this->exportCSV($filters);
                } catch (\Exception $csvError) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Export failed even with CSV fallback: ' . $csvError->getMessage()
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
     * Memory-efficient CSV export using streaming
     */
    private function exportCSV($filters, $recordCount = null)
    {
        Log::info('Starting CSV export');

        // Set conservative limits
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 600);

        $filename = 'diamond_data_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache'
        ];

        return Response::stream(function () use ($filters) {
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

            // Stream data in chunks to avoid memory issues
            $chunkSize = 2000;
            $offset = 0;
            $totalProcessed = 0;

            do {
                // Clear any previous query results from memory
                if ($offset > 0) {
                    \DB::connection()->getPdo()->exec('SELECT 1');
                }

                $diamonds = DiamondData::filter($filters)
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

                // Clear the collection from memory
                unset($diamonds);

                $offset += $chunkSize;

                // Log progress periodically
                if ($totalProcessed % 2000 === 0) {
                    Log::info("CSV export progress: {$totalProcessed} records processed");
                }

            } while ($diamonds->count() === $chunkSize);

            fclose($handle);
            Log::info("CSV export completed: {$totalProcessed} records");

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

        $filename = 'diamond_data_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

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
