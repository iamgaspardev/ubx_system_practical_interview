@extends('layouts.app')

@section('title', 'Dashboard - UBX System')

@section('content')
    <div class="space-y-8">
        <!-- Page Header with Profile -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <!-- Profile Image -->
                @if(Auth::user()->profile_image && file_exists(public_path('storage/' . Auth::user()->profile_image)))
                    <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image"
                        class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-sm">
                @else
                    <div
                        class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center border-2 border-white shadow-sm flex-shrink-0">
                        <span class="text-white font-bold text-xl">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                @endif

                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name }}!</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('profile') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200 flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Update Profile
                </a>
            </div>
        </div>

        <!-- Profile Image Quick Update - Responsive -->
        <div class="bg-white rounded-3xl p-4 sm:p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Profile Update</h2>

            <div class="flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-6">
                <!-- Current Profile Image -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    @if(Auth::user()->profile_image && file_exists(public_path('storage/' . Auth::user()->profile_image)))
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Current Profile Image"
                            class="w-20 h-20 rounded-2xl object-cover border border-gray-200 flex-shrink-0">
                    @else
                        <div
                            class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center border border-gray-200 flex-shrink-0">
                            <span class="text-gray-400 text-sm font-medium">No Image</span>
                        </div>
                    @endif

                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        <p class="text-xs text-gray-400 mt-1">Member since {{ Auth::user()->created_at->format('M Y') }}</p>
                    </div>
                </div>

                <!-- Upload Form - Responsive -->
                <div class="flex-1 w-full lg:w-auto">
                    <form method="POST" action="{{ route('profile.image') }}" enctype="multipart/form-data"
                        class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        @csrf
                        <div class="flex-1">
                            <input type="file" name="image" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-colors duration-200"
                                required>
                            <p class="text-xs text-gray-500 mt-1">JPEG, PNG, JPG, GIF up to 2MB</p>
                        </div>
                        <button type="submit"
                            class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl text-sm font-medium transition-colors duration-200 whitespace-nowrap flex-shrink-0">
                            Upload Photo
                        </button>
                    </form>

                    @if(Auth::user()->profile_image)
                        <form method="POST" action="{{ route('profile.image.delete') }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:text-red-500 text-sm font-medium transition-colors duration-200"
                                onclick="return confirm('Are you sure you want to remove your profile image?')">
                                Remove Current Photo
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Cards with Real Data -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Uploaded Diamond Records Card -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Uploaded Records</p>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ number_format(\App\Models\DiamondData::where('upload_id', '!=', null)->count()) }}
                        </p>
                        <p class="text-sm text-green-600 font-medium mt-2">
                            Total processed records
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Uploads Card -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Uploads</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format(auth()->user()->uploads()->count()) }}
                        </p>
                        <p class="text-sm text-green-600 font-medium mt-2">
                            {{ auth()->user()->uploads()->where('status', 'completed')->count() }} completed
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Processing Status Card -->
                                <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600 mb-1">Processing</p>
                                            <p class="text-3xl font-bold text-gray-900">{{ auth()->user()->uploads()->where('status', 'processing')->count() }}</p>
                                            <p class="text-sm text-orange-600 font-medium mt-2">
                                                Currently active
                                            </p>
                                        </div>
                                        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center">
                                            <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Storage Used Card -->
                                <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600 mb-1">Storage Used</p>
                                            @php
                                                $totalSize = auth()->user()->uploads()->sum('file_size');
                                                $formattedSize = $totalSize >= 107374
                                                    ? number_format($totalSize / 1073741824, 2) . ' GB'
                                                    : ($totalSize >= 104
                                                        ? number_format($totalSize / 1048576, 2) . ' MB'
                                                        : number_format($totalSize / 1024, 2) . ' KB');
                                            @endphp
                                            <p class="text-3xl font-bold text-gray-900">{{ $formattedSize }}</p>
                                            <p class="text-sm text-green-600 font-medium mt-2">
                                                {{ auth()->user()->uploads()->count() }} files uploaded
                                            </p>
                                        </div>
                                        <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Data Table with Real Data -->
                            <div class="bg-white rounded-3xl border border-gray-100">
                                <div class="p-6 border-b border-gray-100">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div>
                                            <h2 class="text-xl font-bold text-gray-900">Recent Data Uploads</h2>
                                            <p class="text-gray-600 text-sm mt-1">Your latest 5 uploads</p>
                                        </div>
                                        <a href="{{ route('bigdata.create') }}"
                                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl text-sm font-medium transition-colors duration-200 flex items-center w-full sm:w-auto justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Upload New Data
                                        </a>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-gray-100">
                                                <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                                                <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                                <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Records</th>
                                                <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                                                <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse(auth()->user()->uploads()->latest()->take(5)->get() as $upload)
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="py-4 px-6">
                                                        <div class="flex items-center">
                                                            <div class="w-10 h-10 bg-blue-50 rounded-2xl flex items-center justify-center mr-3 flex-shrink-0">
                                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                    </path>
                                                                </svg>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="font-medium text-gray-900 truncate">{{ $upload->original_filename }}</p>
                                                                <p class="text-sm text-gray-500 truncate">{{ $upload->filename }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6 text-sm text-gray-600">{{ $upload->formatted_file_size }}</td>
                                                    <td class="py-4 px-6 text-sm">
                                                        <div class="font-medium text-gray-900">{{ number_format($upload->total_records ?? 0) }}</div>
                                                        @if($upload->successful_records > 0)
                                                            <div class="text-green-600 text-xs">{{ number_format($upload->successful_records) }} processed</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-4 px-6 text-sm text-gray-600">{{ $upload->created_at->format('M j, Y') }}</td>
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
                                                    <td class="py-4 px-6">
                                                        <div class="flex items-center space-x-2">
                                                            <a href="{{ route('bigdata.show', $upload) }}"
                                                                class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                            @if($upload->status === 'completed' && $upload->successful_records > 0)
                                                                <a href="{{ route('bigdata.index') }}"
                                                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                        </path>
                                                                    </svg>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="py-12 text-center">
                                                        <div class="flex flex-col items-center">
                                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
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

                                <!-- Table Footer -->
                                @if(auth()->user()->uploads()->count() > 5)
                                    <div class="p-6 border-t border-gray-100">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                <span>Showing 5 of {{ number_format(auth()->user()->uploads()->count()) }} uploads</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('bigdata.uploads') }}"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-green-500 border border-green-500 rounded-2xl hover:bg-green-600 transition-colors duration-200">
                                                    View All Uploads
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-white rounded-3xl p-6 border border-gray-100">
                                <div class="flex items-center justify-between mb-6">
                                    <h2 class="text-xl font-bold text-gray-900">Quick Actions</h2>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <a href="{{ route('bigdata.create') }}"
                                        class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-green-300 hover:bg-green-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <div
                                                class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-green-200 transition-colors duration-200">
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                    </path>
                                                </svg>
                                            </div>
                                            <p class="font-medium text-gray-900">Upload Big Data</p>
                                            <p class="text-sm text-gray-500 mt-1">CSV files up to 100K+ records</p>
                                        </div>
                                    </a>

                                    <a href="{{ route('bigdata.index') }}"
                                        class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <div
                                                class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors duration-200">
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                </svg>
                                            </div>
                                            <p class="font-medium text-gray-900">View Diamond Data</p>
                                            <p class="text-sm text-gray-500 mt-1">Browse and filter records</p>
                                        </div>
                                    </a>

                                    <a href="{{ route('bigdata.uploads') }}"
                                        class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <div
                                                class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 transition-colors duration-200">
                                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <p class="font-medium text-gray-900">Upload History</p>
                                            <p class="text-sm text-gray-500 mt-1">Manage your uploads</p>
                                        </div>
                                    </a>

                                    <a href="{{ route('bigdata.export') }}"
                                        class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-orange-300 hover:bg-orange-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <div
                                                class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-orange-200 transition-colors duration-200">
                                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <p class="font-medium text-gray-900">Export Data</p>
                                            <p class="text-sm text-gray-500 mt-1">Download Excel reports</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
@endsection