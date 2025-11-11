<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all active modules from database
        $dynamicModules = Module::active()->ordered()->get();

        // Get slugs of dynamic modules to avoid duplicates
        $dynamicSlugs = $dynamicModules->pluck('slug')->toArray();

        // Legacy hardcoded modules (only show if NOT in database)
        $legacyModules = [
            [
                'name' => 'Onsite Service',
                'slug' => 'onsite-service',
                'route' => '/onsite-service',
                'icon' => '🔧',
                'color' => 'bg-blue-500',
                'is_legacy' => true
            ],
            [
                'name' => 'Materials',
                'slug' => 'materials',
                'route' => '/materials',
                'icon' => '📦',
                'color' => 'bg-green-500',
                'is_legacy' => true
            ],
            [
                'name' => 'IT',
                'slug' => 'it',
                'route' => '/it',
                'icon' => '💻',
                'color' => 'bg-purple-500',
                'is_legacy' => true
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'route' => '/marketing',
                'icon' => '📊',
                'color' => 'bg-pink-500',
                'is_legacy' => true
            ],
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'route' => '/sales',
                'icon' => '💰',
                'color' => 'bg-yellow-500',
                'is_legacy' => true
            ],
            [
                'name' => 'Accounts',
                'slug' => 'accounts',
                'route' => '/accounts',
                'icon' => '📈',
                'color' => 'bg-red-500',
                'is_legacy' => true
            ],
            [
                'name' => 'R&D',
                'slug' => 'rnd',
                'route' => '/rnd',
                'icon' => '🔬',
                'color' => 'bg-indigo-500',
                'is_legacy' => true
            ],
            [
                'name' => 'Production',
                'slug' => 'production',
                'route' => '/production',
                'icon' => '🏭',
                'color' => 'bg-orange-500',
                'is_legacy' => true
            ]
        ];

        // Filter out legacy modules that now exist in database
        $legacyModules = array_filter($legacyModules, function($module) use ($dynamicSlugs) {
            return !in_array($module['slug'], $dynamicSlugs);
        });

        // Format dynamic modules
        $formattedDynamicModules = $dynamicModules->map(function($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'route' => '/' . $module->slug,
                'icon' => $module->icon ?? '📋',
                'color' => $module->color ?? 'bg-gray-500',
                'description' => $module->description,
                'is_legacy' => false,
                'is_dynamic' => true  // Mark as dynamic for delete button
            ];
        });

        // Combine legacy and dynamic modules
        $allModules = $formattedDynamicModules->concat(collect($legacyModules));

        return view('dashboard', [
            'modules' => $allModules,
            'dynamicModules' => $dynamicModules
        ]);
    }

    /**
     * Store a new module (simplified - name only)
     * Automatically redirects to the module's submodule page
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:modules,name'
        ]);

        $slug = Module::generateSlug($request->name);

        // Check if slug already exists
        if (Module::where('slug', $slug)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A module with this name already exists!');
        }

        // Auto-assign a random color
        $colors = [
            'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500',
            'bg-teal-500', 'bg-blue-500', 'bg-indigo-500', 'bg-purple-500',
            'bg-pink-500', 'bg-cyan-500', 'bg-lime-500', 'bg-emerald-500'
        ];
        
        // Auto-assign a random icon
        $icons = ['📋', '⚙️', '🎯', '✨', '🚀', '💡', '🔔', '📊', '🏆', '⭐', '🎨', '🔧'];

        $module = Module::create([
            'name' => $request->name,
            'slug' => $slug,
            'icon' => $icons[array_rand($icons)],
            'color' => $colors[array_rand($colors)],
            'description' => null,
            'is_active' => true,
            'sort_order' => Module::max('sort_order') + 1
        ]);

        \Log::info("Module created: {$module->name} (ID: {$module->id}, Slug: {$module->slug})");

        // Redirect directly to the module's submodule page
        return redirect()->route('module.show', ['module' => $module->slug])
            ->with('success', "Module '{$module->name}' created successfully! You can now add submodules.");
    }

    /**
     * Delete a module
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $moduleName = $module->name;
        $moduleSlug = $module->slug;

        // Delete all employee assignments for all submodules in this module
        \DB::table('submodules')
            ->where('module', $moduleSlug)
            ->delete();

        \Log::info("Deleted all employee assignments for module: {$moduleName}");

        // Delete the module (this will cascade delete dynamic_submodules)
        $module->delete();

        \Log::info("Module deleted: {$moduleName} (ID: {$id})");

        return redirect()->route('dashboard')
            ->with('success', "Module '{$moduleName}' and all its data have been deleted successfully!");
    }
}