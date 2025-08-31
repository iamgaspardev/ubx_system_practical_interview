<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'status',
        'error_message',
        'progress_percentage',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chunks()
    {
        return $this->hasMany(UploadChunk::class);
    }

    public function errors()
    {
        return $this->hasMany(UploadError::class);
    }

    public function diamondData()
    {
        return $this->hasMany(DiamondData::class);
    }

    public function updateProgress()
    {
        $totalChunks = $this->chunks()->count();
        $completedChunks = $this->chunks()->where('status', 'completed')->count();

        if ($totalChunks > 0) {
            $this->progress_percentage = ($completedChunks / $totalChunks) * 100;
            $this->processed_records = $this->chunks()->sum('processed_rows');
            $this->successful_records = $this->chunks()->sum('successful_rows');
            $this->failed_records = $this->chunks()->sum('failed_rows');
            $this->save();
        }
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}