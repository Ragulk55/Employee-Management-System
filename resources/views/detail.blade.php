<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $submodule->name }} - Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .selected-employee {
            background-color: #dbeafe;
            border-color: #3b82f6;
        }
        .checkbox-checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-6 py-8">
        <!-- ===== Page Header ===== -->
        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $submodule->name }}</h1>
        <p class="text-gray-600 mb-6">
            <strong>{{ $submodule->name }}</strong> 
            <strong>{{ $module }} Resources</strong>.
        </p>

        <!-- ===== Flash Messages ===== -->
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

        <!-- ===== Available Resources Section ===== -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">Available Resources</h2>
                <button onclick="openModal(0)" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition">
                    + Add Employees
                </button>
            </div>
            
            @if($submodule->employees->isEmpty())
                <p class="text-gray-500">No Resources.</p>
            @else
                <ul class="space-y-2">
                    @foreach($submodule->employees as $employee)
                        <li class="flex items-center justify-between bg-gray-50 p-4 rounded border border-gray-200 hover:bg-gray-100 transition">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-lg">{{ $employee->name }}</span>
                                    @if($employee->assignment_date)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                             {{ $employee->assignment_date->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if($employee->position)
                                    <p class="text-gray-400 text-xs mt-1">{{ $employee->position }}</p>
                                @endif
                            </div>
                            <form action="{{ route('submodule.removeEmployee', ['module' => $moduleSlug, 'sub' => $subSlug]) }}" method="POST" onsubmit="return confirm('Remove {{ $employee->name }} from available resources?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <input type="hidden" name="is_trained" value="0">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded text-sm transition">
                                    Remove
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- ===== Trained Resources Section ===== -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">Trainable Resources</h2>
                <button onclick="openModal(1)" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition">
                    + Add Employees
                </button>
            </div>
            
            @if($submodule->trainedEmployees->isEmpty())
                <p class="text-gray-500">No Trained Resources.</p>
            @else
                <ul class="space-y-2">
                    @foreach($submodule->trainedEmployees as $employee)
                        <li class="flex items-center justify-between bg-gray-50 p-4 rounded border border-gray-200 hover:bg-gray-100 transition">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-lg">{{ $employee->name }}</span>
                                    @if($employee->assignment_date)
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                             {{ $employee->assignment_date->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if($employee->position)
                                    <p class="text-gray-400 text-xs mt-1">{{ $employee->position }}</p>
                                @endif
                            </div>
                            <form action="{{ route('submodule.removeEmployee', ['module' => $moduleSlug, 'sub' => $subSlug]) }}" method="POST" onsubmit="return confirm('Remove {{ $employee->name }} from trained resources?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <input type="hidden" name="is_trained" value="1">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded text-sm transition">
                                    Remove
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- ===== Back Link ===== -->
        <a href="/{{ $moduleSlug }}" class="text-blue-500 hover:underline">
            ← Back to {{ $module }}
        </a>
    </div>

    <!-- ===== Modal for Adding Multiple Employees ===== -->
    <div id="employeeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900" id="modalTitle">Add Employees to {{ $submodule->name }}</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modalDescription">Select multiple employees to add</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>

            <div class="mt-4">
                @if($employees->isEmpty())
                    <p class="text-gray-500">No employees available.</p>
                @else
                    <!-- Selection Controls -->
                    <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <button onclick="selectAll()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Select All
                            </button>
                            <button onclick="deselectAll()" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                Deselect All
                            </button>
                            <span class="text-sm text-gray-500">
                                <span id="selectedCount" class="font-bold text-blue-600">0</span> selected
                            </span>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="employeeSearchInput" 
                                placeholder="Search employees by name or position..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                autocomplete="off"
                            >
                        </div>
                        <p id="employeeSearchResults" class="text-xs text-gray-500 mt-2">
                            <span id="employeeResultCount">0</span> employees available
                        </p>
                    </div>

                    <!-- Employee List with Checkboxes -->
                    <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg" id="employeeList"></div>

                    <!-- No Results Message -->
                    <div id="noEmployeeResults" class="hidden text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-500 font-medium">No employees found</p>
                        <p class="text-gray-400 text-sm mt-1">Try adjusting your search terms</p>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-between items-center border-t pt-4">
                <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded transition">
                    Cancel
                </button>
                <button onclick="addSelectedEmployees()" id="addButton" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded transition inline-flex items-center gap-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span id="addButtonText">Add Employees</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===== Scripts ===== -->
    <script>
let currentIsTrained = 0;
const assignedEmployeeIds = @json($assignedEmployeeIds ?? []);
const trainedEmployeeIds = @json($trainedEmployeeIds ?? []);
const allEmployees = @json($employees);
const moduleSlug = @json($moduleSlug);
const subSlug = @json($subSlug);
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let selectedEmployees = new Set();

function openModal(isTrained) {
    currentIsTrained = isTrained;
    selectedEmployees.clear();
    
    const modal = document.getElementById('employeeModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const addButton = document.getElementById('addButton');
    
    if (isTrained === 1) {
        modalTitle.textContent = 'Add Employees to Trained Resources';
        modalDescription.textContent = 'Select multiple employees to add to trained resources';
        addButton.className = 'bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded transition inline-flex items-center gap-2';
    } else {
        modalTitle.textContent = 'Add Employees to Available Resources';
        modalDescription.textContent = 'Select multiple employees to add to available resources';
        addButton.className = 'bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded transition inline-flex items-center gap-2';
    }
    
    modal.classList.remove('hidden');
    populateEmployeeList();
    updateSelectedCount();
    
    const searchInput = document.getElementById('employeeSearchInput');
    if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 100);
    }
}

function closeModal() {
    document.getElementById('employeeModal').classList.add('hidden');
    selectedEmployees.clear();
}

function populateEmployeeList() {
    const employeeList = document.getElementById('employeeList');
    const excludedIds = currentIsTrained === 1 ? trainedEmployeeIds : assignedEmployeeIds;
    
    employeeList.innerHTML = '';
    let count = 0;
    
    allEmployees.forEach(employee => {
        if (!excludedIds.includes(employee.id)) {
            count++;
            
            const div = document.createElement('div');
            div.className = 'employee-item flex items-center gap-3 p-4 hover:bg-gray-50 transition border-b border-gray-200 cursor-pointer';
            div.setAttribute('data-id', employee.id);
            div.setAttribute('data-name', employee.name.toLowerCase());
            div.setAttribute('data-position', (employee.position || '').toLowerCase());
            div.setAttribute('data-department', (employee.department || '').toLowerCase());
            div.onclick = () => toggleEmployee(employee.id);
            
            // Checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = `emp-${employee.id}`;
            checkbox.className = 'w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer';
            checkbox.onclick = (e) => e.stopPropagation();
            checkbox.onchange = () => toggleEmployee(employee.id);
            
            // Employee info container
            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-1';
            
            // Name and department
            const nameDiv = document.createElement('div');
            nameDiv.className = 'flex items-center gap-2';
            
            const nameSpan = document.createElement('span');
            nameSpan.className = 'font-medium text-lg';
            nameSpan.textContent = employee.name;
            nameDiv.appendChild(nameSpan);
            
            if (employee.department) {
                const deptSpan = document.createElement('span');
                deptSpan.className = 'bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded';
                deptSpan.textContent = employee.department;
                nameDiv.appendChild(deptSpan);
            }
            
            infoDiv.appendChild(nameDiv);
            
            // Position
            if (employee.position) {
                const positionP = document.createElement('p');
                positionP.className = 'text-gray-400 text-xs mt-1';
                positionP.textContent = employee.position;
                infoDiv.appendChild(positionP);
            }
            
            div.appendChild(checkbox);
            div.appendChild(infoDiv);
            employeeList.appendChild(div);
        }
    });

    document.getElementById('employeeResultCount').textContent = count;
    setupSearch();
}

function toggleEmployee(employeeId) {
    const checkbox = document.getElementById(`emp-${employeeId}`);
    const employeeDiv = checkbox.closest('.employee-item');
    
    if (selectedEmployees.has(employeeId)) {
        selectedEmployees.delete(employeeId);
        checkbox.checked = false;
        employeeDiv.classList.remove('selected-employee');
    } else {
        selectedEmployees.add(employeeId);
        checkbox.checked = true;
        employeeDiv.classList.add('selected-employee');
    }
    
    updateSelectedCount();
}

function selectAll() {
    const visibleItems = document.querySelectorAll('.employee-item:not([style*="display: none"])');
    visibleItems.forEach(item => {
        const employeeId = parseInt(item.getAttribute('data-id'));
        if (!selectedEmployees.has(employeeId)) {
            selectedEmployees.add(employeeId);
            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.checked = true;
            item.classList.add('selected-employee');
        }
    });
    updateSelectedCount();
}

function deselectAll() {
    selectedEmployees.clear();
    document.querySelectorAll('.employee-item').forEach(item => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        checkbox.checked = false;
        item.classList.remove('selected-employee');
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = selectedEmployees.size;
    document.getElementById('selectedCount').textContent = count;
    
    const addButton = document.getElementById('addButton');
    const addButtonText = document.getElementById('addButtonText');
    
    if (count === 0) {
        addButton.disabled = true;
        addButton.classList.add('opacity-50', 'cursor-not-allowed');
        addButtonText.textContent = 'Add Employees';
    } else {
        addButton.disabled = false;
        addButton.classList.remove('opacity-50', 'cursor-not-allowed');
        addButtonText.textContent = `Add ${count} Employee${count > 1 ? 's' : ''}`;
    }
}

async function addSelectedEmployees() {
    if (selectedEmployees.size === 0) return;
    
    const addButton = document.getElementById('addButton');
    const originalText = addButton.innerHTML;
    
    addButton.disabled = true;
    addButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...';
    
    let successCount = 0;
    let errorCount = 0;
    
    for (const employeeId of selectedEmployees) {
        try {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('employee_id', employeeId);
            formData.append('is_trained', currentIsTrained);
            
            const response = await fetch(`/${moduleSlug}/${subSlug}/add-employee`, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                successCount++;
            } else {
                errorCount++;
            }
        } catch (error) {
            console.error('Error adding employee:', error);
            errorCount++;
        }
    }
    
    // Reload page to show updated lists
    if (successCount > 0) {
        window.location.reload();
    } else {
        addButton.disabled = false;
        addButton.innerHTML = originalText;
        alert('Failed to add employees. Please try again.');
    }
}

function setupSearch() {
    const searchInput = document.getElementById('employeeSearchInput');
    const employeeItems = document.querySelectorAll('.employee-item');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            employeeItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const position = item.getAttribute('data-position');
                const department = item.getAttribute('data-department');
                
                if (searchTerm === '' || 
                    name.includes(searchTerm) || 
                    position.includes(searchTerm) ||
                    department.includes(searchTerm)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            updateResultsCount(visibleCount);
        });
    }
}

function updateResultsCount(count) {
    const resultCountElement = document.getElementById('employeeResultCount');
    const noResultsElement = document.getElementById('noEmployeeResults');
    const employeeList = document.getElementById('employeeList');
    
    if (resultCountElement) {
        resultCountElement.textContent = count;
    }

    if (count === 0) {
        if (employeeList) employeeList.style.display = 'none';
        if (noResultsElement) noResultsElement.classList.remove('hidden');
    } else {
        if (employeeList) employeeList.style.display = 'block';
        if (noResultsElement) noResultsElement.classList.add('hidden');
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('employeeModal');
    if (event.target == modal) {
        closeModal();
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
    </script>
</body>
</html>