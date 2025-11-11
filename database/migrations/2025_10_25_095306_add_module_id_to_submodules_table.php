<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('submodules', function (Blueprint $table) {
        if (!Schema::hasColumn('submodules', 'module_id')) {
            $table->foreignId('module_id')->after('id')->constrained('modules')->onDelete('cascade');
        }
        if (!Schema::hasColumn('submodules', 'is_dynamic')) {
            $table->boolean('is_dynamic')->default(false);
        }
    });
}

    public function down(): void
    {
        Schema::dropIfExists('submodules');
    }
};