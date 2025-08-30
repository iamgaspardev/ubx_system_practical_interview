@extends('layouts.app')

@section('title', 'Dashboard - UBX System')

@section('content')
    <div class="space-y-8">
        <!-- Page Header with Profile -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Profile Image -->
                @if(Auth::user()->profile_image && file_exists(public_path('storage/' . Auth::user()->profile_image)))
                    <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image"
                        class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-sm">
                @else
                    <div
                        class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center border-2 border-white shadow-sm">
                        <span class="text-white font-bold text-xl">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                @endif

                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name }}!</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('profile') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Update Profile
                </a>
            </div>
        </div>

        <!-- Profile Image Quick Update -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Profile Update</h2>

            <div class="flex items-center space-x-6">
                <!-- Current Profile Image -->
                <div class="flex items-center space-x-4">
                    @if(Auth::user()->profile_image && file_exists(public_path('storage/' . Auth::user()->profile_image)))
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Current Profile Image"
                            class="w-20 h-20 rounded-2xl object-cover border border-gray-200">
                    @else
                        <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center border border-gray-200">
                            <span class="text-gray-400 text-sm font-medium">No Image</span>
                        </div>
                    @endif

                    <div>
                        <p class="font-medium text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                        <p class="text-xs text-gray-400 mt-1">Member since {{ Auth::user()->created_at->format('M Y') }}</p>
                    </div>
                </div>

                <!-- Upload Form -->
                <div class="flex-1">
                    <form method="POST" action="{{ route('profile.image') }}" enctype="multipart/form-data"
                        class="flex items-center space-x-4">
                        @csrf
                        <div class="flex-1">
                            <input type="file" name="image" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-colors duration-200"
                                required>
                            <p class="text-xs text-gray-500 mt-1">JPEG, PNG, JPG, GIF up to 2MB</p>
                        </div>
                        <button type="submit"
                            class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl text-sm font-medium transition-colors duration-200 whitespace-nowrap">
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Data Uploaded Card -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Data Uploaded</p>
                        <p class="text-3xl font-bold text-gray-900">1,247</p>
                        <p class="text-sm text-green-600 font-medium mt-2">↗ +12% from last month</p>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">892</p>
                        <p class="text-sm text-green-600 font-medium mt-2">↗ +8% from last month</p>
                    </div>
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Storage Used Card -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Storage Used</p>
                        <p class="text-3xl font-bold text-gray-900">2.4 GB</p>
                        <p class="text-sm text-orange-600 font-medium mt-2">↗ +5% from last month</p>
                    </div>
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Data Table -->
        <div class="bg-white rounded-3xl border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Recent Data Uploads</h2>
                        <p class="text-gray-600 text-sm mt-1">Latest 5 data entries</p>
                    </div>
                    <button
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl text-sm font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Upload New Data
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Data
                                Name</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Type
                            </th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Size
                            </th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Upload Date</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
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
                                        <p class="font-medium text-gray-900">Customer Analytics Q4</p>
                                        <p class="text-sm text-gray-500">customer_analytics_q4.csv</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    CSV
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-600">2.4 MB</td>
                            <td class="py-4 px-6 text-sm text-gray-600">Aug 28, 2025</td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    Processed
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-50 rounded-2xl flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Sales Report 2025</p>
                                        <p class="text-sm text-gray-500">sales_report_2025.xlsx</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    Excel
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-600">5.2 MB</td>
                            <td class="py-4 px-6 text-sm text-gray-600">Aug 27, 2025</td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    Processed
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-50 rounded-2xl flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">User Behavior Data</p>
                                        <p class="text-sm text-gray-500">user_behavior.json</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                                    JSON
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-600">1.8 MB</td>
                            <td class="py-4 px-6 text-sm text-gray-600">Aug 26, 2025</td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">
                                    <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></div>
                                    Processing
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-orange-50 rounded-2xl flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V9a2 2 0 012-2h6l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Product Inventory</p>
                                        <p class="text-sm text-gray-500">inventory_data.csv</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    CSV
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-600">3.1 MB</td>
                            <td class="py-4 px-6 text-sm text-gray-600">Aug 25, 2025</td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    Processed
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-red-50 rounded-2xl flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Error Logs</p>
                                        <p class="text-sm text-gray-500">system_errors.log</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                    LOG
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-600">0.8 MB</td>
                            <td class="py-4 px-6 text-sm text-gray-600">Aug 24, 2025</td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                    <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                                    Error
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="p-6 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <span>Showing 5 of 1,247 entries</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-2xl hover:bg-gray-50 transition-colors duration-200">
                            Previous
                        </button>
                        <button
                            class="px-4 py-2 text-sm font-medium text-white bg-green-500 border border-green-500 rounded-2xl hover:bg-green-600 transition-colors duration-200">
                            View All Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Quick Actions</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <button
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
                        <p class="font-medium text-gray-900">Upload Data</p>
                        <p class="text-sm text-gray-500 mt-1">Add new dataset</p>
                    </div>
                </button>

                <button
                    class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                    <div class="text-center">
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors duration-200">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900">Analytics</p>
                        <p class="text-sm text-gray-500 mt-1">View insights</p>
                    </div>
                </button>

                <button
                    class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                    <div class="text-center">
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 transition-colors duration-200">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900">Export</p>
                        <p class="text-sm text-gray-500 mt-1">Download reports</p>
                    </div>
                </button>

                <button
                    class="flex items-center justify-center p-4 border-2 border-dashed border-gray-200 rounded-2xl hover:border-orange-300 hover:bg-orange-50 transition-all duration-200 group">
                    <div class="text-center">
                        <div
                            class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-orange-200 transition-colors duration-200">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900">Settings</p>
                        <p class="text-sm text-gray-500 mt-1">Configure system</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
