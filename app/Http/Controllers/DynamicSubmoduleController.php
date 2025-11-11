<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Module;
use App\Models\DynamicSubmodule;
use App\Models\Submodule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DynamicSubmoduleController extends Controller
{
    /**
     * Store a newly created submodule
     */
    public function store(Request $request, $module)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id'
        ], [
            'name.required' => 'Submodule name is required.',
            'name.max' => 'Submodule name must not exceed 255 characters.',
            'module_id.required' => 'Module ID is required.',
            'module_id.exists' => 'Selected module does not exist.'
        ]);

        try {
            // Create slug from name
            $slug = Str::slug($validated['name']);
            
            // Ensure slug is not empty
            if (empty($slug)) {
                return redirect()
                    ->back()
                    ->with('error', 'Invalid submodule name. Please use alphanumeric characters.')
                    ->withInput();
            }
            
            // Check if slug already exists for this module
            $exists = DynamicSubmodule::where('module_id', $validated['module_id'])
                ->where('slug', $slug)
                ->exists();
            
            if ($exists) {
                return redirect()
                    ->back()
                    ->with('error', 'A submodule with this name already exists!')
                    ->withInput();
            }

            // Check if name already exists (case-insensitive)
            $nameExists = DynamicSubmodule::where('module_id', $validated['module_id'])
                ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
                ->exists();
            
            if ($nameExists) {
                return redirect()
                    ->back()
                    ->with('error', 'A submodule with a similar name already exists!')
                    ->withInput();
            }

            // Prepare data for creating submodule
            $submoduleData = [
                'module_id' => $validated['module_id'],
                'name' => trim($validated['name']),
                'slug' => $slug,
                'is_active' => true
            ];

            // Add order only if column exists
            if (Schema::hasColumn('dynamic_submodules', 'order')) {
                $maxOrder = DynamicSubmodule::where('module_id', $validated['module_id'])
                    ->max('order') ?? 0;
                $submoduleData['order'] = $maxOrder + 1;
            }

            // Create the dynamic submodule
            $submodule = DynamicSubmodule::create($submoduleData);

            Log::info('Dynamic submodule created', [
                'module_id' => $validated['module_id'],
                'submodule_id' => $submodule->id,
                'name' => $submodule->name,
                'slug' => $submodule->slug
            ]);

            return redirect()
                ->route('module.show', ['module' => $module])
                ->with('success', "Submodule '{$submodule->name}' created successfully!");

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error creating submodule', [
                'error' => $e->getMessage(),
                'module' => $module,
                'data' => $validated
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Database error: Unable to create submodule. Please try again.')
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error creating submodule', [
                'error' => $e->getMessage(),
                'module' => $module,
                'data' => $validated
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to create submodule: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a submodule
     */
    public function destroy($module, $id)
    {
        try {
            // Find the dynamic submodule
            $submodule = DynamicSubmodule::findOrFail($id);
            
            // Store name for success message
            $submoduleName = $submodule->name;
            $submoduleSlug = $submodule->slug;

            // Start database transaction
            DB::beginTransaction();

            try {
                // Delete all employee assignments for this submodule
                $deletedAssignments = Submodule::where('module', $module)
                    ->where('submodule', $submoduleSlug)
                    ->delete();

                // Delete the submodule
                $submodule->delete();

                // Commit the transaction
                DB::commit();

                Log::info('Dynamic submodule deleted', [
                    'module' => $module,
                    'submodule_id' => $id,
                    'name' => $submoduleName,
                    'slug' => $submoduleSlug,
                    'deleted_assignments' => $deletedAssignments
                ]);

                return redirect()
                    ->route('module.show', ['module' => $module])
                    ->with('success', "Submodule '{$submoduleName}' deleted successfully! ({$deletedAssignments} employee assignments removed)");

            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Submodule not found for deletion', [
                'module' => $module,
                'id' => $id
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Submodule not found. It may have already been deleted.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting submodule', [
                'error' => $e->getMessage(),
                'module' => $module,
                'id' => $id
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete submodule: ' . $e->getMessage());
        }
    }

    /**
     * Update submodule order
     */
    public function updateOrder(Request $request, $module)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:dynamic_submodules,id',
            'orders.*.order' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['orders'] as $orderData) {
                DynamicSubmodule::where('id', $orderData['id'])
                    ->update(['order' => $orderData['order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating submodule order', [
                'error' => $e->getMessage(),
                'module' => $module
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order'
            ], 500);
        }
    }

    /**
     * Toggle submodule active status
     */
    public function toggleActive($module, $id)
    {
        try {
            $submodule = DynamicSubmodule::findOrFail($id);
            $submodule->is_active = !$submodule->is_active;
            $submodule->save();

            $status = $submodule->is_active ? 'activated' : 'deactivated';

            Log::info('Submodule status toggled', [
                'module' => $module,
                'submodule_id' => $id,
                'new_status' => $submodule->is_active
            ]);

            return redirect()
                ->back()
                ->with('success', "Submodule '{$submodule->name}' {$status} successfully!");

        } catch (\Exception $e) {
            Log::error('Error toggling submodule status', [
                'error' => $e->getMessage(),
                'module' => $module,
                'id' => $id
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to update submodule status.');
        }
    }
}