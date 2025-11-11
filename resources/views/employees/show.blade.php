<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $employee->name }} - Employee Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 py-4 sm:py-8 max-w-7xl">
        <!-- Employee Profile Header -->
        <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xl sm:text-2xl font-bold flex-shrink-0">
                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 truncate">{{ $employee->name }}</h1>
                        @if($employee->position)
                            <p class="text-base sm:text-lg text-gray-600 mt-1">{{ $employee->position }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Profile Button -->
                <button onclick="toggleProfileModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-medium text-sm sm:text-base transition-colors flex items-center gap-2 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="hidden sm:inline">View Profile</span>
                    <span class="sm:hidden">Profile</span>
                </button>
            </div>
        </div>

        <!-- Skill Set Section (Available Resources) -->
        <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 sm:gap-0 mb-4">
                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Skill Set (Available Resources)</h2>
                <span class="bg-blue-100 text-blue-800 text-xs sm:text-sm font-medium px-3 py-1 rounded-full w-fit">
                    {{ $assignments->where('is_trained', 0)->count() }} {{ $assignments->where('is_trained', 0)->count() === 1 ? 'Assignment' : 'Assignments' }}
                </span>
            </div>

            @if($assignments->where('is_trained', 0)->isEmpty())
                <div class="text-center py-6">
                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">No assignments yet</p>
                    <p class="text-gray-400 text-xs mt-1">This employee hasn't been assigned to any available resources</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($assignments->where('is_trained', 0) as $assignment)
                        <a href="/{{ $assignment->module }}/{{ $assignment->submodule }}" 
                           class="block bg-gray-50 hover:bg-gray-100 p-3 sm:p-4 rounded-lg border border-gray-200 transition-colors">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded w-fit">
                                            {{ $assignment->module_name }}
                                        </span>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 break-words">{{ $assignment->submodule_name }}</h3>
                                    </div>
                                    @if($assignment->created_at)
                                        <p class="text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Assigned on {{ $assignment->created_at->format('d M Y') }}
                                        </p>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Trainable Resources Section -->
        <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 sm:gap-0 mb-4">
                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Trainable Resources</h2>
                <span class="bg-green-100 text-green-800 text-xs sm:text-sm font-medium px-3 py-1 rounded-full w-fit">
                    {{ $assignments->where('is_trained', 1)->count() }} {{ $assignments->where('is_trained', 1)->count() === 1 ? 'Assignment' : 'Assignments' }}
                </span>
            </div>

            @if($assignments->where('is_trained', 1)->isEmpty())
                <div class="text-center py-8 sm:py-12">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p class="text-gray-500 text-base sm:text-lg font-medium">No trainable resources</p>
                    <p class="text-gray-400 text-xs sm:text-sm mt-1">This employee hasn't been assigned to any trainable resources</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($assignments->where('is_trained', 1) as $assignment)
                        <a href="/{{ $assignment->module }}/{{ $assignment->submodule }}" 
                           class="block bg-green-50 hover:bg-green-100 p-3 sm:p-4 rounded-lg border border-green-200 transition-colors">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2">
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded w-fit">
                                            {{ $assignment->module_name }}
                                        </span>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 break-words">{{ $assignment->submodule_name }}</h3>
                                    </div>
                                    @if($assignment->created_at)
                                        <p class="text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Assigned on {{ $assignment->created_at->format('d M Y') }}
                                        </p>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Back Links -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <a href="{{ route('employees.index') }}" class="text-blue-500 hover:text-blue-600 hover:underline inline-flex items-center text-sm sm:text-base">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to All Employees
            </a>
            <a href="/" class="text-gray-500 hover:text-gray-700 hover:underline inline-flex items-center text-sm sm:text-base">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b px-4 sm:px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Employee Details</h2>
                <button onclick="toggleProfileModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Personal Information
                        </h3>
                        <div class="space-y-3">
                            @if($employeeDetails->date_of_birth)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Date of Birth:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ date('d M Y', strtotime($employeeDetails->date_of_birth)) }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->blood_group)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Blood Group:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->blood_group }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->qualification)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Qualification:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->qualification }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->aadhar_id)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Aadhar ID:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->aadhar_id }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contact & Other Information -->
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Contact & Other Details
                        </h3>
                        <div class="space-y-3">
                            @if($employeeDetails->contact_number)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Contact Number:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->contact_number }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->native_address)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base flex-shrink-0">Native Address:</span>
                                <span class="text-gray-800 text-sm sm:text-base break-words">{{ $employeeDetails->native_address }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->local_address)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base flex-shrink-0">Local Address:</span>
                                <span class="text-gray-800 text-sm sm:text-base break-words">{{ $employeeDetails->local_address }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->driving_license_no)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Driving License:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->driving_license_no }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->date_of_joining)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Date of Joining:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ date('d M Y', strtotime($employeeDetails->date_of_joining)) }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->salary)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Salary:</span>
                                <span class="text-gray-800 text-sm sm:text-base">₹{{ number_format($employeeDetails->salary, 2) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Family Information (Full Width) -->
                    @if($employeeDetails->father_no || $employeeDetails->mother_no)
                    <div class="lg:col-span-2">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Family Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            @if($employeeDetails->father_no)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Father's Contact:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->father_no }}</span>
                            </div>
                            @endif
                            
                            @if($employeeDetails->mother_no)
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0">
                                <span class="text-gray-500 font-medium sm:w-40 text-sm sm:text-base">Mother's Contact:</span>
                                <span class="text-gray-800 text-sm sm:text-base">{{ $employeeDetails->mother_no }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleProfileModal() {
            const modal = document.getElementById('profileModal');
            modal.classList.toggle('hidden');
            
            // Prevent body scroll when modal is open
            if (!modal.classList.contains('hidden')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Close modal when clicking outside
        document.getElementById('profileModal').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleProfileModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('profileModal');
                if (!modal.classList.contains('hidden')) {
                    toggleProfileModal();
                }
            }
        });
    </script>
</body>
</html>