@endsection
{{-- @extends('layouts.app')

@section('title', 'Profile - UBX System')@section('content')<div class="max-w-2xl mx-auto">
    <!-- Profile Header -->
    <div class="bg-white rounded-3xl p-8 mb-8 border border-gray-100">
        <div class="flex items-center">
            @if($user->profile_image && file_exists(public_path('storage/' . $user->profile_image)))
                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Image"
                    class="w-20 h-20 rounded-2xl object-cover mr-6">
            @else
                <div class="w-20 h-20 bg-green-500 rounded-2xl flex items-center justify-center mr-6">
                    <span class="text-white font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->name }}</h1>
                <p class="text-gray-600 text-lg">{{ $user->email }}</p>
                <div class="mt-3 flex items-center space-x-4">
                    <div
                        class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-medium">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        Active User
                    </div>
                    <span class="text-sm text-gray-500">
                        Member since {{ $user->created_at->format('F Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Image Update -->
    <div class="bg-white rounded-3xl p-8 mb-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Profile Image (Optional)</h2>

        <div class="flex items-center space-x-4">
            @if($user->profile_image && file_exists(public_path('storage/' . $user->profile_image)))
                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Current Profile Image"
                    class="w-16 h-16 rounded-2xl object-cover">
            @else
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                    <span class="text-gray-400 text-sm">No Image</span>
                </div>
            @endif

            <div class="flex-1">
                <form method="POST" action="{{ route('profile.image') }}" enctype="multipart/form-data"
                    class="flex items-center space-x-4">
                    @csrf
                    <input type="file" name="image" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                        required>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-full text-sm font-medium transition-colors duration-200">
                        Upload
                    </button>
                </form>

                @if($user->profile_image)
                    <form method="POST" action="{{ route('profile.image.delete') }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-500 text-sm font-medium"
                            onclick="return confirm('Are you sure you want to delete your profile image?')">
                            Remove Image
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <p class="text-sm text-gray-500 mt-4">
            Accepted formats: JPEG, PNG, JPG, GIF. Maximum size: 2MB. This is completely optional.
        </p>
    </div>

    <!-- Profile Form -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Update Profile Information</h2>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('name') border-red-300 @enderror"
                    placeholder="Enter your full name">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('email') border-red-300 @enderror"
                    placeholder="Enter your email">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current
                    Password</label>
                <input id="current_password" name="current_password" type="password"
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('current_password') border-red-300 @enderror"
                    placeholder="Enter current password (required to change password)">
                @error('current_password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input id="password" name="password" type="password"
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('password') border-red-300 @enderror"
                    placeholder="Enter new password (leave blank to keep current)">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New
                    Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200"
                    placeholder="Confirm new password">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('dashboard') }}"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-2xl transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-2xl transition-colors duration-200 focus:ring-4 focus:ring-green-200 focus:outline-none">
                    Update Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-3xl p-8 mt-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Account Information</h2><div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-gray-600">Account Created</span>
                    <span class="font-medium text-gray-900">{{ $user->created_at->format('F j, Y g:i A') }}</span>
                </div>

                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium text-gray-900">{{ $user->updated_at->format('F j, Y g:i A') }}</span>
                </div>

                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-600">Account Status</span>
                    <span
                        class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-medium">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        Active
                    </span>
                </div>
                </div>
            </div>
        </div>
@endsection --}}