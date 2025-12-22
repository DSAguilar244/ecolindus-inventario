<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Redirigir raíz al login para iniciar sesión directamente
// Use a route redirect (no closure) so routes can be cached in production
Route::redirect('/', '/login');

// Reports
Route::get('/reports/sales-by-customer', [App\Http\Controllers\ReportController::class, 'salesByCustomer'])->name('reports.sales_by_customer');
Route::get('/reports/sales-by-product', [App\Http\Controllers\ReportController::class, 'salesByProduct'])->name('reports.sales_by_product');
Route::get('/reports/monthly', [App\Http\Controllers\ReportsController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/monthly/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('reports.export');
    Route::post('/reports/monthly/queue', [App\Http\Controllers\ReportsController::class, 'queue'])->name('reports.queue');

// Customers
Route::resource('customers', App\Http\Controllers\CustomerController::class)->except(['show']);
Route::get('/customers/export/pdf', [App\Http\Controllers\CustomerController::class, 'exportPdf'])->name('customers.export.pdf');
Route::get('/customers/export/csv', [App\Http\Controllers\CustomerController::class, 'exportCsv'])->name('customers.export.csv');
Route::resource('brands', App\Http\Controllers\BrandController::class)->except(['show']);
Route::resource('categories', App\Http\Controllers\CategoryController::class)->except(['show']);

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de productos
    // Ajax search endpoint must be declared before resource routes to avoid shadowing by /products/{product}
    Route::get('/products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');
    Route::resource('products', ProductController::class);
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');

    // Gestión de proveedores (desactivada)
    // Route::resource('suppliers', SupplierController::class);
    // Route::get('/suppliers/export/pdf', [SupplierController::class, 'exportPdf'])->name('suppliers.export.pdf');

    // Gestión de movimientos de inventario (desactivada)
    // Route::resource('movements', InventoryMovementController::class);
    // Route::get('/movements/export/pdf', [InventoryMovementController::class, 'exportPdf'])->name('movements.export.pdf');

    // Gestión de facturas / ventas
    Route::resource('invoices', App\Http\Controllers\InvoiceController::class);
    // Permanent delete of an invoice (force delete). Only authorized users (admin) should be able to use this.
    Route::delete('/invoices/{invoice}/force', [App\Http\Controllers\InvoiceController::class, 'forceDestroy'])->name('invoices.forceDestroy');
    Route::get('/invoices/export/pdf', [App\Http\Controllers\InvoiceController::class, 'exportPdf'])->name('invoices.export.pdf');
    Route::get('/invoices/{invoice}/print', [App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('/invoices/{invoice}/reopen', [App\Http\Controllers\InvoiceController::class, 'reopen'])->name('invoices.reopen');
    Route::patch('/invoices/{invoice}/update-number', [App\Http\Controllers\InvoiceController::class, 'updateInvoiceNumber'])->name('invoices.updateNumber');
    
    // Invoice payment management
    Route::post('/invoices/{invoice}/payments', [App\Http\Controllers\InvoicePaymentController::class, 'store'])->name('invoice_payments.store');
    Route::get('/invoices/{invoice}/payments/edit', [App\Http\Controllers\InvoicePaymentController::class, 'edit'])->name('invoice_payments.edit');

    // Cash session management
    Route::post('/cash-sessions/open', [App\Http\Controllers\CashSessionController::class, 'open'])->name('cash_sessions.open');
    Route::post('/cash-sessions/close', [App\Http\Controllers\CashSessionController::class, 'close'])->name('cash_sessions.close');
    Route::get('/cash-sessions/summary', [App\Http\Controllers\CashSessionController::class, 'summary'])->name('cash_sessions.summary');
    Route::get('/cash-sessions/export-pdf', [App\Http\Controllers\CashSessionController::class, 'exportPdf'])->name('cash_sessions.exportPdf');
    Route::get('/cash-sessions/history', [App\Http\Controllers\CashSessionController::class, 'history'])->name('cash_sessions.history');
    Route::post('/cash-sessions/clear-history', [App\Http\Controllers\CashSessionController::class, 'clearHistory'])->name('cash_sessions.clearHistory');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin management routes (user roles)
Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->middleware('can:manage-users')->group(function () {
        Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::patch('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    });
});

// Ajax search endpoints (authenticated) — customers search kept separately
Route::middleware('auth')->group(function () {
    Route::get('/customers/search', [App\Http\Controllers\CustomerController::class, 'search'])->name('customers.search');
});

// Rutas de autenticación (login, registro, etc.)
require __DIR__.'/auth.php';
