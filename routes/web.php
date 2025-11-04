<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de productos
    Route::resource('products', ProductController::class);
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');

    // Gestión de proveedores
    Route::resource('suppliers', SupplierController::class);
    Route::get('/suppliers/export/pdf', [SupplierController::class, 'exportPdf'])->name('suppliers.export.pdf');

    // Gestión de movimientos de inventario
    Route::resource('movements', InventoryMovementController::class);
    Route::get('/movements/export/pdf', [InventoryMovementController::class, 'exportPdf'])->name('movements.export.pdf');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de autenticación (login, registro, etc.)
require __DIR__.'/auth.php';