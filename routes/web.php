<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\DynamicSubmoduleController;
use App\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (Home)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Module Management Routes
Route::post('/modules', [DashboardController::class, 'store'])->name('module.store');
Route::delete('/modules/{id}', [DashboardController::class, 'destroy'])->name('module.destroy');

// Employee Routes (must come before module routes to avoid conflicts)
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/export', [EmployeeController::class, 'exportToExcel'])->name('employees.export');
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');

// Dynamic Submodule Management Routes
Route::post('/{module}/submodules', [DynamicSubmoduleController::class, 'store'])->name('submodule.store');
Route::delete('/{module}/submodules/{id}', [DynamicSubmoduleController::class, 'destroy'])->name('submodule.destroy');

// Employee Assignment Routes (for all modules including production and dynamic)
Route::post('/{module}/{sub}/add-employee', [ModuleController::class, 'addEmployee'])->name('submodule.addEmployee');
Route::delete('/{module}/{sub}/remove-employee', [ModuleController::class, 'removeEmployee'])->name('submodule.removeEmployee');

// Module detail page - shows employee assignment (MUST come before module show)
Route::get('/{module}/{sub}', [ModuleController::class, 'detail'])->name('module.detail');

// Module list page - shows list of submodules/products (MUST come last)
Route::get('/{module}', [ModuleController::class, 'show'])->name('module.show');