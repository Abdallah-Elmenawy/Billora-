<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Journal;
use App\Models\MoneyCategory;
use App\Models\MoneyEntry;
use App\Models\MoneyTransaction;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\TreasuryTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountingService
{
    public function postJournal(string $description, array $lines, $reference = null, ?string $date = null): Journal
    {
        $debit = collect($lines)->sum(fn ($line) => (float) ($line['debit'] ?? 0));
        $credit = collect($lines)->sum(fn ($line) => (float) ($line['credit'] ?? 0));
        if (round($debit, 2) !== round($credit, 2) || $debit <= 0) {
            throw new RuntimeException('القيد غير متوازن.');
        }

        $journal = Journal::query()->create([
            'number' => CompanySetting::current()->nextNumber('journal'),
            'journal_date' => $date ?? now()->toDateString(),
            'description' => $description,
            'status' => 'posted',
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'created_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $journal->lines()->create([
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'notes' => $line['notes'] ?? null,
            ]);
            $account = Account::query()->findOrFail($line['account_id']);
            $delta = ((float) ($line['debit'] ?? 0)) - ((float) ($line['credit'] ?? 0));
            $account->increment('current_balance', in_array($account->type, ['asset', 'expense'], true) ? $delta : -$delta);
        }

        return $journal;
    }

    public function confirmSalesInvoice(SalesInvoice $invoice): void
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException('لا يمكن تأكيد هذه الفاتورة.');
        }
        DB::transaction(function () use ($invoice) {
            $settings = CompanySetting::current();
            $cogs = 0;
            foreach ($invoice->items as $item) {
                $product = $item->product;
                if ($product->type === 'product') {
                    if ($product->current_stock < $item->qty) {
                        throw new RuntimeException("الكمية غير كافية للمنتج {$product->name}.");
                    }
                    $cogs += (float) $product->cost_price * (float) $item->qty;
                    $product->decrement('current_stock', $item->qty);
                    InventoryMovement::query()->create([
                        'product_id' => $product->id, 'type' => 'out', 'qty' => $item->qty,
                        'unit_cost' => $product->cost_price, 'reference_type' => SalesInvoice::class,
                        'reference_id' => $invoice->id, 'moved_at' => $invoice->invoice_date, 'created_by' => auth()->id(),
                    ]);
                }
            }
            $lines = [
                ['account_id' => $settings->customers_account_id, 'debit' => $invoice->total, 'credit' => 0],
                ['account_id' => $settings->sales_account_id, 'debit' => 0, 'credit' => $invoice->subtotal],
            ];
            if ((float) $invoice->tax > 0 && $settings->tax_account_id) {
                $lines[] = ['account_id' => $settings->tax_account_id, 'debit' => 0, 'credit' => $invoice->tax];
            }
            if ((float) $invoice->discount > 0 && $settings->sales_discount_account_id) {
                $lines[] = ['account_id' => $settings->sales_discount_account_id, 'debit' => $invoice->discount, 'credit' => 0];
            }
            if ($cogs > 0 && $settings->cogs_account_id && $settings->inventory_account_id) {
                $lines[] = ['account_id' => $settings->cogs_account_id, 'debit' => $cogs, 'credit' => 0];
                $lines[] = ['account_id' => $settings->inventory_account_id, 'debit' => 0, 'credit' => $cogs];
            }
            $journal = $this->postJournal('فاتورة مبيعات '.$invoice->number, $lines, $invoice, $invoice->invoice_date->toDateString());
            $invoice->update(['status' => (float) $invoice->paid > 0 ? 'partial' : 'confirmed', 'journal_id' => $journal->id]);
            $invoice->customer->increment('current_balance', $invoice->remaining);
            ActivityLog::record('sales', 'confirm', "تأكيد فاتورة مبيعات {$invoice->number}", $invoice);
        });
    }

    public function confirmPurchaseInvoice(PurchaseInvoice $invoice): void
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException('لا يمكن تأكيد هذه الفاتورة.');
        }
        DB::transaction(function () use ($invoice) {
            $settings = CompanySetting::current();
            foreach ($invoice->items as $item) {
                $product = $item->product;
                if ($product->type === 'product') {
                    $product->increment('current_stock', $item->qty);
                    InventoryMovement::query()->create([
                        'product_id' => $product->id, 'type' => 'in', 'qty' => $item->qty,
                        'unit_cost' => $item->unit_cost, 'reference_type' => PurchaseInvoice::class,
                        'reference_id' => $invoice->id, 'moved_at' => $invoice->invoice_date, 'created_by' => auth()->id(),
                    ]);
                }
            }
            $journal = $this->postJournal('فاتورة مشتريات '.$invoice->number, [
                ['account_id' => $settings->inventory_account_id ?: $settings->purchases_account_id, 'debit' => $invoice->total, 'credit' => 0],
                ['account_id' => $settings->suppliers_account_id, 'debit' => 0, 'credit' => $invoice->total],
            ], $invoice, $invoice->invoice_date->toDateString());
            $invoice->update(['status' => (float) $invoice->paid > 0 ? 'partial' : 'confirmed', 'journal_id' => $journal->id]);
            $invoice->supplier->increment('current_balance', $invoice->remaining);
            ActivityLog::record('purchases', 'confirm', "تأكيد فاتورة مشتريات {$invoice->number}", $invoice);
        });
    }

    public function recordReceipt(array $data): MoneyTransaction
    {
        return DB::transaction(function () use ($data) {
            $settings = CompanySetting::current();
            $treasury = Treasury::query()->findOrFail($data['treasury_id']);
            $invoice = ! empty($data['invoice_id']) ? SalesInvoice::query()->findOrFail($data['invoice_id']) : null;
            $transaction = MoneyTransaction::query()->create([
                'number' => $settings->nextNumber('receipt'), 'type' => 'receipt',
                'treasury_id' => $treasury->id,
                'party_type' => $data['party_type'] ?? ($invoice ? Customer::class : null),
                'party_id' => $data['party_id'] ?? $invoice?->customer_id,
                'invoice_type' => $invoice ? SalesInvoice::class : null, 'invoice_id' => $invoice?->id,
                'amount' => $data['amount'], 'transacted_at' => $data['transacted_at'],
                'method' => $data['method'] ?? 'cash', 'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ]);
            $accountId = $treasury->account_id ?: $settings->customers_account_id;
            $journal = $this->postJournal('تحصيل '.$transaction->number, [
                ['account_id' => $accountId, 'debit' => $data['amount'], 'credit' => 0],
                ['account_id' => $settings->customers_account_id, 'debit' => 0, 'credit' => $data['amount']],
            ], $transaction, $data['transacted_at']);
            $transaction->update(['journal_id' => $journal->id]);
            $treasury->increment('current_balance', $data['amount']);
            if ($invoice) {
                $invoice->increment('paid', $data['amount']);
                $invoice->decrement('remaining', $data['amount']);
                $invoice->refresh();
                $invoice->update(['status' => (float) $invoice->remaining <= 0 ? 'paid' : 'partial']);
                $invoice->customer->decrement('current_balance', $data['amount']);
            } elseif (! empty($data['party_id'])) {
                Customer::query()->find($data['party_id'])?->decrement('current_balance', $data['amount']);
            }
            ActivityLog::record('treasury', 'receipt', "تحصيل {$transaction->number}", $transaction);

            return $transaction;
        });
    }

    public function recordPayment(array $data): MoneyTransaction
    {
        return DB::transaction(function () use ($data) {
            $settings = CompanySetting::current();
            $treasury = Treasury::query()->findOrFail($data['treasury_id']);
            if ($treasury->current_balance < $data['amount']) {
                throw new RuntimeException('رصيد الخزينة غير كافٍ.');
            }
            $invoice = ! empty($data['invoice_id']) ? PurchaseInvoice::query()->findOrFail($data['invoice_id']) : null;
            $transaction = MoneyTransaction::query()->create([
                'number' => $settings->nextNumber('payment'), 'type' => 'payment',
                'treasury_id' => $treasury->id,
                'party_type' => $data['party_type'] ?? ($invoice ? Supplier::class : null),
                'party_id' => $data['party_id'] ?? $invoice?->supplier_id,
                'invoice_type' => $invoice ? PurchaseInvoice::class : null, 'invoice_id' => $invoice?->id,
                'amount' => $data['amount'], 'transacted_at' => $data['transacted_at'],
                'method' => $data['method'] ?? 'cash', 'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ]);
            $accountId = $treasury->account_id ?: $settings->suppliers_account_id;
            $journal = $this->postJournal('صرف '.$transaction->number, [
                ['account_id' => $settings->suppliers_account_id, 'debit' => $data['amount'], 'credit' => 0],
                ['account_id' => $accountId, 'debit' => 0, 'credit' => $data['amount']],
            ], $transaction, $data['transacted_at']);
            $transaction->update(['journal_id' => $journal->id]);
            $treasury->decrement('current_balance', $data['amount']);
            if ($invoice) {
                $invoice->increment('paid', $data['amount']);
                $invoice->decrement('remaining', $data['amount']);
                $invoice->refresh();
                $invoice->update(['status' => (float) $invoice->remaining <= 0 ? 'paid' : 'partial']);
                $invoice->supplier->decrement('current_balance', $data['amount']);
            } elseif (! empty($data['party_id'])) {
                Supplier::query()->find($data['party_id'])?->decrement('current_balance', $data['amount']);
            }
            ActivityLog::record('treasury', 'payment', "صرف {$transaction->number}", $transaction);

            return $transaction;
        });
    }

    public function transfer(array $data): TreasuryTransfer
    {
        return DB::transaction(function () use ($data) {
            $from = Treasury::query()->findOrFail($data['from_treasury_id']);
            $to = Treasury::query()->findOrFail($data['to_treasury_id']);
            if ($from->current_balance < $data['amount']) {
                throw new RuntimeException('رصيد الخزينة المحوّل منها غير كافٍ.');
            }
            $transfer = TreasuryTransfer::query()->create([
                'number' => CompanySetting::current()->nextNumber('journal'),
                'from_treasury_id' => $from->id, 'to_treasury_id' => $to->id,
                'amount' => $data['amount'], 'transferred_at' => $data['transferred_at'],
                'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ]);
            if ($from->account_id && $to->account_id) {
                $journal = $this->postJournal('تحويل خزينة', [
                    ['account_id' => $to->account_id, 'debit' => $data['amount'], 'credit' => 0],
                    ['account_id' => $from->account_id, 'debit' => 0, 'credit' => $data['amount']],
                ], $transfer, $data['transferred_at']);
                $transfer->update(['journal_id' => $journal->id]);
            }
            $from->decrement('current_balance', $data['amount']);
            $to->increment('current_balance', $data['amount']);
            ActivityLog::record('treasury', 'transfer', 'تحويل بين الخزائن', $transfer);

            return $transfer;
        });
    }

    public function recordMoneyEntry(array $data): MoneyEntry
    {
        return DB::transaction(function () use ($data) {
            $settings = CompanySetting::current();
            $treasury = Treasury::query()->findOrFail($data['treasury_id']);
            $category = \App\Models\MoneyCategory::query()->findOrFail($data['money_category_id']);
            if ($data['type'] === 'expense' && $treasury->current_balance < $data['amount']) {
                throw new RuntimeException('رصيد الخزينة غير كافٍ.');
            }
            $entry = MoneyEntry::query()->create([
                'number' => $settings->nextNumber($data['type'] === 'expense' ? 'payment' : 'receipt'),
                'type' => $data['type'], 'money_category_id' => $category->id, 'treasury_id' => $treasury->id,
                'amount' => $data['amount'], 'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null, 'created_by' => auth()->id(),
            ]);
            $treasuryAccount = $treasury->account_id;
            $categoryAccount = $category->account_id;
            if ($treasuryAccount && $categoryAccount) {
                $lines = $data['type'] === 'expense'
                    ? [['account_id' => $categoryAccount, 'debit' => $data['amount'], 'credit' => 0], ['account_id' => $treasuryAccount, 'debit' => 0, 'credit' => $data['amount']]]
                    : [['account_id' => $treasuryAccount, 'debit' => $data['amount'], 'credit' => 0], ['account_id' => $categoryAccount, 'debit' => 0, 'credit' => $data['amount']]];
                $journal = $this->postJournal(($data['type'] === 'expense' ? 'مصروف ' : 'إيراد ').$entry->number, $lines, $entry, $data['entry_date']);
                $entry->update(['journal_id' => $journal->id]);
            }
            $treasury->{$data['type'] === 'expense' ? 'decrement' : 'increment'}('current_balance', $data['amount']);
            ActivityLog::record('expenses', $data['type'], "تسجيل {$entry->number}", $entry);

            return $entry;
        });
    }

    public function updateMoneyEntry(MoneyEntry $entry, array $data): MoneyEntry
    {
        return DB::transaction(function () use ($entry, $data) {
            $this->unwindMoneyEntry($entry);

            $treasury = Treasury::query()->findOrFail($data['treasury_id']);
            $category = MoneyCategory::query()->findOrFail($data['money_category_id']);
            if ($data['type'] === 'expense' && $treasury->current_balance < $data['amount']) {
                throw new RuntimeException('رصيد الخزينة غير كافٍ.');
            }

            $entry->update([
                'type' => $data['type'],
                'money_category_id' => $category->id,
                'treasury_id' => $treasury->id,
                'amount' => $data['amount'],
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
            ]);

            $treasuryAccount = $treasury->account_id;
            $categoryAccount = $category->account_id;
            if ($treasuryAccount && $categoryAccount) {
                $lines = $data['type'] === 'expense'
                    ? [['account_id' => $categoryAccount, 'debit' => $data['amount'], 'credit' => 0], ['account_id' => $treasuryAccount, 'debit' => 0, 'credit' => $data['amount']]]
                    : [['account_id' => $treasuryAccount, 'debit' => $data['amount'], 'credit' => 0], ['account_id' => $categoryAccount, 'debit' => 0, 'credit' => $data['amount']]];
                $journal = $this->postJournal(($data['type'] === 'expense' ? 'مصروف ' : 'إيراد ').$entry->number, $lines, $entry, $data['entry_date']);
                $entry->update(['journal_id' => $journal->id]);
            }
            $treasury->{$data['type'] === 'expense' ? 'decrement' : 'increment'}('current_balance', $data['amount']);
            ActivityLog::record('expenses', 'update', "تعديل {$entry->number}", $entry);

            return $entry;
        });
    }

    public function deleteMoneyEntry(MoneyEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $number = $entry->number;
            $this->unwindMoneyEntry($entry);
            $entry->delete();
            ActivityLog::record('expenses', 'delete', "حذف {$number}");
        });
    }

    protected function unwindMoneyEntry(MoneyEntry $entry): void
    {
        $treasury = Treasury::query()->findOrFail($entry->treasury_id);
        if ($entry->type === 'revenue' && $treasury->current_balance < $entry->amount) {
            throw new RuntimeException('لا يمكن التراجع عن العملية لأن رصيد الخزينة غير كافٍ.');
        }
        $treasury->{$entry->type === 'expense' ? 'increment' : 'decrement'}('current_balance', $entry->amount);
        if ($entry->journal_id) {
            $this->reverseJournal(Journal::query()->find($entry->journal_id));
            $entry->update(['journal_id' => null]);
        }
    }

    protected function reverseJournal(?Journal $journal): void
    {
        if (! $journal) {
            return;
        }
        foreach ($journal->lines as $line) {
            $account = Account::query()->findOrFail($line->account_id);
            $delta = ((float) $line->debit) - ((float) $line->credit);
            $account->increment('current_balance', in_array($account->type, ['asset', 'expense'], true) ? -$delta : $delta);
        }
        $journal->lines()->delete();
        $journal->delete();
    }

    public function adjustStock(Product $product, float $qty, string $type, ?string $notes = null): void
    {
        DB::transaction(function () use ($product, $qty, $type, $notes) {
            if ($type === 'out' && $product->current_stock < $qty) {
                throw new RuntimeException('الكمية غير كافية.');
            }
            if ($type === 'in') {
                $product->increment('current_stock', $qty);
            } elseif ($type === 'out') {
                $product->decrement('current_stock', $qty);
            } else {
                $product->update(['current_stock' => $qty]);
            }
            InventoryMovement::query()->create([
                'product_id' => $product->id, 'type' => $type, 'qty' => $qty,
                'unit_cost' => $product->cost_price, 'moved_at' => now()->toDateString(),
                'notes' => $notes, 'created_by' => auth()->id(),
            ]);
            ActivityLog::record('inventory', $type, "حركة مخزون {$product->name}", $product);
        });
    }

    public function confirmSalesReturn(SalesReturn $return): void
    {
        if ($return->status !== 'draft') {
            throw new RuntimeException('لا يمكن تأكيد هذا المرتجع.');
        }
        DB::transaction(function () use ($return) {
            $settings = CompanySetting::current();
            foreach ($return->items as $item) {
                if ($item->product->type === 'product') {
                    $item->product->increment('current_stock', $item->qty);
                    InventoryMovement::query()->create([
                        'product_id' => $item->product_id, 'type' => 'in', 'qty' => $item->qty,
                        'unit_cost' => $item->product->cost_price, 'reference_type' => SalesReturn::class,
                        'reference_id' => $return->id, 'moved_at' => $return->return_date, 'created_by' => auth()->id(),
                    ]);
                }
            }
            $journal = $this->postJournal('مرتجع مبيعات '.$return->number, [
                ['account_id' => $settings->sales_account_id, 'debit' => $return->total, 'credit' => 0],
                ['account_id' => $settings->customers_account_id, 'debit' => 0, 'credit' => $return->total],
            ], $return, $return->return_date->toDateString());
            $return->update(['status' => 'confirmed', 'journal_id' => $journal->id]);
            $return->customer->decrement('current_balance', $return->total);
            ActivityLog::record('sales', 'return', "مرتجع مبيعات {$return->number}", $return);
        });
    }

    public function confirmPurchaseReturn(PurchaseReturn $return): void
    {
        if ($return->status !== 'draft') {
            throw new RuntimeException('لا يمكن تأكيد هذا المرتجع.');
        }
        DB::transaction(function () use ($return) {
            $settings = CompanySetting::current();
            foreach ($return->items as $item) {
                if ($item->product->type === 'product') {
                    if ($item->product->current_stock < $item->qty) {
                        throw new RuntimeException("الكمية غير كافية لمرتجع {$item->product->name}.");
                    }
                    $item->product->decrement('current_stock', $item->qty);
                    InventoryMovement::query()->create([
                        'product_id' => $item->product_id, 'type' => 'out', 'qty' => $item->qty,
                        'unit_cost' => $item->unit_cost, 'reference_type' => PurchaseReturn::class,
                        'reference_id' => $return->id, 'moved_at' => $return->return_date, 'created_by' => auth()->id(),
                    ]);
                }
            }
            $journal = $this->postJournal('مرتجع مشتريات '.$return->number, [
                ['account_id' => $settings->suppliers_account_id, 'debit' => $return->total, 'credit' => 0],
                ['account_id' => $settings->inventory_account_id ?: $settings->purchases_account_id, 'debit' => 0, 'credit' => $return->total],
            ], $return, $return->return_date->toDateString());
            $return->update(['status' => 'confirmed', 'journal_id' => $journal->id]);
            $return->supplier->decrement('current_balance', $return->total);
            ActivityLog::record('purchases', 'return', "مرتجع مشتريات {$return->number}", $return);
        });
    }
}
