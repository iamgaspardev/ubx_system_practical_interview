@extends('layouts.app')

@section('title', 'Upload Big Data - UBX System')

@section('content')
    <div class="max-w-8xl mx-auto space-y-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Upload Big Data</h1>
                <p class="text-gray-600 mt-1">Upload CSV files with up to 100,000+ records</p>
            </div>
            <a href="{{ route('bigdata.uploads') }}"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                View Upload History
            </a>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-3xl p-8 border border-gray-100">
            <form method="POST" action="{{ route('bigdata.store') }}" enctype="multipart/form-data" class="space-y-6"
                id="uploadForm">
                @csrf

                <!-- File Upload Area -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">Select CSV File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-3xl p-8 text-center hover:border-green-400 transition-colors duration-200"
                        id="dropZone">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <div class="space-y-2">
                            <p class="text-xl font-medium text-gray-900">Drag and drop your CSV file here</p>
                            <p class="text-gray-500">or click to browse files</p>
                        </div>
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="hidden" id="fileInput">
                        <button type="button" onclick="document.getElementById('fileInput').click()"
                            class="mt-4 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-2xl font-medium transition-colors duration-200">
                            Choose File
                        </button>
                    </div>
                    @error('csv_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Information --->
                <div id="fileInfo" class="hidden bg-gray-50 rounded-2xl p-6">
                    <h3 class="font-medium text-gray-900 mb-3">File Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Filename:</span>
                            <span id="fileName" class="ml-2 font-medium text-gray-900"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Size:</span>
                            <span id="fileSize" class="ml-2 font-medium text-gray-900"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Type:</span>
                            <span id="fileType" class="ml-2 font-medium text-gray-900">CSV</span>
                        </div>
                    </div>
                </div>

                <!-- Expected CSV Format -->
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                    <h3 class="font-medium  mb-3">Expected CSV Format</h3>
                    <p class=" text-sm mb-3">Your CSV should contain the following columns (in any order):</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                        <span class="bg-white px-2 py-1 rounded">cut</span>
                        <span class="bg-white px-2 py-1 rounded">color</span>
                        <span class="bg-white px-2 py-1 rounded">clarity</span>
                        <span class="bg-white px-2 py-1 rounded">carat_weight</span>
                        <span class="bg-white px-2 py-1 rounded">cut_quality</span>
                        <span class="bg-white px-2 py-1 rounded">lab</span>
                        <span class="bg-white px-2 py-1 rounded">symmetry</span>
                        <span class="bg-white px-2 py-1 rounded">polish</span>
                        <span class="bg-white px-2 py-1 rounded">eye_clean</span>
                        <span class="bg-white px-2 py-1 rounded">culet_size</span>
                        <span class="bg-white px-2 py-1 rounded">culet_condition</span>
                        <span class="bg-white px-2 py-1 rounded">depth_percent</span>
                        <span class="bg-white px-2 py-1 rounded">table_percent</span>
                        <span class="bg-white px-2 py-1 rounded">meas_length</span>
                        <span class="bg-white px-2 py-1 rounded">meas_width</span>
                        <span class="bg-white px-2 py-1 rounded">meas_depth</span>
                        <span class="bg-white px-2 py-1 rounded">girdle_min</span>
                        <span class="bg-white px-2 py-1 rounded">girdle_max</span>
                        <span class="bg-white px-2 py-1 rounded">fluor_color</span>
                        <span class="bg-white px-2 py-1 rounded">fluor_intensity</span>
                        <span class="bg-white px-2 py-1 rounded">fancy_color_dominant_color</span>
                        <span class="bg-white px-2 py-1 rounded">fancy_color_secondary_color</span>
                        <span class="bg-white px-2 py-1 rounded">fancy_color_overtone</span>
                        <span class="bg-white px-2 py-1 rounded">fancy_color_intensity</span>
                        <span class="bg-white px-2 py-1 rounded">total_sales_price</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('dashboard') }}"
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-2xl transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" id="submitButton"
                        class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-2xl transition-colors duration-200 focus:ring-4 focus:ring-green-200 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                        Upload and Process
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('fileInput');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const submitButton = document.getElementById('submitButton');
            const dropZone = document.getElementById('dropZone');

            // Handle file selection
            fileInput.addEventListener('change', handleFileSelect);

            // Handle drag and drop
            dropZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                dropZone.classList.add('border-green-400', 'bg-green-50');
            });

            dropZone.addEventListener('dragleave', function (e) {
                e.preventDefault();
                dropZone.classList.remove('border-green-400', 'bg-green-50');
            });

            dropZone.addEventListener('drop', function (e) {
                e.preventDefault();
                dropZone.classList.remove('border-green-400', 'bg-green-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect();
                }
            });

            function handleFileSelect() {
                const file = fileInput.files[0];
                if (file) {
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    fileInfo.classList.remove('hidden');
                    submitButton.disabled = false;
                } else {
                    fileInfo.classList.add('hidden');
                    submitButton.disabled = true;
                }
            }

            function formatFileSize(bytes) {
                if (bytes >= 1073741824) {
                    return (bytes / 1073741824).toFixed(2) + ' GB';
                } else if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(2) + ' MB';
                } else if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(2) + ' KB';
                }
                return bytes + ' bytes';
            }
        });
    </script>
@endsection