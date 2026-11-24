<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest:operator')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth:operator')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::patch('/inventory/slots/{slot}', [InventoryController::class, 'update'])
        ->where('slot', '[A-Za-z][A-Za-z0-9]{1,5}')
        ->name('inventory.slots.update');
    Route::get('/sales', SalesController::class)->name('sales');
    Route::get('/machine', MachineController::class)->name('machine');
    Route::get('/alerts', AlertController::class)->name('alerts');
    Route::get('/reports', ReportController::class)->name('reports');
});

Route::view('/offline', 'errors.offline')->name('offline');
Route::view('/maintenance', 'errors.503')->name('maintenance');
Route::view('/server-error', 'errors.500')->name('server-error');
Route::fallback(fn () => response()->view('errors.404', [], 404));
