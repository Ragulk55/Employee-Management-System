<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enterprise Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .module-card {
            transition: all 0.25s ease-in-out;
        }
        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- ===== Header ===== -->
    <header class="bg-white shadow-sm sticky top-0 z-20">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Enterprise Dashboard</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('employees.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Employees
                </a>
            </div>
        </div>
    </header>

    <!-- ===== Main Content ===== -->
    <main class="container mx-auto px-6 py-10">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 font-bold text-xl">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900 font-bold text-xl">&times;</button>
            </div>
        @endif

        <!-- Module Header with Add Button -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-700 uppercase tracking-wider">Main Modules</h3>
            <button onclick="openModuleModal()" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Module
            </button>
        </div>

        <!-- ===== Modules Grid ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($modules as $module)
            <div class="relative group">
                <a href="{{ $module['route'] }}" class="module-card bg-white rounded-xl shadow-sm border border-gray-100 hover:border-transparent hover:shadow-lg block">
                    <div class="p-6">
                        
                        <h3 class="text-xl font-semibold text-gray-800 mb-1">{{ $module['name'] }}</h3>
                        @if(isset($module['description']) && $module['description'])
                            <p class="text-sm text-gray-500 mt-2">{{ $module['description'] }}</p>
                        @endif
                    </div>
                </a>
                
                @if(!($module['is_legacy'] ?? false))
                    <form action="{{ route('module.destroy', $module['id']) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete {{ $module['name'] }}? This will also delete all its submodules and employee assignments.')"
                          class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
            @endforeach
        </div>

    </main>

    <!-- ===== Add Module Modal (Simplified - Name Only) ===== -->
    <div id="moduleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-2xl font-bold text-gray-900">Create New Module</h3>
                <button onclick="closeModuleModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('module.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Module Name *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="e.g., Quality Control"
                           autofocus>
                    <p class="text-xs text-gray-500 mt-1">Enter the module name and we'll handle the rest</p>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModuleModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded transition">
                        Create & Continue
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function openModuleModal() {
            document.getElementById('moduleModal').classList.remove('hidden');
            setTimeout(() => document.getElementById('name').focus(), 100);
        }

        function closeModuleModal() {
            document.getElementById('moduleModal').classList.add('hidden');
            document.querySelector('#moduleModal form').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('moduleModal');
            if (event.target == modal) {
                closeModuleModal();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModuleModal();
            }
        });

        // Auto-dismiss flash messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

</body>
</html>