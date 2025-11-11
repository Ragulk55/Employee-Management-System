<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\DynamicSubmodule;
use App\Models\Submodule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ITModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting IT Module Seeder...');

        try {
            DB::beginTransaction();

            // Check if 'order' column exists in modules table
            $hasOrderColumn = Schema::hasColumn('modules', 'order');
            
            // Step 1: Create or get IT Module
            $moduleData = [
                'name' => 'IT',
                'slug' => 'it',
                'is_active' => true
            ];
            
            // Only add order if column exists
            if ($hasOrderColumn) {
                $moduleData['order'] = Module::max('order') + 1;
            }
            
            $itModule = Module::updateOrCreate(
                ['slug' => 'it'],
                $moduleData
            );

            $this->command->info("✓ IT Module created (ID: {$itModule->id})");

            // Step 2: Define IT Submodules
            $itSubmodules = [
                ['name' => 'Amudhu App', 'slug' => 'amudhu-app'],
                ['name' => 'Website Design and Deployment', 'slug' => 'website'],
                ['name' => 'ERP using PHP', 'slug' => 'erp'],
            ];

            // Check if 'order' column exists in dynamic_submodules table
            $hasSubmoduleOrderColumn = Schema::hasColumn('dynamic_submodules', 'order');

            // Step 3: Create Dynamic Submodules
            foreach ($itSubmodules as $index => $submoduleData) {
                $dynamicSubmoduleData = [
                    'name' => $submoduleData['name'],
                    'slug' => $submoduleData['slug'],
                    'is_active' => true
                ];
                
                // Only add order if column exists
                if ($hasSubmoduleOrderColumn) {
                    $dynamicSubmoduleData['order'] = $index + 1;
                }
                
                $dynamicSubmodule = DynamicSubmodule::updateOrCreate(
                    [
                        'module_id' => $itModule->id,
                        'slug' => $submoduleData['slug']
                    ],
                    $dynamicSubmoduleData
                );

                $this->command->info("  ✓ Created submodule: {$dynamicSubmodule->name}");

                // Step 4: Update existing employee assignments
                $updated = Submodule::where('module', 'it')
                    ->where('submodule', $submoduleData['slug'])
                    ->update(['module_id' => $itModule->id]);

                if ($updated > 0) {
                    $this->command->info("    → Updated {$updated} employee assignments");
                }
            }

            DB::commit();

            $this->command->info('');
            $this->command->info('═══════════════════════════════════════════');
            $this->command->info('✓ IT Module successfully converted to dynamic!');
            $this->command->info("  Module ID: {$itModule->id}");
            $this->command->info("  Submodules: " . count($itSubmodules));
            $this->command->info('═══════════════════════════════════════════');
            $this->command->info('');
            $this->command->info('You can now:');
            $this->command->info('  • Add new submodules via the UI');
            $this->command->info('  • Delete existing submodules');
            $this->command->info('  • Visit: /it to see the changes');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Error: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}