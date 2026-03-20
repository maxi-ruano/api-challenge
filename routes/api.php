<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\Controllers\BranchController;
use App\Infrastructure\Http\Controllers\EmployeeController;

Route::get('branches/search', [BranchController::class, 'search']);
Route::apiResource('branches', BranchController::class);
Route::apiResource('employees', EmployeeController::class);