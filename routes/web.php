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
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    Route::middleware('permission:customers.view')->group(function () {
        Route::get('customers/accounts', [CustomerController::class, 'accounts'])->name('customers.accounts');
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::middleware('permission:customers.create')->group(function () {
            Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        });
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::middleware('permission:customers.update')->group(function () {
            Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::match(['put', 'patch'], 'customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::post('customers/{customer}/collect', [CustomerController::class, 'collect'])->name('customers.collect');
        });
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');
    });
    Route::middleware('permission:suppliers.view')->group(function () {
        Route::get('suppliers/accounts', [SupplierController::class, 'accounts'])->name('suppliers.accounts');
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::middleware('permission:suppliers.create')->group(function () {
            Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        });
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::middleware('permission:suppliers.update')->group(function () {
            Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::match(['put', 'patch'], 'suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
            Route::post('suppliers/{supplier}/pay', [SupplierController::class, 'pay'])->name('suppliers.pay');
        });
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete')->name('suppliers.destroy');
    });
    Route::middleware('permission:products.view')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('categories', [ProductCategoryController::class, 'index'])->name('categories.index');
        Route::middleware('permission:products.create')->group(function () {
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
            Route::post('categories', [ProductCategoryController::class, 'store'])->name('categories.store');
        });
        Route::middleware('permission:products.update')->group(function () {
            Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::match(['put', 'patch'], 'categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');
        });
        Route::middleware('permission:products.delete')->group(function () {
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::delete('categories/{category}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');
        });
    });
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory', [InventoryController::class, 'store'])->middleware('permission:inventory.create')->name('inventory.store');
    });
    Route::middleware('permission:sales.view')->group(function () {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->middleware('permission:sales.create')->name('pos.checkout');
        Route::get('sales', [SalesInvoiceController::class, 'index'])->name('sales.index');
        Route::get('sales-returns', [SalesReturnController::class, 'index'])->name('sales-returns.index');
        Route::middleware('permission:sales.create')->group(function () {
            Route::get('sales/create', [SalesInvoiceController::class, 'create'])->name('sales.create');
            Route::post('sales', [SalesInvoiceController::class, 'store'])->name('sales.store');
            Route::get('sales-returns/create', [SalesReturnController::class, 'create'])->name('sales-returns.create');
            Route::post('sales-returns', [SalesReturnController::class, 'store'])->name('sales-returns.store');
        });
        Route::get('sales/{sale}', [SalesInvoiceController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/print', [SalesInvoiceController::class, 'print'])->name('sales.print');
        Route::get('sales-returns/{sales_return}', [SalesReturnController::class, 'show'])->name('sales-returns.show');
        Route::middleware('permission:sales.update')->group(function () {
            Route::get('sales/{sale}/edit', [SalesInvoiceController::class, 'edit'])->name('sales.edit');
            Route::match(['put', 'patch'], 'sales/{sale}', [SalesInvoiceController::class, 'update'])->name('sales.update');
            Route::post('sales/{sale}/confirm', [SalesInvoiceController::class, 'confirm'])->name('sales.confirm');
            Route::post('sales/{sale}/pay', [SalesInvoiceController::class, 'pay'])->name('sales.pay');
            Route::post('sales/{sale}/cancel', [SalesInvoiceController::class, 'cancel'])->name('sales.cancel');
            Route::post('sales-returns/{sales_return}/confirm', [SalesReturnController::class, 'confirm'])->name('sales-returns.confirm');
        });
        Route::middleware('permission:sales.delete')->group(function () {
            Route::delete('sales/{sale}', [SalesInvoiceController::class, 'destroy'])->name('sales.destroy');
            Route::delete('sales-returns/{sales_return}', [SalesReturnController::class, 'destroy'])->name('sales-returns.destroy');
        });
    });
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('purchases', [PurchaseInvoiceController::class, 'index'])->name('purchases.index');
        Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::middleware('permission:purchases.create')->group(function () {
            Route::get('purchases/create', [PurchaseInvoiceController::class, 'create'])->name('purchases.create');
            Route::post('purchases', [PurchaseInvoiceController::class, 'store'])->name('purchases.store');
            Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
            Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
        });
        Route::get('purchases/{purchase}', [PurchaseInvoiceController::class, 'show'])->name('purchases.show');
        Route::get('purchases/{purchase}/print', [PurchaseInvoiceController::class, 'print'])->name('purchases.print');
        Route::get('purchase-returns/{purchase_return}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
        Route::middleware('permission:purchases.update')->group(function () {
            Route::get('purchases/{purchase}/edit', [PurchaseInvoiceController::class, 'edit'])->name('purchases.edit');
            Route::match(['put', 'patch'], 'purchases/{purchase}', [PurchaseInvoiceController::class, 'update'])->name('purchases.update');
            Route::post('purchases/{purchase}/confirm', [PurchaseInvoiceController::class, 'confirm'])->name('purchases.confirm');
            Route::post('purchases/{purchase}/pay', [PurchaseInvoiceController::class, 'pay'])->name('purchases.pay');
            Route::post('purchases/{purchase}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('purchases.cancel');
            Route::post('purchase-returns/{purchase_return}/confirm', [PurchaseReturnController::class, 'confirm'])->name('purchase-returns.confirm');
        });
        Route::middleware('permission:purchases.delete')->group(function () {
            Route::delete('purchases/{purchase}', [PurchaseInvoiceController::class, 'destroy'])->name('purchases.destroy');
            Route::delete('purchase-returns/{purchase_return}', [PurchaseReturnController::class, 'destroy'])->name('purchase-returns.destroy');
        });
    });
    Route::middleware('permission:accounting.view')->group(function () {
        Route::get('accounts/receivables', [AccountController::class, 'receivables'])->name('accounts.receivables');
        Route::get('accounts/payables', [AccountController::class, 'payables'])->name('accounts.payables');
        Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('journals', [JournalController::class, 'index'])->name('journals.index');
        Route::middleware('permission:accounting.create')->group(function () {
            Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
            Route::get('journals/create', [JournalController::class, 'create'])->name('journals.create');
            Route::post('journals', [JournalController::class, 'store'])->name('journals.store');
        });
        Route::get('journals/{journal}', [JournalController::class, 'show'])->name('journals.show');
        Route::put('accounts/{account}', [AccountController::class, 'update'])->middleware('permission:accounting.update')->name('accounts.update');
    });
    Route::middleware('permission:treasury.view')->group(function () {
        Route::get('treasury', [TreasuryController::class, 'index'])->name('treasury.index');
        Route::middleware('permission:treasury.create')->group(function () {
            Route::post('treasury', [TreasuryController::class, 'storeTreasury'])->name('treasury.store');
            Route::post('treasury/receipt', [TreasuryController::class, 'receipt'])->name('treasury.receipt');
            Route::post('treasury/payment', [TreasuryController::class, 'payment'])->name('treasury.payment');
            Route::post('treasury/transfer', [TreasuryController::class, 'transfer'])->name('treasury.transfer');
        });
    });
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::middleware('permission:expenses.create')->group(function () {
            Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::post('expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
        });
        Route::middleware('permission:expenses.update')->group(function () {
            Route::get('expenses/{entry}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
            Route::match(['put', 'patch'], 'expenses/{entry}', [ExpenseController::class, 'update'])->name('expenses.update');
        });
        Route::delete('expenses/{entry}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete')->name('expenses.destroy');
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
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::middleware('permission:users.create')->group(function () {
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        });
        Route::middleware('permission:users.update')->group(function () {
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:users.delete')->name('roles.destroy');
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
