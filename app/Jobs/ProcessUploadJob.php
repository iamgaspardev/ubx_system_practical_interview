<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\UploadChunk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class ProcessUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function handle()
    {
        try {
            $this->upload->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Read CSV file to count total records and create chunks
            $filePath = Storage::path($this->upload->file_path);
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            // Count total records
            $totalRecords = iterator_count($csv->getRecords());

            $this->upload->update(['total_records' => $totalRecords]);

            // Create chunks (2000 records per chunk)
            $chunkSize = 2000;
            $totalChunks = ceil($totalRecords / $chunkSize);

            for ($i = 0; $i < $totalChunks; $i++) {
                $startRow = ($i * $chunkSize) + 1; // +1 for header
                $endRow = min(($i + 1) * $chunkSize, $totalRecords);

                $chunk = UploadChunk::create([
                    'upload_id' => $this->upload->id,
                    'chunk_number' => $i + 1,
                    'start_row' => $startRow,
                    'end_row' => $endRow,
                    'total_rows' => $endRow - $startRow + 1,
                    'status' => 'pending',
                ]);

                // Dispatch chunk processing job
                ProcessChunkJob::dispatch($this->upload, $chunk);
            }

        } catch (\Exception $e) {
            $this->upload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        $this->upload->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}