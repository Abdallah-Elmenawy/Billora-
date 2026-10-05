<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('type', 20);
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('legal_name', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->string('logo')->nullable();
            $table->string('currency', 10)->default('EGP');
            $table->string('currency_symbol', 10)->default('ج.م');
            $table->string('language', 5)->default('ar');
            $table->string('sale_prefix', 10)->default('SAL');
            $table->string('purchase_prefix', 10)->default('PUR');
            $table->string('payment_prefix', 10)->default('PAY');
            $table->string('receipt_prefix', 10)->default('REC');
            $table->string('journal_prefix', 10)->default('JRN');
            $table->unsignedInteger('sale_next_number')->default(1);
            $table->unsignedInteger('purchase_next_number')->default(1);
            $table->unsignedInteger('payment_next_number')->default(1);
            $table->unsignedInteger('receipt_next_number')->default(1);
            $table->unsignedInteger('journal_next_number')->default(1);
            $table->decimal('default_tax_rate', 8, 2)->default(0);
            $table->boolean('tax_enabled')->default(false);
            $table->boolean('discount_enabled')->default(false);
            $table->foreignId('sales_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('purchases_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('inventory_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('customers_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('suppliers_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('tax_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('sales_discount_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('type', 20)->default('product');
            $table->string('unit', 30)->nullable();
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('min_stock', 15, 3)->default(0);
            $table->decimal('current_stock', 15, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable()->unique();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable()->unique();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('treasuries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 20);
            $table->string('account_number', 100)->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('money_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 20);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->date('journal_date');
            $table->string('description')->nullable();
            $table->string('status', 20)->default('posted');
            $table->nullableMorphs('reference');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->decimal('remaining', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->decimal('remaining', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->nullable()->unique();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->date('return_date');
            $table->string('status', 20)->default('draft');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->nullable()->unique();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->date('return_date');
            $table->string('status', 20)->default('draft');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->nullableMorphs('reference');
            $table->date('moved_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('money_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->string('type', 20);
            $table->foreignId('treasury_id')->constrained('treasuries')->restrictOnDelete();
            $table->nullableMorphs('party');
            $table->nullableMorphs('invoice');
            $table->decimal('amount', 15, 2);
            $table->date('transacted_at');
            $table->string('method', 20)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('money_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->string('type', 20);
            $table->foreignId('money_category_id')->constrained('money_categories')->restrictOnDelete();
            $table->foreignId('treasury_id')->constrained('treasuries')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('treasury_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->nullable()->unique();
            $table->foreignId('from_treasury_id')->constrained('treasuries')->restrictOnDelete();
            $table->foreignId('to_treasury_id')->constrained('treasuries')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('transferred_at');
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 50)->nullable();
            $table->string('action', 50)->nullable();
            $table->text('description')->nullable();
            $table->nullableMorphs('auditable');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('treasury_transfers');
        Schema::dropIfExists('money_entries');
        Schema::dropIfExists('money_transactions');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('money_categories');
        Schema::dropIfExists('treasuries');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('accounts');
    }
};
