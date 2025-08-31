<?php

namespace App\Models;

use App\Models\UploadError;
use Illuminate\Database\Eloquent\Model;

class UploadChunk extends Model
{
    protected $fillable = [
        'upload_id',
        'chunk_number',
        'start_row',
        'end_row',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'error_message',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }

    public function errors()
    {
        return $this->hasMany(UploadError::class, 'chunk_id');
    }
}