<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PurchaseInvoiceController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SalesInvoiceController;
use App\Http\Controllers\Admin\SalesReturnController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TreasuryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::resource('verify', TwoFactorController::class)->only(['index', 'store']);
    Route::post('verify/resend', [TwoFactorController::class, 'resend'])->name('verify.resend');
});

Route::middleware(['auth', 'twofactor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:customers.view')->group(function () {
        Route::get('customers/accounts', [CustomerController::class, 'accounts'])->name('customers.accounts');
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/collect', [CustomerController::class, 'collect'])->name('customers.collect');
    });
    Route::middleware('permission:suppliers.view')->group(function () {
        Route::get('suppliers/accounts', [SupplierController::class, 'accounts'])->name('suppliers.accounts');
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/{supplier}/pay', [SupplierController::class, 'pay'])->name('suppliers.pay');
    });
    Route::middleware('permission:products.view')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('categories', ProductCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    });
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    });
    Route::middleware('permission:sales.view')->group(function () {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::resource('sales', SalesInvoiceController::class);
        Route::post('sales/{sale}/confirm', [SalesInvoiceController::class, 'confirm'])->name('sales.confirm');
        Route::post('sales/{sale}/pay', [SalesInvoiceController::class, 'pay'])->name('sales.pay');
        Route::post('sales/{sale}/cancel', [SalesInvoiceController::class, 'cancel'])->name('sales.cancel');
        Route::get('sales/{sale}/print', [SalesInvoiceController::class, 'print'])->name('sales.print');
        Route::resource('sales-returns', SalesReturnController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::post('sales-returns/{sales_return}/confirm', [SalesReturnController::class, 'confirm'])->name('sales-returns.confirm');
    });
    Route::middleware('permission:purchases.view')->group(function () {
        Route::resource('purchases', PurchaseInvoiceController::class);
        Route::post('purchases/{purchase}/confirm', [PurchaseInvoiceController::class, 'confirm'])->name('purchases.confirm');
        Route::post('purchases/{purchase}/pay', [PurchaseInvoiceController::class, 'pay'])->name('purchases.pay');
        Route::post('purchases/{purchase}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('purchases.cancel');
        Route::get('purchases/{purchase}/print', [PurchaseInvoiceController::class, 'print'])->name('purchases.print');
        Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::post('purchase-returns/{purchase_return}/confirm', [PurchaseReturnController::class, 'confirm'])->name('purchase-returns.confirm');
    });
    Route::middleware('permission:accounting.view')->group(function () {
        Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::put('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::get('accounts/receivables', [AccountController::class, 'receivables'])->name('accounts.receivables');
        Route::get('accounts/payables', [AccountController::class, 'payables'])->name('accounts.payables');
        Route::resource('journals', JournalController::class)->only(['index', 'create', 'store', 'show']);
    });
    Route::middleware('permission:treasury.view')->group(function () {
        Route::get('treasury', [TreasuryController::class, 'index'])->name('treasury.index');
        Route::post('treasury', [TreasuryController::class, 'storeTreasury'])->name('treasury.store');
        Route::post('treasury/receipt', [TreasuryController::class, 'receipt'])->name('treasury.receipt');
        Route::post('treasury/payment', [TreasuryController::class, 'payment'])->name('treasury.payment');
        Route::post('treasury/transfer', [TreasuryController::class, 'transfer'])->name('treasury.transfer');
    });
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::post('expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
        Route::get('expenses/{entry}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{entry}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('expenses/{entry}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
        Route::get('reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit');
        Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('reports/ledger', [ReportController::class, 'accountLedger'])->name('reports.ledger');
    });
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('logs.index');
    });
    Route::middleware('permission:settings.update')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

require __DIR__.'/auth.php';
