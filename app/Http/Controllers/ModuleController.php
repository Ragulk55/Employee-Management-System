<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Submodule;
use App\Models\Product;
use App\Models\Module as DynamicModule;
use App\Models\DynamicSubmodule;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    /**
     * Show the submodules for a given module
     */
    public function show($module)
    {
        // Check if it's a dynamic module first
        $dynamicModule = DynamicModule::where('slug', $module)->first();
        
        if ($dynamicModule) {
            return $this->showDynamicModule($dynamicModule, $module);
        }

        // Handle Production Module
        if ($module === 'production') {
            return $this->showProductionModule();
        }

        // Handle Legacy Modules
        return $this->showLegacyModule($module);
    }

    /**
     * Show dynamic module submodules
     */
    private function showDynamicModule($dynamicModule, $module)
    {
        $dynamicSubmodules = $dynamicModule->dynamicSubmodules()->active()->ordered()->get();
        
        $submodules = $dynamicSubmodules->map(function ($submodule) use ($module) {
            return [
                'id' => $submodule->id,
                'name' => $submodule->name,
                'slug' => $submodule->slug,
                'route' => "/{$module}/{$submodule->slug}",
                'is_dynamic' => true
            ];
        });

        return view('submodules', [
            'module' => $dynamicModule->name,
            'moduleSlug' => $module,
            'submodules' => $submodules,
            'isDynamic' => true,
            'moduleId' => $dynamicModule->id
        ]);
    }

    /**
     * Show production module (products)
     */
    private function showProductionModule()
    {
        $products = Product::orderBy('id', 'asc')->get();

        $submodules = $products->map(function ($product) {
            return [
                'name' => $product->name,
                'slug' => $product->id,
                'route' => "/production/{$product->id}"
            ];
        });

        return view('submodules', [
            'module' => 'Production',
            'moduleSlug' => 'production',
            'submodules' => $submodules,
            'isDynamic' => false
        ]);
    }

    /**
     * Show legacy module submodules
     */
    private function showLegacyModule($module)
    {
        $legacySubmodules = [
            'onsite-service' => [
                ['name' => 'Add Fire Alarm', 'slug' => 'add-fire-alarm'],
                ['name' => 'Add PA', 'slug' => 'add-pa'],
                ['name' => 'Add Talkback', 'slug' => 'add-talkback'],
                ['name' => 'Conv Fire Alarm', 'slug' => 'conv-fire-alarm'],
                ['name' => 'Conv LPG System', 'slug' => 'conv-lpg'],
                ['name' => 'Add LPG System', 'slug' => 'add-lpg'],
                ['name' => 'Networkable Fire Alarm with Repeator Panel', 'slug' => 'networkable-fire-alarm'],
            ],
            'materials' => [
                ['name' => 'Materials Planning', 'slug' => 'planning'],
                ['name' => 'Store Process', 'slug' => 'store'],
                ['name' => 'Purchase', 'slug' => 'purchase'],
            ],
            'it' => [
                ['name' => 'Amudhu App', 'slug' => 'amudhu-app'],
                ['name' => 'Website Design and Deployment', 'slug' => 'website'],
                ['name' => 'ERP using PHP', 'slug' => 'erp'],
            ],
            'marketing' => [
                ['name' => 'Datasheet Preparation', 'slug' => 'datasheet'],
                ['name' => 'User Manual Preparation', 'slug' => 'manual'],
                ['name' => 'Catalog Preparation', 'slug' => 'catalog'],
            ],
            'sales' => [
                ['name' => 'Quotation Preparation', 'slug' => 'quotation'],
                ['name' => 'Bill Preparation', 'slug' => 'bill'],
                ['name' => 'Payment Follow-up', 'slug' => 'payment'],
            ],
            'accounts' => [
                ['name' => 'Accounts Entry', 'slug' => 'entry'],
                ['name' => 'GST Filing', 'slug' => 'gst'],
            ],
            'rnd' => [
                ['name' => 'Schematic Preparation', 'slug' => 'schematic'],
                ['name' => 'PCB Designing', 'slug' => 'pcb'],
            ],
        ];

        if (!isset($legacySubmodules[$module])) {
            abort(404, 'Module not found');
        }

        $submodules = array_map(function ($sub) use ($module) {
            return [
                'name' => $sub['name'],
                'slug' => $sub['slug'],
                'route' => "/{$module}/{$sub['slug']}",
                'is_dynamic' => false
            ];
        }, $legacySubmodules[$module]);

        $moduleDisplayName = $this->getModuleDisplayName($module);

        return view('submodules', [
            'module' => $moduleDisplayName,
            'moduleSlug' => $module,
            'submodules' => $submodules,
            'isDynamic' => false
        ]);
    }

    /**
     * Get formatted module display name
     */
    private function getModuleDisplayName($module)
    {
        return match($module) {
            'rnd' => 'R&D',
            'onsite-service' => 'Onsite Service',
            'materials' => 'Materials',
            'it' => 'IT',
            'marketing' => 'Marketing',
            'sales' => 'Sales',
            'accounts' => 'Accounts',
            default => ucfirst(str_replace('-', ' ', $module))
        };
    }

    /**
     * Show detail page for a specific submodule
     */
    public function detail($module, $sub)
    {
        // Get allowed departments for filtering employees
        $allowedDepartments = $this->getModuleDepartments($module);

        // Filter employees by department
        $employees = $this->getEmployeesByDepartments($allowedDepartments);

        // Check if it's a dynamic module
        $dynamicModule = DynamicModule::where('slug', $module)->first();
        
        if ($dynamicModule) {
            return $this->showDynamicModuleDetail($dynamicModule, $module, $sub, $employees, $allowedDepartments);
        }

        // Handle Production Module
        if ($module === 'production') {
            return $this->showProductionDetail($module, $sub, $employees, $allowedDepartments);
        }

        // Handle Legacy Modules
        return $this->showLegacyModuleDetail($module, $sub, $employees, $allowedDepartments);
    }

    /**
     * Get departments mapped to a module
     */
    private function getModuleDepartments($module)
    {
        $mapping = [
            'production' => ['Production', 'AMC'],
            'onsite-service' => ['AMC'],
            'materials' => ['Materials'],
            'it' => ['IT'],
            'marketing' => ['Marketing'],
            'sales' => ['Sales'],
            'accounts' => ['Accounts'],
            'rnd' => ['R&D']
        ];

        return $mapping[$module] ?? [];
    }

    /**
     * Get employees filtered by departments
     */
    private function getEmployeesByDepartments($departments)
    {
        if (empty($departments)) {
            return Employee::all();
        }

        return Employee::where(function($query) use ($departments) {
            foreach ($departments as $department) {
                $query->orWhere('department', 'LIKE', '%' . $department . '%');
            }
        })->get();
    }

    /**
     * Get assigned and trained employees for a submodule
     */
    private function getSubmoduleEmployees($module, $sub)
    {
        // First get all relevant submodule assignments from the mysql database
        $assignments = Submodule::on('mysql')
            ->where('module', $module)
            ->where('submodule', $sub)
            ->get();
        
        // Get employee IDs from assignments
        $untrainedIds = $assignments->where('is_trained', 0)->pluck('employee_id')->toArray();
        $trainedIds = $assignments->where('is_trained', 1)->pluck('employee_id')->toArray();

        // Now get the employees from the attendance database
        $assignedEmployees = Employee::whereIn('id', $untrainedIds)
            ->get()
            ->map(function ($employee) use ($assignments) {
                $assignment = $assignments->where('employee_id', $employee->id)
                    ->where('is_trained', 0)
                    ->first();
                $employee->assignment_date = $assignment ? $assignment->created_at : null;
                return $employee;
            });

        $trainedEmployees = Employee::whereIn('id', $trainedIds)
            ->get()
            ->map(function ($employee) use ($assignments) {
                $assignment = $assignments->where('employee_id', $employee->id)
                    ->where('is_trained', 1)
                    ->first();
                $employee->assignment_date = $assignment ? $assignment->created_at : null;
                return $employee;
            });

        return [
            'assigned' => $assignedEmployees,
            'trained' => $trainedEmployees
        ];
    }

    /**
     * Show dynamic module detail
     */
    private function showDynamicModuleDetail($dynamicModule, $module, $sub, $employees, $allowedDepartments)
    {
        $dynamicSubmodule = $dynamicModule->dynamicSubmodules()
            ->where('slug', $sub)
            ->firstOrFail();

        $submoduleEmployees = $this->getSubmoduleEmployees($module, $sub);

        return view('detail', [
            'module' => $dynamicModule->name,
            'moduleSlug' => $module,
            'subSlug' => $sub,
            'submodule' => (object)[
                'name' => $dynamicSubmodule->name,
                'route' => "/$module/$sub",
                'employees' => $submoduleEmployees['assigned'],
                'trainedEmployees' => $submoduleEmployees['trained']
            ],
            'employees' => $employees,
            'assignedEmployeeIds' => $submoduleEmployees['assigned']->pluck('id')->toArray(),
            'trainedEmployeeIds' => $submoduleEmployees['trained']->pluck('id')->toArray(),
            'isDynamic' => true,
            'currentDepartments' => $allowedDepartments
        ]);
    }

    /**
     * Show production module detail
     */
    private function showProductionDetail($module, $sub, $employees, $allowedDepartments)
    {
        $product = Product::find($sub);

        if (!$product) {
            abort(404, 'Product not found');
        }

        $submoduleEmployees = $this->getSubmoduleEmployees($module, $sub);

        return view('detail', [
            'module' => 'Production',
            'moduleSlug' => $module,
            'subSlug' => $sub,
            'submodule' => (object)[
                'name' => $product->name,
                'route' => "/$module/$sub",
                'employees' => $submoduleEmployees['assigned'],
                'trainedEmployees' => $submoduleEmployees['trained']
            ],
            'employees' => $employees,
            'assignedEmployeeIds' => $submoduleEmployees['assigned']->pluck('id')->toArray(),
            'trainedEmployeeIds' => $submoduleEmployees['trained']->pluck('id')->toArray(),
            'currentDepartments' => $allowedDepartments
        ]);
    }

    /**
     * Show legacy module detail
     */
    private function showLegacyModuleDetail($module, $sub, $employees, $allowedDepartments)
    {
        $submodulesList = [
            'onsite-service' => [
                'add-fire-alarm' => 'Add Fire Alarm',
                'add-pa' => 'Add PA',
                'add-talkback' => 'Add Talkback',
                'conv-fire-alarm' => 'Conv Fire Alarm',
                'conv-lpg' => 'Conv LPG System',
                'add-lpg' => 'Add LPG System',
                'networkable-fire-alarm' => 'Networkable Fire Alarm with Repeator Panel',
            ],
            'materials' => [
                'planning' => 'Materials Planning',
                'store' => 'Store Process',
                'purchase' => 'Purchase',
            ],
            'it' => [
                'amudhu-app' => 'Amudhu App',
                'website' => 'Website Design and Deployment',
                'erp' => 'ERP using PHP',
            ],
            'marketing' => [
                'datasheet' => 'Datasheet Preparation',
                'manual' => 'User Manual Preparation',
                'catalog' => 'Catalog Preparation',
            ],
            'sales' => [
                'quotation' => 'Quotation Preparation',
                'bill' => 'Bill Preparation',
                'payment' => 'Payment Follow-up',
            ],
            'accounts' => [
                'entry' => 'Accounts Entry',
                'gst' => 'GST Filing',
            ],
            'rnd' => [
                'schematic' => 'Schematic Preparation',
                'pcb' => 'PCB Designing',
            ],
        ];

        $submoduleName = $submodulesList[$module][$sub] ?? null;

        if (!$submoduleName) {
            abort(404, 'Submodule not found');
        }

        $submoduleEmployees = $this->getSubmoduleEmployees($module, $sub);
        $moduleDisplayName = $this->getModuleDisplayName($module);

        return view('detail', [
            'module' => $moduleDisplayName,
            'moduleSlug' => $module,
            'subSlug' => $sub,
            'submodule' => (object)[
                'name' => $submoduleName,
                'route' => "/$module/$sub",
                'employees' => $submoduleEmployees['assigned'],
                'trainedEmployees' => $submoduleEmployees['trained']
            ],
            'employees' => $employees,
            'assignedEmployeeIds' => $submoduleEmployees['assigned']->pluck('id')->toArray(),
            'trainedEmployeeIds' => $submoduleEmployees['trained']->pluck('id')->toArray(),
            'currentDepartments' => $allowedDepartments
        ]);
    }

    /**
     * Add an employee to a submodule
     */
    public function addEmployee(Request $request, $module, $sub)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'is_trained' => 'required'
        ]);

        $isTrained = (int) $request->is_trained;

        // Check if employee is already assigned (regardless of training status)
        $existingAssignment = Submodule::where('module', $module)
            ->where('submodule', $sub)
            ->where('employee_id', $request->employee_id)
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('error', 'Employee is already assigned to this submodule!');
        }

        // Get module_id if it's a dynamic module
        $moduleId = null;
        $dynamicModule = DynamicModule::where('slug', $module)->first();
        
        if ($dynamicModule) {
            $moduleId = $dynamicModule->id;
        }

        // Create the assignment
        $data = [
            'module' => $module,
            'submodule' => $sub,
            'employee_id' => $request->employee_id,
            'is_trained' => $isTrained,
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Add module_id if it exists
        if ($moduleId !== null) {
            $data['module_id'] = $moduleId;
        }

        Submodule::create($data);

        return redirect()->back()->with('success', 'Employee added successfully!');
    }

    /**
     * Remove an employee from a submodule
     */
    public function removeEmployee(Request $request, $module, $sub)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'is_trained' => 'required|boolean'
        ]);

        $deleted = Submodule::where('module', $module)
            ->where('submodule', $sub)
            ->where('employee_id', $request->employee_id)
            ->where('is_trained', $request->is_trained)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Employee removed successfully!');
        }

        return redirect()->back()->with('error', 'Employee assignment not found!');
    }
}