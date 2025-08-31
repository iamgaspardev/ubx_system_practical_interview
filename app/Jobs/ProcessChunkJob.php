<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\UploadChunk;
use App\Models\UploadError;
use App\Models\DiamondData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class ProcessChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    protected $upload;
    protected $chunk;

    public function __construct(Upload $upload, UploadChunk $chunk)
    {
        $this->upload = $upload;
        $this->chunk = $chunk;
    }

    public function handle()
    {
        try {
            $this->chunk->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $filePath = Storage::path($this->upload->file_path);
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            $processedRows = 0;
            $successfulRows = 0;
            $failedRows = 0;
            $currentRow = 0;

            $batchData = [];
            $batchSize = 100;

            foreach ($records as $offset => $record) {
                $currentRow = $offset + 1;

                // Skip rows outside this chunk's range
                if ($currentRow < $this->chunk->start_row || $currentRow > $this->chunk->end_row) {
                    continue;
                }

                $processedRows++;

                // Validate and prepare data
                $validatedData = $this->validateAndPrepareRow($record, $currentRow);

                if ($validatedData['valid']) {
                    $batchData[] = array_merge($validatedData['data'], [
                        'upload_id' => $this->upload->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Insert batch when it reaches the batch size
                    if (count($batchData) >= $batchSize) {
                        DiamondData::insert($batchData);
                        $successfulRows += count($batchData);
                        $batchData = [];
                    }
                } else {
                    $failedRows++;
                    $this->logError($currentRow, $validatedData['errors'], $record);
                }
            }

            // Insert remaining batch data
            if (!empty($batchData)) {
                DiamondData::insert($batchData);
                $successfulRows += count($batchData);
            }

            // Update chunk status
            $this->chunk->update([
                'status' => 'completed',
                'processed_rows' => $processedRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => $failedRows,
                'completed_at' => now(),
            ]);

            // Update upload progress
            $this->upload->updateProgress();

            // Check if all chunks are completed
            $this->checkUploadCompletion();

        } catch (\Exception $e) {
            $this->chunk->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    private function validateAndPrepareRow(array $record, int $rowNumber)
    {
        // Define validation rules
        $rules = [
            'cut' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'clarity' => 'nullable|string|max:50',
            'carat_weight' => 'nullable|numeric|min:0|max:50',
            'cut_quality' => 'nullable|string|max:50',
            'lab' => 'nullable|string|max:100',
            'symmetry' => 'nullable|string|max:50',
            'polish' => 'nullable|string|max:50',
            'eye_clean' => 'nullable|string|max:50',
            'culet_size' => 'nullable|string|max:50',
            'culet_condition' => 'nullable|string|max:50',
            'depth_percent' => 'nullable|numeric|min:0|max:100',
            'table_percent' => 'nullable|numeric|min:0|max:100',
            'meas_length' => 'nullable|numeric|min:0',
            'meas_width' => 'nullable|numeric|min:0',
            'meas_depth' => 'nullable|numeric|min:0',
            'girdle_min' => 'nullable|string|max:50',
            'girdle_max' => 'nullable|string|max:50',
            'fluor_color' => 'nullable|string|max:50',
            'fluor_intensity' => 'nullable|string|max:50',
            'fancy_color_dominant_color' => 'nullable|string|max:50',
            'fancy_color_secondary_color' => 'nullable|string|max:50',
            'fancy_color_overtone' => 'nullable|string|max:50',
            'fancy_color_intensity' => 'nullable|string|max:50',
            'total_sales_price' => 'nullable|integer|min:0',
        ];

        // Clean and prepare data
        $cleanRecord = [];
        foreach ($record as $key => $value) {
            // Skip empty keys
            if (trim($key) === '') {
                continue;
            }

            $cleanKey = trim(strtolower(str_replace(' ', '_', $key)));
            $cleanValue = trim($value);

            // Only add non-empty keys that match our expected columns
            $expectedColumns = [
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

            if (in_array($cleanKey, $expectedColumns)) {
                $cleanRecord[$cleanKey] = $cleanValue === '' ? null : $cleanValue;
            }
        }

        $validator = Validator::make($cleanRecord, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
                'data' => null
            ];
        }

        return [
            'valid' => true,
            'errors' => null,
            'data' => $cleanRecord
        ];
    }

    private function logError(int $rowNumber, array $errors, array $rowData)
    {
        foreach ($errors as $error) {
            UploadError::create([
                'upload_id' => $this->upload->id,
                'chunk_id' => $this->chunk->id,
                'row_data' => json_encode($rowData),
                'error_type' => 'validation',
            ]);
        }
    }

    private function checkUploadCompletion()
    {
        $totalChunks = $this->upload->chunks()->count();
        $completedChunks = $this->upload->chunks()->where('status', 'completed')->count();
        $failedChunks = $this->upload->chunks()->where('status', 'failed')->count();

        if (($completedChunks + $failedChunks) === $totalChunks) {
            $status = $failedChunks > 0 ? 'completed' : 'completed';

            $this->upload->update([
                'status' => $status,
                'completed_at' => now(),
                'progress_percentage' => 100,
            ]);
        }
    }

    public function failed(\Exception $exception)
    {
        $this->chunk->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}