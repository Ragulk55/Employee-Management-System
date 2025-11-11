<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $module }} - Submodules</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .submodule-card {
            transition: all 0.25s ease-in-out;
        }
        .submodule-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .delete-button {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        .group:hover .delete-button {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $module }}</h1>
            <p class="text-gray-600">Select a {{ strtolower($module) }} item to manage employees</p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
            </div>
        @endif

        <!-- Add Submodule Button (only for dynamic modules) -->
        @if(isset($isDynamic) && $isDynamic)
            <div class="flex justify-end mb-6">
                <button onclick="openSubmoduleModal()" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition inline-flex items-center gap-2 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Submodule
                </button>
            </div>
        @endif

        <!-- Search Bar -->
        @if($submodules && count($submodules) > 0)
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
                        placeholder="Search {{ strtolower($module) }} items..." 
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        autocomplete="off"
                    >
                </div>
                <p id="searchResults" class="text-sm text-gray-500 mt-2">
                    Showing <span id="resultCount">{{ count($submodules) }}</span> of {{ count($submodules) }} items
                </p>
            </div>
        @endif

        <!-- Submodules Grid -->
        @if($submodules && count($submodules) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="submodulesGrid">
                @foreach($submodules as $submodule)
                    <div class="relative group">
                        <a href="{{ $submodule['route'] }}" 
                           class="submodule-card bg-white rounded-lg shadow-md border border-gray-200 hover:border-blue-400 p-6 block" 
                           data-name="{{ strtolower($submodule['name']) }}">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ $submodule['name'] }}</h3>
                        </a>
                        
                        @if(isset($submodule['is_dynamic']) && $submodule['is_dynamic'])
                            <button onclick="deleteSubmodule('{{ $submodule['id'] }}', '{{ addslashes($submodule['name']) }}')"
                                    class="delete-button absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <form id="delete-form-{{ $submodule['id'] }}" 
                                  action="{{ route('submodule.destroy', ['module' => $moduleSlug, 'id' => $submodule['id']]) }}" 
                                  method="POST" 
                                  class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 text-lg font-medium">No items found</p>
                <p class="text-gray-400 text-sm mt-2">Try adjusting your search terms</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500 text-lg mb-3">No items found for this module.</p>
                @if(isset($isDynamic) && $isDynamic)
                    <button onclick="openSubmoduleModal()" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add First Submodule
                    </button>
                @endif
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

    <!-- Add Submodule Modal (only for dynamic modules) -->
    @if(isset($isDynamic) && $isDynamic)
        <div id="submoduleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
                
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-2xl font-bold text-gray-900">Create New Submodule</h3>
                    <button onclick="closeSubmoduleModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <form id="submoduleForm" action="{{ route('submodule.store', ['module' => $moduleSlug]) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="module_id" value="{{ $moduleId ?? '' }}">
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Submodule Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="e.g., Testing & Quality Assurance">
                        <p class="text-xs text-gray-500 mt-1">Enter a descriptive name for the submodule</p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                        <button type="button" 
                                onclick="closeSubmoduleModal()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                id="submitButton"
                                class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded transition inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Submodule
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

    <!-- Search Functionality Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const submoduleCards = document.querySelectorAll('.submodule-card');
            const resultCount = document.getElementById('resultCount');
            const noResults = document.getElementById('noResults');
            const submodulesGrid = document.getElementById('submodulesGrid');

            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    let visibleCount = 0;

                    submoduleCards.forEach(card => {
                        const itemName = card.getAttribute('data-name');
                        
                        if (searchTerm === '' || itemName.includes(searchTerm)) {
                            card.parentElement.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.parentElement.style.display = 'none';
                        }
                    });

                    if (resultCount) {
                        resultCount.textContent = visibleCount;
                    }

                    if (visibleCount === 0) {
                        if (submodulesGrid) submodulesGrid.style.display = 'none';
                        if (noResults) noResults.classList.remove('hidden');
                    } else {
                        if (submodulesGrid) submodulesGrid.style.display = 'grid';
                        if (noResults) noResults.classList.add('hidden');
                    }
                });

                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        searchInput.value = '';
                        searchInput.dispatchEvent(new Event('input'));
                        searchInput.blur();
                    }
                });
            }
        });

        // Open Add Submodule Modal
        function openSubmoduleModal() {
            const modal = document.getElementById('submoduleModal');
            const nameInput = document.getElementById('name');
            
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    if (nameInput) {
                        nameInput.focus();
                    }
                }, 100);
            }
        }

        // Close Add Submodule Modal
        function closeSubmoduleModal() {
            const modal = document.getElementById('submoduleModal');
            const form = document.getElementById('submoduleForm');
            
            if (modal) {
                modal.classList.add('hidden');
            }
            if (form) {
                form.reset();
            }
        }

        // Delete Submodule Function
        function deleteSubmodule(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?\n\nThis will remove all employee assignments for this submodule.`)) {
                const form = document.getElementById(`delete-form-${id}`);
                if (form) {
                    form.submit();
                } else {
                    console.error('Delete form not found for ID:', id);
                    alert('Error: Could not find delete form. Please refresh the page and try again.');
                }
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('submoduleModal');
            if (event.target == modal) {
                closeSubmoduleModal();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('submoduleModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeSubmoduleModal();
                }
            }
        });

        // Form submission handling
        const submoduleForm = document.getElementById('submoduleForm');
        if (submoduleForm) {
            submoduleForm.addEventListener('submit', function(e) {
                const submitButton = document.getElementById('submitButton');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating...';
                }
            });
        }
    </script>
</body>
</html>