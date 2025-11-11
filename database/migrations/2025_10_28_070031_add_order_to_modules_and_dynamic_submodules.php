<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'order' column to modules table if it doesn't exist
        if (Schema::hasTable('modules') && !Schema::hasColumn('modules', 'order')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->integer('order')->default(0)->after('is_active');
            });
        }

        // Add 'order' column to dynamic_submodules table if it doesn't exist
        if (Schema::hasTable('dynamic_submodules') && !Schema::hasColumn('dynamic_submodules', 'order')) {
            Schema::table('dynamic_submodules', function (Blueprint $table) {
                $table->integer('order')->default(0)->after('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'order' column from modules table if it exists
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'order')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }

        // Remove 'order' column from dynamic_submodules table if it exists
        if (Schema::hasTable('dynamic_submodules') && Schema::hasColumn('dynamic_submodules', 'order')) {
            Schema::table('dynamic_submodules', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};