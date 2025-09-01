@extends('layouts.app')

@section('title', 'Diamond Data - UBX System')

@section('content')
    <div class="space-y-6">
        <!-- Header with Actions -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Diamond Data</h1>
                <p class="text-gray-600 mt-1" data-total-records>{{ number_format($diamonds->total()) }} records total</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('bigdata.create') }}"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Upload Data
                </a>
                
                <!-- Export Dropdown for different options -->
                <div class="relative inline-block text-left" id="exportDropdown">
                    <button onclick="toggleExportOptions()" 
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-2xl transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span id="exportText">Export Data</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div id="exportOptions" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                        <div class="py-1">
                            <button onclick="exportData()" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Standard Export (Excel)
                                <span class="ml-auto text-xs text-gray-500">Recommended</span>
                            </button>
                            @if($diamonds->total() > 25000)
                            <button onclick="exportDataSimple()" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Quick Export (Large Dataset)
                                <span class="ml-auto text-xs text-orange-500">{{ number_format($diamonds->total()) }} records</span>
                            </button>
                            @endif
                            <div class="border-t border-gray-100 my-1"></div>
                            <div class="px-4 py-2 text-xs text-gray-500">
                                Current filters will be applied to export
                            </div>
                        </div>
                    </div>
                </div>

                <button onclick="toggleFilters()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z">
                        </path>
                    </svg>
                    Filters
                </button>
            </div>
        </div>

        <!-- Export Status Bar (shows for large exports) -->
        @if($diamonds->total() > 10000)
        <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <p class="text-sm text-yellow-800">
                        <strong>Large Dataset Notice:</strong> You have {{ number_format($diamonds->total()) }} records. 
                        Export may take several minutes to complete.
                        @if($diamonds->total() > 50000)
                        Consider applying filters to reduce export time.
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Filters Panel (Collapsible) -->
        <div id="filtersPanel"
            class="bg-white rounded-3xl border border-gray-100 {{ request()->hasAny(['cut', 'color', 'clarity', 'min_carat', 'max_carat', 'min_price', 'max_price', 'lab', 'search']) ? '' : 'hidden' }}">
            <form method="GET" action="{{ route('bigdata.index') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search cut, color, clarity, lab..."
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <!-- Cut Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cut</label>
                        <select name="cut"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Cuts</option>
                            @foreach($filterOptions['cuts'] as $cut)
                                <option value="{{ $cut }}" {{ ($filters['cut'] ?? '') === $cut ? 'selected' : '' }}>
                                    {{ $cut }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Color Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <select name="color"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Colors</option>
                            @foreach($filterOptions['colors'] as $color)
                                <option value="{{ $color }}" {{ ($filters['color'] ?? '') === $color ? 'selected' : '' }}>
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Clarity Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Clarity</label>
                        <select name="clarity"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Clarity</option>
                            @foreach($filterOptions['clarities'] as $clarity)
                                <option value="{{ $clarity }}" {{ ($filters['clarity'] ?? '') === $clarity ? 'selected' : '' }}>
                                    {{ $clarity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lab Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lab</label>
                        <select name="lab"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Labs</option>
                            @foreach($filterOptions['labs'] as $lab)
                                <option value="{{ $lab }}" {{ ($filters['lab'] ?? '') === $lab ? 'selected' : '' }}>
                                    {{ $lab }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Carat Weight Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Carat</label>
                        <input type="number" name="min_carat" value="{{ $filters['min_carat'] ?? '' }}" step="0.01" min="0"
                            placeholder="0.00"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Carat</label>
                        <input type="number" name="max_carat" value="{{ $filters['max_carat'] ?? '' }}" step="0.01" min="0"
                            placeholder="10.00"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Price ($)</label>
                        <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" min="0"
                            placeholder="0"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Price ($)</label>
                        <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" min="0"
                            placeholder="100000"
                            class="w-full px-4 py-2 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-100">
                    <a href="{{ route('bigdata.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200">
                        Clear Filters
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-2xl transition-colors duration-200">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>


        <!-- Data Table -->
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Diamond Records</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Sort Options -->
                        <form method="GET" class="flex items-center space-x-2">
                            @foreach(request()->except(['sort_by', 'sort_order']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="sort_by" onchange="this.form.submit()"
                                class="px-3 py-1 border border-gray-200 rounded-lg text-sm">
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date
                                    Added</option>
                                <option value="carat_weight" {{ request('sort_by') === 'carat_weight' ? 'selected' : '' }}>
                                    Carat Weight</option>
                                <option value="total_sales_price" {{ request('sort_by') === 'total_sales_price' ? 'selected' : '' }}>Price</option>
                                <option value="cut" {{ request('sort_by') === 'cut' ? 'selected' : '' }}>Cut</option>
                                <option value="color" {{ request('sort_by') === 'color' ? 'selected' : '' }}>Color</option>
                                <option value="clarity" {{ request('sort_by') === 'clarity' ? 'selected' : '' }}>Clarity
                                </option>
                            </select>
                            <select name="sort_order" onchange="this.form.submit()"
                                class="px-3 py-1 border border-gray-200 rounded-lg text-sm">
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Descending
                                </option>
                            </select>
                        </form>

                        <span class="text-sm text-gray-500">
                            {{ $diamonds->firstItem() }}-{{ $diamonds->lastItem() }} of
                            {{ number_format($diamonds->total()) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Diamond Details</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Specifications</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Measurements</th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">Price
                            </th>
                            <th class="text-left py-4 px-6 text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($diamonds as $diamond)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center mr-4">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $diamond->carat_weight ?? 'N/A' }}ct
                                                {{ $diamond->cut ?? 'Unknown' }}
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $diamond->color ?? 'N/A' }} •
                                                {{ $diamond->clarity ?? 'N/A' }}
                                            </p>
                                            <p class="text-xs text-gray-400">Lab: {{ $diamond->lab ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="space-y-1 text-sm">
                                        <p><span class="text-gray-500">Quality:</span> {{ $diamond->cut_quality ?? 'N/A' }}</p>
                                        <p><span class="text-gray-500">Symmetry:</span> {{ $diamond->symmetry ?? 'N/A' }}</p>
                                        <p><span class="text-gray-500">Polish:</span> {{ $diamond->polish ?? 'N/A' }}</p>
                                        <p><span class="text-gray-500">Eye Clean:</span> {{ $diamond->eye_clean ?? 'N/A' }}</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="space-y-1 text-sm">
                                        <p><span class="text-gray-500">L×W×D:</span>
                                            {{ $diamond->meas_length ?? 'N/A' }}×{{ $diamond->meas_width ?? 'N/A' }}×{{ $diamond->meas_depth ?? 'N/A' }}
                                        </p>
                                        <p><span class="text-gray-500">Depth:</span> {{ $diamond->depth_percent ?? 'N/A' }}%</p>
                                        <p><span class="text-gray-500">Table:</span> {{ $diamond->table_percent ?? 'N/A' }}%</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-lg font-bold text-gray-900">{{ $diamond->formatted_price }}</p>
                                    <p class="text-sm text-gray-500">{{ $diamond->created_at->format('M j, Y') }}</p>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="showDetails({{ $diamond->id }})"
                                            class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-500 mb-2">No diamond data found</p>
                                        <p class="text-gray-400">Upload a CSV file to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($diamonds->hasPages())
                <div class="p-6 border-t border-gray-100">
                    {{ $diamonds->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-black opacity-50"></div>
            </div>

            <div
                class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Diamond Details</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="modalContent" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button onclick="closeModal()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>


     <script>
        function toggleFilters() {
            const panel = document.getElementById('filtersPanel');
            panel.classList.toggle('hidden');
        }
        
        function toggleExportOptions() {
            const options = document.getElementById('exportOptions');
            options.classList.toggle('hidden');
        }
        
        // Close export dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('exportDropdown');
            if (!dropdown.contains(event.target)) {
                document.getElementById('exportOptions').classList.add('hidden');
            }
        });

        // Include your improved export functions here
        function exportData() {
    const button = document.getElementById('exportText');
    const originalText = button.textContent;
    const exportButton = button.closest('button');

    // Show loading state and disable button
    button.textContent = 'Preparing Export...';
    exportButton.disabled = true;
    exportButton.classList.add('opacity-50', 'cursor-not-allowed');

    // Get current URL parameters to maintain filters
    const params = new URLSearchParams(window.location.search);
    
    // Check record count and show appropriate message
    const recordCountElement = document.querySelector('[data-total-records]');
    let recordCount = 0;
    if (recordCountElement) {
        const matches = recordCountElement.textContent.match(/[\d,]+/);
        if (matches) {
            recordCount = parseInt(matches[0].replace(/,/g, ''));
        }
    }

    // Show different loading messages based on dataset size
    if (recordCount > 50000) {
        button.textContent = `Processing ${recordCount.toLocaleString()} records (CSV format)...`;
        showNotification('info', `Large dataset detected (${recordCount.toLocaleString()} records). Export will be in CSV format for optimal performance.`);
    } else if (recordCount > 25000) {
        button.textContent = `Processing ${recordCount.toLocaleString()} records...`;
        showNotification('info', `Medium dataset (${recordCount.toLocaleString()} records). This may take a few minutes.`);
    } else if (recordCount > 10000) {
        button.textContent = `Exporting ${recordCount.toLocaleString()} records...`;
    }

    const exportUrl = `{{ route("bigdata.export") }}?${params.toString()}`;

    // Extended timeout for large datasets
    const controller = new AbortController();
    const timeoutDuration = recordCount > 50000 ? 900000 : 600000; // 15 min for huge, 10 min for large
    const timeoutId = setTimeout(() => controller.abort(), timeoutDuration);

    fetch(exportUrl, {
        method: 'GET',
        signal: controller.signal,
        headers: {
            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            let errorMessage = `Export failed (${response.status})`;
            
            try {
                const responseText = await response.text();
                
                // Try to parse as JSON first
                try {
                    const errorData = JSON.parse(responseText);
                    errorMessage = errorData.message || errorMessage;
                } catch (jsonError) {
                    // Check for common error patterns in HTML response
                    if (responseText.includes('memory')) {
                        errorMessage = 'Server memory limit exceeded. Try applying more specific filters to reduce the dataset size, or contact support to increase server limits.';
                    } else if (responseText.includes('execution time')) {
                        errorMessage = 'Export timed out. The dataset is too large. Please apply more specific filters.';
                    } else if (responseText.includes('Illuminate\\Database')) {
                        errorMessage = 'Database error occurred during export. Please try again or contact support.';
                    } else if (responseText.length < 500) {
                        errorMessage = responseText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            } catch (readError) {
                console.error('Could not read error response:', readError);
            }
            
            throw new Error(errorMessage);
        }

        // Check content type to determine file format
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            const jsonData = await response.json();
            throw new Error(jsonData.message || 'Unexpected response format');
        }

        // Determine file extension based on content type
        let fileExtension = '.xlsx';
        if (contentType && contentType.includes('text/csv')) {
            fileExtension = '.csv';
        }

        // Get filename from headers or create default
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = `diamond_data_export_${new Date().getTime()}${fileExtension}`;
        if (contentDisposition) {
            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
            if (matches != null && matches[1]) {
                filename = matches[1].replace(/['"]/g, '');
            }
        }

        // Process the download
        button.textContent = 'Finalizing Download...';
        const blob = await response.blob();
        
        if (blob.size === 0) {
            throw new Error('Export file is empty. Please check your filters and try again.');
        }

        // Create download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // Show success message with file details
        const fileSizeMB = (blob.size / 1024 / 1024).toFixed(2);
        const formatType = fileExtension === '.csv' ? 'CSV' : 'Excel';
        showNotification('success', `Export completed! Downloaded ${filename} (${formatType} format, ${fileSizeMB} MB)`);
    })
    .catch(error => {
        clearTimeout(timeoutId);
        
        if (error.name === 'AbortError') {
            showNotification('error', `Export was cancelled due to timeout (${timeoutDuration/60000} minutes). The dataset is too large. Please apply more specific filters to reduce the number of records.`);
        } else {
            console.error('Export error:', error);
            showNotification('error', error.message || 'Export failed. Please try again or contact support.');
        }
    })
    .finally(() => {
        // Reset button
        button.textContent = originalText;
        exportButton.disabled = false;
        exportButton.classList.remove('opacity-50', 'cursor-not-allowed');
    });
}

// Memory-efficient export for very large datasets
function exportLargeDataset() {
    const recordCountElement = document.querySelector('[data-total-records]');
    let recordCount = 0;
    if (recordCountElement) {
        const matches = recordCountElement.textContent.match(/[\d,]+/);
        if (matches) {
            recordCount = parseInt(matches[0].replace(/,/g, ''));
        }
    }

    if (recordCount > 100000) {
        showNotification('warning', `Very large dataset (${recordCount.toLocaleString()} records). This export will take several minutes and will be in CSV format.`);
    }

    // Use the standard export function but with memory-optimized settings
    exportData();
}

function showNotification(type, message) {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.export-notification');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = `export-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md transform transition-all duration-300 translate-x-full`;
    
    const configs = {
        success: { classes: 'bg-green-500 text-white', icon: '✓' },
        error: { classes: 'bg-red-500 text-white', icon: '✗' },
        info: { classes: 'bg-blue-500 text-white', icon: 'ℹ' },
        warning: { classes: 'bg-yellow-500 text-black', icon: '⚠' }
    };
    
    const config = configs[type] || configs.info;
    notification.className += ` ${config.classes}`;
    
    notification.innerHTML = `
        <div class="flex items-start">
            <span class="text-lg mr-3 mt-0.5">${config.icon}</span>
            <div class="flex-1">
                <div class="text-sm font-medium break-words">${message}</div>
            </div>
            <button onclick="this.closest('.export-notification').remove()" class="ml-3 text-current hover:opacity-70 focus:outline-none">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Slide in animation
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after appropriate time based on message type and length
    const baseTime = type === 'error' ? 12000 : 6000;
    const extraTime = Math.min(message.length * 50, 5000); // Extra time for longer messages
    const autoRemoveTime = baseTime + extraTime;
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, autoRemoveTime);
}
    </script>
@endsection