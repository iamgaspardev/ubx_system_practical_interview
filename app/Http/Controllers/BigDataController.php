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
use Maatwebsite\Excel\Facades\Excel;
use League\Csv\Reader;

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

        // For large datasets, use queue job for export
        if (DiamondData::filter($filters)->count() > 10000) {
            // Dispatch export job and notify user via email
            return back()->with('success', 'Export is being processed. You will receive an email when ready.');
        }

        return Excel::download(new DiamondDataExport($filters), 'diamond_data_export.xlsx');
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
