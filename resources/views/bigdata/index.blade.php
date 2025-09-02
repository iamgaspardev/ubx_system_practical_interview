@extends('layouts.app')

@section('title', 'Diamond Data - UBX System')

@section('content')
    <style>
        /* Spinner animation for processing notification */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Progress bar animations */
        .progress-bar-animation {
            transition: width 0.3s ease-in-out;
        }

        /* Pulse animation for processing states */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Notification entrance animation */
        .notification-enter {
            transform: translateX(100%);
            opacity: 0;
        }

        .notification-enter-active {
            transform: translateX(0);
            opacity: 1;
            transition: all 0.3s ease-out;
        }
    </style>
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

                    <div id="exportOptions"
                        class="hidden absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                        <div class="py-1">
                            <button onclick="exportData()"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Standard Export (Excel)
                                <span class="ml-auto text-xs text-gray-500">Recommended</span>
                            </button>
                            @if($diamonds->total() > 25000)
                                <button onclick="exportDataSimple()"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Quick Export (Large Dataset)
                                    <span class="ml-auto text-xs text-orange-500">{{ number_format($diamonds->total()) }}
                                        records</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z">
                        </path>
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
        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('exportDropdown');
            if (!dropdown.contains(event.target)) {
                document.getElementById('exportOptions').classList.add('hidden');
            }
        });

        // Updated JavaScript with data_export filename
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

            const exportUrl = `{{ route("bigdata.export") }}?${params.toString()}`;

            fetch(exportUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json,text/csv',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(async response => {
                    const contentType = response.headers.get('content-type');

                    if (contentType && contentType.includes('application/json')) {
                        // This is a queued export
                        const data = await response.json();

                        if (data.queued) {
                            button.textContent = 'Export Queued';
                            showNotification('info', data.message + ' We\'ll notify you here when it\'s ready for download.');
                            startExportStatusCheck(); // Start checking for completion
                            return;
                        }
                    } else {
                        // This is a direct download response
                        button.textContent = 'Download Starting...';
                        const blob = await response.blob();

                        // Get filename from headers or create default with data_export
                        const contentDisposition = response.headers.get('Content-Disposition');
                        let filename = `data_export_${new Date().getTime()}.csv`;
                        if (contentDisposition) {
                            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                                // Replace diamond_data_export with data_export if found
                                filename = filename.replace(/diamond_data_export_/, 'data_export_');
                            }
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

                        const fileSizeMB = (blob.size / 1024 / 1024).toFixed(2);
                        showNotification('success', `Export completed! Downloaded ${filename} (${fileSizeMB} MB)`);
                    }
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showNotification('error', error.message || 'Export failed. Please try again.');
                })
                .finally(() => {
                    // Reset button
                    button.textContent = originalText;
                    exportButton.disabled = false;
                    exportButton.classList.remove('opacity-50', 'cursor-not-allowed');
                });
        }
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                // Page is hidden, reduce polling frequency
                if (exportProgressInterval) {
                    clearInterval(exportProgressInterval);
                    exportProgressInterval = setInterval(() => {
                        if (currentJobId) checkExportProgress(currentJobId);
                    }, 10000); // Check every 10 seconds when hidden
                }
            } else {
                // Page is visible, resume normal polling
                if (currentJobId) {
                    if (exportProgressInterval) clearInterval(exportProgressInterval);
                    startProgressTracking(currentJobId);
                }
            }
        });

        // Export status checking functions
        let exportStatusInterval;

        function startExportStatusCheck() {
            exportStatusInterval = setInterval(checkExportStatus, 5000);
        }

        function stopExportStatusCheck() {
            if (exportStatusInterval) {
                clearInterval(exportStatusInterval);
                exportStatusInterval = null;
            }
        }

        function checkExportStatus() {
            fetch('{{ route("bigdata.export-status") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'ready' && data.is_recent) {
                        stopExportStatusCheck();
                        showDownloadNotification(data);
                    }
                })
                .catch(error => {
                    console.error('Export status check error:', error);
                });
        }

        function showDownloadNotification(exportData) {
            const notification = document.createElement('div');
            notification.className = 'export-notification fixed top-4 right-4 z-50 p-6 bg-green-500 text-white rounded-lg shadow-lg max-w-md transform transition-all duration-300';

            // Ensure filename uses data_export prefix
            let displayFilename = exportData.filename;
            if (displayFilename.includes('diamond_export_')) {
                displayFilename = displayFilename.replace('diamond_export_', 'data_export_');
            }

            notification.innerHTML = `
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-bold mb-2">Export Complete!</h4>
                                        <p class="text-sm mb-3">
                                            Your export is ready for download<br>
                                            <strong>${displayFilename}</strong><br>
                                            Records: ${exportData.total_records || 'N/A'}<br>
                                            Size: ${exportData.file_size}
                                        </p>
                                        <div id="download-countdown" class="text-xs mb-3 opacity-75">
                                            Auto-download starting in <span id="countdown">3</span> seconds...
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="${exportData.download_url}" 
                                               id="download-link"
                                               class="bg-white text-green-600 px-4 py-2 rounded font-medium hover:bg-gray-100 transition-colors"
                                               download="${displayFilename}"
                                               onclick="clearExportCache(); cancelAutoDownload();">
                                                Download Now
                                            </a>
                                            <button onclick="this.closest('.export-notification').remove(); clearExportCache(); cancelAutoDownload();" 
                                                    class="bg-green-600 text-white px-4 py-2 rounded border border-white hover:bg-green-700 transition-colors">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;

            document.body.appendChild(notification);

            // Countdown and auto-download functionality
            let countdown = 3;
            let countdownInterval;
            let autoDownloadTimeout;

            window.cancelAutoDownload = function () {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                if (autoDownloadTimeout) {
                    clearTimeout(autoDownloadTimeout);
                    autoDownloadTimeout = null;
                }
                const countdownEl = document.getElementById('download-countdown');
                if (countdownEl) {
                    countdownEl.style.display = 'none';
                }
            };

            // Update countdown display
            countdownInterval = setInterval(() => {
                countdown--;
                const countdownEl = document.getElementById('countdown');
                if (countdownEl) {
                    countdownEl.textContent = countdown;
                }

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    const countdownContainer = document.getElementById('download-countdown');
                    if (countdownContainer) {
                        countdownContainer.innerHTML = '<span class="text-green-200">Starting download...</span>';
                    }
                }
            }, 1000);

            // Auto-download after countdown
            autoDownloadTimeout = setTimeout(() => {
                const downloadLink = document.getElementById('download-link');
                if (downloadLink) {
                    // Create a hidden link and trigger download
                    const hiddenLink = document.createElement('a');
                    hiddenLink.href = exportData.download_url;
                    hiddenLink.download = displayFilename;
                    hiddenLink.style.display = 'none';
                    document.body.appendChild(hiddenLink);
                    hiddenLink.click();
                    document.body.removeChild(hiddenLink);

                    showNotification('success', 'Download started automatically!');
                    clearExportCache();

                    // Update the notification to show download started
                    const countdownContainer = document.getElementById('download-countdown');
                    if (countdownContainer) {
                        countdownContainer.innerHTML = '<span class="text-green-200">✓ Download started!</span>';
                    }
                }
            }, 3000);

            // Auto remove notification after 30 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
                cancelAutoDownload();
            }, 30000);
        }

        function clearExportCache() {
            // Clear the cache so the notification doesn't keep appearing
            fetch('{{ route("bigdata.export-status") }}?clear_cache=1', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
        }

        // Start checking on page load for any recent exports
        document.addEventListener('DOMContentLoaded', function () {
            checkExportStatus(); // Check once on page load
        });
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

        function showNotification(type, message, duration = 5000) {
            const existingNotifications = document.querySelectorAll(`.notification-${type}`);
            existingNotifications.forEach(n => n.remove());

            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-yellow-500' :
                        'bg-blue-500';

            notification.className = `notification-${type} fixed top-4 right-4 z-50 p-4 ${bgColor} text-white rounded-lg shadow-lg max-w-sm notification-enter`;

            notification.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            ${getNotificationIcon(type)}
                            <span class="text-sm ml-2">${message}</span>
                        </div>
                        <button onclick="removeNotification(this)" class="ml-3 text-white hover:text-gray-200 focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                `;

            document.body.appendChild(notification);

            // Trigger entrance animation
            setTimeout(() => {
                notification.classList.add('notification-enter-active');
            }, 10);

            // Auto remove after specified duration
            if (duration > 0) {
                setTimeout(() => {
                    removeNotification(notification);
                }, duration);
            }

            return notification;
        }
        function getNotificationIcon(type) {
            switch (type) {
                case 'success':
                    return `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>`;
                case 'error':
                    return `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>`;
                case 'warning':
                    return `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>`;
                default:
                    return `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>`;
            }
        }

        function removeNotification(element) {
            const notification = element.closest('[class*="notification-"]') || element;
            if (notification) {
                notification.style.transform = 'translateX(100%)';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }
    function updateTabTitle(status, percentage = null) {
        const originalTitle = document.title;

        if (status === 'processing' && percentage !== null) {
            document.title = `(${percentage}%) Export Processing - Diamond Data`;
        } else if (status === 'completed') {
            document.title = '✓ Export Complete - Diamond Data';
            // Reset title after 5 seconds
            setTimeout(() => {
                document.title = originalTitle;
            }, 5000);
        } else {
            document.title = originalTitle;
        }
    }

    console.log('Enhanced export system loaded successfully');
    </script>
@endsection