@extends('layouts.app')
@section('title', 'Upload Progress - UBX System')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Upload Progress</h1>
                <p class="text-gray-600 mt-1">{{ $upload->original_filename }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('bigdata.uploads') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200">
                    All Uploads
                </a>
                @if($upload->status === 'completed')
                    <a href="{{ route('bigdata.index') }}"
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl transition-colors duration-200">
                        View Data
                    </a>
                @endif
            </div>
        </div>

        <!-- Progress Card -->
        <div class="bg-white rounded-3xl p-8 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Processing Status</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($upload->status === 'completed') bg-green-50 text-green-700
                                            @elseif($upload->status === 'processing') bg-yellow-50 text-yellow-700
                                            @elseif($upload->status === 'failed') bg-red-50 text-red-700
                                            @else bg-gray-50 text-gray-700 @endif">
                    @if($upload->status === 'completed')
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        Completed
                    @elseif($upload->status === 'processing')
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></div>
                        Processing
                    @elseif($upload->status === 'failed')
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                        Failed
                    @else
                        <div class="w-2 h-2 bg-gray-500 rounded-full mr-2"></div>
                        Uploaded
                    @endif
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                    <span>Progress</span>
                    <span id="progressText">{{ number_format($upload->progress_percentage, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="progressBar" class="bg-green-500 h-3 rounded-full transition-all duration-300"
                        style="width: {{ $upload->progress_percentage }}%"></div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900" id="totalRecords">
                        {{ number_format($upload->total_records ?? 0) }}
                    </div>
                    <div class="text-sm text-gray-500">Total Records</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600" id="successfulRecords">
                        {{ number_format($upload->successful_records) }}
                    </div>
                    <div class="text-sm text-gray-500">Successful</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600" id="failedRecords">
                        {{ number_format($upload->failed_records) }}
                    </div>
                    <div class="text-sm text-gray-500">Failed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600" id="processedRecords">
                        {{ number_format($upload->processed_records) }}
                    </div>
                    <div class="text-sm text-gray-500">Processed</div>
                </div>
            </div>
        </div>

        <!-- Chunks Progress -->
        <div class="bg-white rounded-3xl p-8 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Processing Chunks</h3>
            <div class="space-y-3">
                @foreach($upload->chunks as $chunk)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-3 h-3 rounded-full
                                                                                    @if($chunk->status === 'completed') bg-green-500
                                                                                    @elseif($chunk->status === 'processing') bg-yellow-500 animate-pulse
                                                                                    @elseif($chunk->status === 'failed') bg-red-500
                                                                                    @else bg-gray-400 @endif">
                            </div>
                            <span class="font-medium text-gray-900">
                                Chunk {{ $chunk->chunk_number }}
                            </span>
                            <span class="text-sm text-gray-500">
                                (Rows {{ number_format($chunk->start_row) }} - {{ number_format($chunk->end_row) }})
                            </span>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $chunk->successful_rows }}/{{ $chunk->total_rows }} processed
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($upload->errors->count() > 0)
            <!-- Error Summary -->
            <div class="bg-white rounded-3xl p-8 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Processing Errors</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Row</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Error</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($upload->errors->take(10) as $error)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-900">{{ $error->row_number }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $error->error_message }}</td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                                                                            @if($error->error_type === 'validation') bg-red-50 text-red-700
                                                                                                                            @elseif($error->error_type === 'database') bg-orange-50 text-orange-700
                                                                                                                            @else bg-gray-50 text-gray-700 @endif">
                                            {{ ucfirst($error->error_type) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($upload->errors->count() > 10)
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-500">Showing 10 of {{ number_format($upload->errors->count()) }} errors</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if($upload->status === 'processing')
        <script>
            // Auto-refresh progress for processing uploads
            function updateProgress() {
                fetch(`{{ route('bigdata.progress', $upload) }}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('progressBar').style.width = data.progress + '%';
                        document.getElementById('progressText').textContent = data.progress.toFixed(1) + '%';
                        document.getElementById('totalRecords').textContent = data.total.toLocaleString();
                        document.getElementById('successfulRecords').textContent = data.successful.toLocaleString();
                        document.getElementById('failedRecords').textContent = data.failed.toLocaleString();
                        document.getElementById('processedRecords').textContent = data.processed.toLocaleString();

                        if (data.status === 'completed' || data.status === 'failed') {
                            location.reload();
                        } else {
                            setTimeout(updateProgress, 3000); // Update every 3 seconds
                        }
                    })
                    .catch(error => {
                        console.error('Error updating progress:', error);
                        setTimeout(updateProgress, 5000); // Retry in 5 seconds
                    });
            }

            // Start progress updates
            setTimeout(updateProgress, 2000);
        </script>
    @endif
@endsection