{{-- resources/views/bigdata/uploads.blade.php --}}
@extends('layouts.app')

@section('title', 'Upload History - UBX System')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Upload History</h1>
                <p class="text-gray-600 mt-1">View and manage your data uploads</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('bigdata.index') }}"
                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-2xl transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    View Data
                </a>
                <a href="{{ route('bigdata.create') }}"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Upload
                </a>
            </div>
        </div>

        <!-- Uploads Table -->
        <div class="bg-white rounded-3xl border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-900">Your Uploads</h2>
                <p class="text-gray-600 text-sm mt-1">{{ $uploads->total() }} total uploads</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">File
                                Name</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Size
                            </th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Records</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Upload Date</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($uploads as $upload)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-50 rounded-2xl flex items-center justify-center mr-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $upload->original_filename }}</p>
                                            <p class="text-sm text-gray-500">{{ $upload->filename }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-600">{{ $upload->formatted_file_size }}</td>
                                <td class="py-4 px-6">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900">{{ number_format($upload->total_records ?? 0) }}
                                        </div>
                                        @if($upload->total_records)
                                            <div class="text-gray-500">
                                                <span class="text-green-600">{{ number_format($upload->successful_records) }}</span>
                                                success /
                                                <span class="text-red-600">{{ number_format($upload->failed_records) }}</span>
                                                failed
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                    @if($upload->status === 'completed') bg-green-50 text-green-700
                                                    @elseif($upload->status === 'processing') bg-yellow-50 text-yellow-700
                                                    @elseif($upload->status === 'failed') bg-red-50 text-red-700
                                                    @else bg-gray-50 text-gray-700 @endif">
                                        @if($upload->status === 'processing')
                                            <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></div>
                                        @else
                                            <div class="w-1.5 h-1.5 bg-current rounded-full mr-1.5"></div>
                                        @endif
                                        {{ ucfirst($upload->status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-600">
                                    {{ $upload->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('bigdata.show', $upload) }}"
                                            class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if($upload->status === 'completed' && $upload->successful_records > 0)
                                            <a href="{{ route('bigdata.index') }}?upload_id={{ $upload->id }}"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                                title="View Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                                                    </path>
                                                </svg>
                                            </a>
                                        @endif
                                        @if(in_array($upload->status, ['completed', 'failed']))
                                            <form method="POST" action="{{ route('bigdata.destroy', $upload) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('Are you sure? This will delete the upload and all associated data.')"
                                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
                                                    title="Delete Upload">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-500 mb-2">No uploads found</p>
                                        <p class="text-gray-400 mb-4">Upload your first CSV file to get started</p>
                                        <a href="{{ route('bigdata.create') }}"
                                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl transition-colors duration-200">
                                            Upload Data
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($uploads->hasPages())
                <div class="p-6 border-t border-gray-100">
                    {{ $uploads->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection