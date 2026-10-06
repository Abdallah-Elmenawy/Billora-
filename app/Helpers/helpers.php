<?php

use App\Models\CompanySetting;

if (! function_exists('company')) {
    function company(): ?CompanySetting
    {
        try {
            return CompanySetting::current();
        } catch (Throwable) {
            return null;
        }
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        $symbol = company()?->currency_symbol ?? 'ج.م';

        return number_format((float) $amount, 2).' '.$symbol;
    }
}

if (! function_exists('invoice_status_label')) {
    function invoice_status_label(?string $status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'confirmed' => 'غير مدفوعة',
            'partial' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة',
            'cancelled' => 'ملغاة',
            default => $status ?? '-',
        };
    }
}

if (! function_exists('invoice_status_badge')) {
    function invoice_status_badge(?string $status): string
    {
        return match ($status) {
            'draft' => 'secondary',
            'confirmed' => 'info',
            'partial' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'light',
        };
    }
}

if (! function_exists('can')) {
    function can(string $permission): bool
    {
        $user = auth()->user();

        return $user ? $user->hasPermission($permission) : false;
    }
}

if (! function_exists('row_no')) {
    function row_no(mixed $items, $loop): int
    {
        if (is_object($items) && method_exists($items, 'firstItem') && $items->firstItem()) {
            return (int) $items->firstItem() + $loop->index;
        }

        return (int) $loop->iteration;
    }
}
