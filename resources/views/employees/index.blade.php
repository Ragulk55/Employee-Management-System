<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Employees</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .employee-card {
            transition: all 0.25s ease-in-out;
        }
        .employee-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">All Employees</h1>
                <p class="text-gray-600">View employee details and their assignments</p>
            </div>
            <a href="{{ route('employees.export') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export to Excel
            </a>
        </div>

        <!-- Search Bar -->
        @if($employees && count($employees) > 0)
            <div class="mb-6">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search employees by name or position..." 
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        autocomplete="off"
                    >
                </div>
                <p id="searchResults" class="text-sm text-gray-500 mt-2">
                    Showing <span id="resultCount">{{ count($employees) }}</span> of {{ count($employees) }} employees
                </p>
            </div>
        @endif

        <!-- Employees Grid -->
        @if($employees && count($employees) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="employeesGrid">
                @foreach($employees as $employee)
                    <a href="{{ route('employees.show', $employee->id) }}" 
                       class="employee-card bg-white rounded-lg shadow-md border border-gray-200 hover:border-blue-400 p-6 block" 
                       data-name="{{ strtolower($employee->name) }}"
                       data-position="{{ strtolower($employee->position ?? '') }}">
                        <div class="flex items-center">
                            <!-- <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
                                {{ strtoupper(substr($employee->name, 0, 2)) }}
                            </div> -->
                            <div class="ml-3 min-w-0 flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 truncate">{{ $employee->name }}</h3>
                                @if($employee->position)
                                    <p class="text-sm text-gray-500 truncate">{{ $employee->position }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 text-lg font-medium">No employees found</p>
                <p class="text-gray-400 text-sm mt-2">Try adjusting your search terms</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-500 text-lg">No employees found in the system.</p>
            </div>
        @endif

        <!-- Back Link -->
        <div class="mt-8">
            <a href="/" class="text-blue-500 hover:underline inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search Functionality Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const employeeCards = document.querySelectorAll('.employee-card');
            const resultCount = document.getElementById('resultCount');
            const noResults = document.getElementById('noResults');
            const employeesGrid = document.getElementById('employeesGrid');
            const totalCount = employeeCards.length;

            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    let visibleCount = 0;

                    employeeCards.forEach(card => {
                        const name = card.getAttribute('data-name');
                        const position = card.getAttribute('data-position');
                        
                        // Search in both name and position
                        if (searchTerm === '' || name.includes(searchTerm) || position.includes(searchTerm)) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Update result count
                    if (resultCount) {
                        resultCount.textContent = visibleCount;
                    }

                    // Show/hide no results message
                    if (visibleCount === 0) {
                        if (employeesGrid) employeesGrid.style.display = 'none';
                        if (noResults) noResults.classList.remove('hidden');
                    } else {
                        if (employeesGrid) employeesGrid.style.display = 'grid';
                        if (noResults) noResults.classList.add('hidden');
                    }
                });

                // Clear search on Escape key
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        searchInput.value = '';
                        searchInput.dispatchEvent(new Event('input'));
                        searchInput.blur();
                    }
                });
            }
        });
    </script>
</body>
</html>