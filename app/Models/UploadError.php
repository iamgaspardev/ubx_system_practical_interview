<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadError extends Model
{
    protected $fillable = [
        'upload_id',
        'chunk_id',
        'row_number',
        'column_name',
        'error_message',
        'row_data',
        'error_type'
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }

    public function chunk()
    {
        return $this->belongsTo(UploadChunk::class, 'chunk_id');
    }
}