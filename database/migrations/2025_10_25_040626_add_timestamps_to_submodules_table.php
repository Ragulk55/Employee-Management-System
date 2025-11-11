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
        // Check if timestamps don't exist and add them
        if (!Schema::hasColumn('submodules', 'created_at')) {
            Schema::table('submodules', function (Blueprint $table) {
                $table->timestamps(); // Adds both created_at and updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submodules', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
    
};