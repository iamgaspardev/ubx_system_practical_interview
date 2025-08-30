<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'UBX System')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50">
    @auth
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="w-64 bg-white border-r border-gray-100 flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center px-6 py-4 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-lg font-bold text-gray-900">UBX System</h1>
                        </div>
                    </div>
                </div>

                <!-- Navigation - Scrollable area -->
                <div class="flex-1 overflow-y-auto">
                    <nav class="px-4 py-6 space-y-2">
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center px-4 py-3 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-600 border border-green-100' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('profile') }}"
                            class="flex items-center px-4 py-3 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors duration-200 {{ request()->routeIs('profile') ? 'bg-green-50 text-green-600 border border-green-100' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </a>

                        <div class="flex items-center px-4 py-3 text-gray-500 rounded-2xl cursor-not-allowed">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            Upload BigData
                            <span class="ml-auto text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Soon</span>
                        </div>

                        <!-- Add more navigation items here as needed -->
                        <!-- These items will be scrollable if they exceed the available space -->
                    </nav>
                </div>

                <!-- User Info - Fixed at bottom -->
                <div class="border-t border-gray-100 p-4 flex-shrink-0">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 overflow-auto">
                <main class="p-8">
                    @if (session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <!-- Guest Layout -->
        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="fixed top-4 right-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl z-50">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="fixed top-4 right-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl z-50">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    @endauth
</body>

</html>