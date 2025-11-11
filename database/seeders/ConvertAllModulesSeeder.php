<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\DynamicSubmodule;
use App\Models\Submodule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertAllModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  Converting All Legacy Modules to Dynamic');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->newLine();

        // Check if 'order' column exists
        $hasOrderColumn = Schema::hasColumn('modules', 'order');
        $hasSubOrderColumn = Schema::hasColumn('dynamic_submodules', 'order');

        // Define all modules with their submodules
        $allModules = [
            'onsite-service' => [
                'name' => 'Onsite Service',
                'submodules' => [
                    ['name' => 'Add Fire Alarm', 'slug' => 'add-fire-alarm'],
                    ['name' => 'Add PA', 'slug' => 'add-pa'],
                    ['name' => 'Add Talkback', 'slug' => 'add-talkback'],
                    ['name' => 'Conv Fire Alarm', 'slug' => 'conv-fire-alarm'],
                    ['name' => 'Conv LPG System', 'slug' => 'conv-lpg'],
                    ['name' => 'Add LPG System', 'slug' => 'add-lpg'],
                    ['name' => 'Networkable Fire Alarm with Repeator Panel', 'slug' => 'networkable-fire-alarm'],
                ]
            ],
            'materials' => [
                'name' => 'Materials',
                'submodules' => [
                    ['name' => 'Materials Planning', 'slug' => 'planning'],
                    ['name' => 'Store Process', 'slug' => 'store'],
                    ['name' => 'Purchase', 'slug' => 'purchase'],
                ]
            ],
            'it' => [
                'name' => 'IT',
                'submodules' => [
                    ['name' => 'Amudhu App', 'slug' => 'amudhu-app'],
                    ['name' => 'Website Design and Deployment', 'slug' => 'website'],
                    ['name' => 'ERP using PHP', 'slug' => 'erp'],
                ]
            ],
            'marketing' => [
                'name' => 'Marketing',
                'submodules' => [
                    ['name' => 'Datasheet Preparation', 'slug' => 'datasheet'],
                    ['name' => 'User Manual Preparation', 'slug' => 'manual'],
                    ['name' => 'Catalog Preparation', 'slug' => 'catalog'],
                ]
            ],
            'sales' => [
                'name' => 'Sales',
                'submodules' => [
                    ['name' => 'Quotation Preparation', 'slug' => 'quotation'],
                    ['name' => 'Bill Preparation', 'slug' => 'bill'],
                    ['name' => 'Payment Follow-up', 'slug' => 'payment'],
                ]
            ],
            'accounts' => [
                'name' => 'Accounts',
                'submodules' => [
                    ['name' => 'Accounts Entry', 'slug' => 'entry'],
                    ['name' => 'GST Filing', 'slug' => 'gst'],
                ]
            ],
            'rnd' => [
                'name' => 'R&D',
                'submodules' => [
                    ['name' => 'Schematic Preparation', 'slug' => 'schematic'],
                    ['name' => 'PCB Designing', 'slug' => 'pcb'],
                ]
            ]
        ];

        try {
            DB::beginTransaction();

            $moduleOrder = 1;
            $totalModules = 0;
            $totalSubmodules = 0;

            foreach ($allModules as $slug => $moduleData) {
                $this->command->info("Processing: {$moduleData['name']} ({$slug})");

                // Create module data
                $createData = [
                    'name' => $moduleData['name'],
                    'slug' => $slug,
                    'is_active' => true
                ];

                // Add order if column exists
                if ($hasOrderColumn) {
                    $createData['order'] = $moduleOrder++;
                }

                // Create or update module
                $module = Module::updateOrCreate(
                    ['slug' => $slug],
                    $createData
                );

                $this->command->line("  ✓ Module created/updated (ID: {$module->id})");
                $totalModules++;

                // Create submodules
                foreach ($moduleData['submodules'] as $index => $submoduleData) {
                    $subCreateData = [
                        'name' => $submoduleData['name'],
                        'slug' => $submoduleData['slug'],
                        'is_active' => true
                    ];

                    // Add order if column exists
                    if ($hasSubOrderColumn) {
                        $subCreateData['order'] = $index + 1;
                    }

                    $dynamicSubmodule = DynamicSubmodule::updateOrCreate(
                        [
                            'module_id' => $module->id,
                            'slug' => $submoduleData['slug']
                        ],
                        $subCreateData
                    );

                    $this->command->line("    • {$dynamicSubmodule->name}");
                    $totalSubmodules++;

                    // Update existing employee assignments
                    $updated = Submodule::where('module', $slug)
                        ->where('submodule', $submoduleData['slug'])
                        ->whereNull('module_id')
                        ->update(['module_id' => $module->id]);

                    if ($updated > 0) {
                        $this->command->line("      → Updated {$updated} employee assignments");
                    }
                }

                $this->command->newLine();
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('═══════════════════════════════════════════');
            $this->command->info('✓ ALL MODULES CONVERTED SUCCESSFULLY!');
            $this->command->info('═══════════════════════════════════════════');
            $this->command->info("Total Modules: {$totalModules}");
            $this->command->info("Total Submodules: {$totalSubmodules}");
            $this->command->newLine();
            $this->command->info('All modules now support:');
            $this->command->line('  • Adding new submodules via UI');
            $this->command->line('  • Deleting submodules');
            $this->command->line('  • Managing employee assignments');
            $this->command->newLine();
            $this->command->info('NOTE: You might see duplicate modules on dashboard.');
            $this->command->info('Remove the old hardcoded "IT" from your dashboard display logic.');
            $this->command->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Error: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}