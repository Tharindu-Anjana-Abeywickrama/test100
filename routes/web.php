<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia; 
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TerritoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FreeIssueController;
use App\Http\Controllers\SkusController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard'); 

Route::get('zones', [ZoneController::class, 'index'])->name('zones.index');
Route::post('zones', [ZoneController::class, 'store'])->name('zones.store');
Route::get('zones/{zone}/edit', [ZoneController::class, 'edit'])->name('zones.edit');
Route::put('zones/{zone}', [ZoneController::class, 'update'])->name('zones.update');
Route::delete('zones/{zone}', [ZoneController::class, 'destroy'])->name('zones.destroy'); 

Route::get('/getZones', [ZoneController::class, 'getZones']);

Route::get('regions', [RegionController::class, 'index'])->name('regions.index');
Route::post('regions', [RegionController::class, 'store'])->name('regions.store');
Route::get('regions/{region}/edit', [RegionController::class, 'edit'])->name('regions.edit');
Route::put('regions/{region}', [RegionController::class, 'update'])->name('regions.update');
Route::delete('regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');

Route::get('territories', [TerritoryController::class, 'index'])->name('territories.index');
Route::post('territories', [TerritoryController::class, 'store'])->name('territories.store');
Route::get('territories/{territory}/edit', [TerritoryController::class, 'edit'])->name('territories.edit');
Route::put('territories/{territory}', [TerritoryController::class, 'update'])->name('territories.update');
Route::delete('territories/{territory}', [TerritoryController::class, 'destroy'])->name('territories.destroy');
Route::get('/getRegions/{zone}', [TerritoryController::class, 'getRegionsByZone']);
Route::get('/getTerritories/{region}', [TerritoryController::class, 'getTerritoriesByRegion']);

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    // Add this inside your auth middleware group
    Route::resource('products', ProductController::class);

    Route::get('/discounts', [DiscountController::class, 'index'])->name('discount.index');
    Route::get('/getDiscounts', [DiscountController::class, 'getDiscounts']);
    Route::post('/discounts', [DiscountController::class, 'store']);
    Route::put('/discounts/{id}', [DiscountController::class, 'update']);
    Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
});

Route::middleware(['auth'])->group(function () {
    // Purchase Order routes - cleaned up and organized
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/view', [PurchaseOrderController::class, 'viewPurchaseOrders'])->name('purchase-orders.view');
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'getPurchaseOrders'])->name('purchase-orders.list');
    Route::get('/purchase-orders/{purchase_order}/details', [PurchaseOrderController::class, 'viewPurchaseOrder'])->name('purchase-orders.show');
    Route::get('/export-purchase-order/{purchase_order}', [PurchaseOrderController::class, 'exportPurchaseOrder'])->name('purchase-order.export');
    
    // Utility routes for Purchase Orders
    Route::get('/getRegions/{zone}', [PurchaseOrderController::class, 'getRegionsByZone']);
    Route::get('/getRegionsAll', [PurchaseOrderController::class, 'getRegionsAll']);
    Route::get('/getTerritories', [PurchaseOrderController::class, 'getTerritories']);
    Route::get('/getDistributors', [PurchaseOrderController::class, 'getDistributors']);
    Route::get('/getPOnumber', [PurchaseOrderController::class, 'generatePONumber'])->name('purchase-orders.generatePONumber');
    Route::get('/purchase-orders/{id}/view', [PurchaseOrderController::class, 'viewPurchaseOrder'])
        ->name('purchase-orders.show');
    Route::get('/purchase-orders/{id}/details', [PurchaseOrderController::class, 'viewPurchaseOrder']);
    Route::get('/export-purchase-order/{id}', [PurchaseOrderController::class, 'exportPurchaseOrder'])->name('purchase-order.export');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::post('/bulk-export-purchase-orders', [PurchaseOrderController::class, 'bulkExportPurchaseOrders']);
}); 


Route::post('login', [LoginController::class, 'login'])->name('login');
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/free-issues', [FreeIssueController::class, 'index'])->name('free-issues.index');

// Invoice routes
Route::middleware(['auth'])->group(function () {
    Route::get('/invoice', [App\Http\Controllers\InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/{id}', [App\Http\Controllers\InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/create/{id}', [App\Http\Controllers\InvoiceController::class, 'createFromPurchaseOrder'])->name('invoice.create');
    Route::post('/invoice', [App\Http\Controllers\InvoiceController::class, 'store'])->name('invoice.store');
    Route::put('/invoice/{id}/status', [App\Http\Controllers\InvoiceController::class, 'updateStatus'])->name('invoice.update-status');
    Route::get('/generate-invoice-number', [App\Http\Controllers\InvoiceController::class, 'generateInvoiceNumber'])->name('invoice.generate-number');
    Route::get('/get-purchase-order-items/{id}', [App\Http\Controllers\InvoiceController::class, 'getPurchaseOrderItems'])->name('invoice.get-purchase-order-items');
});
Route::post('/free-issues', [FreeIssueController::class, 'store'])->name('free-issues.store');
Route::put('/free-issues/{id}', [FreeIssueController::class, 'update'])->name('free-issues.update');
Route::delete('/free-issues/{id}', [FreeIssueController::class, 'destroy'])->name('free-issues.destroy');
Route::get('/skus', [SkusController::class, 'fetchSkus']);

