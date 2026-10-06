@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'سجل العمليات'])
@endsection
@section('content')
@php
    $moduleLabels = [
        'customers' => 'العملاء',
        'suppliers' => 'الموردون',
        'products' => 'المنتجات',
        'sales' => 'المبيعات',
        'purchases' => 'المشتريات',
        'treasury' => 'الخزينة',
        'expenses' => 'المصروفات',
        'inventory' => 'المخزون',
        'users' => 'المستخدمون',
        'accounting' => 'الحسابات',
    ];
    $actionLabels = [
        'create' => 'إضافة',
        'update' => 'تعديل',
        'delete' => 'حذف',
        'confirm' => 'تأكيد',
        'receipt' => 'تحصيل',
        'payment' => 'صرف',
        'transfer' => 'تحويل',
        'return' => 'مرتجع',
        'in' => 'إضافة مخزون',
        'out' => 'صرف مخزون',
        'adjust' => 'تسوية',
        'expense' => 'مصروف',
        'revenue' => 'إيراد',
    ];
    $actionBadges = [
        'create' => 'success',
        'update' => 'info',
        'delete' => 'danger',
        'confirm' => 'primary',
        'receipt' => 'success',
        'payment' => 'warning',
        'transfer' => 'secondary',
        'return' => 'warning',
        'in' => 'success',
        'out' => 'danger',
        'adjust' => 'info',
        'expense' => 'danger',
        'revenue' => 'success',
    ];
@endphp

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>الوصف</th>
                        <th>الوحدة</th>
                        <th>الإجراء</th>
                        <th>المستخدم</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="col-serial">{{ row_no($logs, $loop) }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $moduleLabels[$log->module] ?? $log->module }}</td>
                        <td>
                            <span class="badge badge-{{ $actionBadges[$log->action] ?? 'light' }}">
                                {{ $actionLabels[$log->action] ?? $log->action }}
                            </span>
                        </td>
                        <td>{{ $log->user->name ?? 'غير محدد' }}</td>
                        <td>{{ optional($log->created_at)->format('H:i Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">لا توجد عمليات مسجلة</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
