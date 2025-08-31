<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BigDataUploadRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:102400', // 100MB max
            ]
        ];
    }

    public function messages()
    {
        return [
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.mimes' => 'Only CSV files are allowed.',
            'csv_file.max' => 'File size must not exceed 100MB.',
        ];
    }
}
