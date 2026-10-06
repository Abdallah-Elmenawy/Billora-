@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'التقارير', 'subtitle' => 'اختر التقرير الذي تريد عرضه'])
@endsection
@section('content')
@php
    $groups = [
        'العمليات' => [
            ['title' => 'تقرير المبيعات', 'desc' => 'فواتير المبيعات المؤكدة خلال فترة محددة', 'icon' => 'fe-shopping-cart', 'color' => 'blue', 'url' => route('reports.sales')],
            ['title' => 'تقرير المشتريات', 'desc' => 'فواتير المشتريات والمدفوع والمتبقي للموردين', 'icon' => 'fe-truck', 'color' => 'purple', 'url' => route('reports.purchases')],
            ['title' => 'تقرير المخزون', 'desc' => 'الكميات الحالية وقيمة المخزون وتنبيهات النقص', 'icon' => 'fe-package', 'color' => 'teal', 'url' => route('reports.inventory')],
            ['title' => 'المصروفات والإيرادات', 'desc' => 'سندات المصروفات والإيرادات حسب التصنيف والخزينة', 'icon' => 'fe-trending-down', 'color' => 'orange', 'url' => route('reports.expenses')],
        ],
        'الأطراف' => [
            ['title' => 'تقرير العملاء', 'desc' => 'أرصدة العملاء والمستحقات المتبقية عليهم', 'icon' => 'fe-users', 'color' => 'green', 'url' => route('reports.customers')],
            ['title' => 'تقرير الموردين', 'desc' => 'أرصدة الموردين والمبالغ المستحقة لهم', 'icon' => 'fe-briefcase', 'color' => 'pink', 'url' => route('reports.suppliers')],
        ],
        'المالية' => [
            ['title' => 'الأرباح والخسائر', 'desc' => 'المبيعات والإيرادات مقابل المشتريات والمصروفات', 'icon' => 'fe-bar-chart-2', 'color' => 'blue', 'url' => route('reports.profit')],
            ['title' => 'التقارير المالية', 'desc' => 'أرصدة الحسابات مجمعة حسب النوع', 'icon' => 'fe-pie-chart', 'color' => 'purple', 'url' => route('reports.financial')],
            ['title' => 'دفتر الأستاذ', 'desc' => 'حركة القيود التفصيلية لأي حساب في الدليل', 'icon' => 'fe-book-open', 'color' => 'teal', 'url' => route('reports.ledger')],
        ],
    ];
@endphp

@foreach($groups as $group => $items)
    <h5 class="report-group-title">{{ $group }}</h5>
    <div class="row">
        @foreach($items as $r)
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a class="card report-card report-{{ $r['color'] }}" href="{{ $r['url'] }}">
                    <div class="card-body">
                        <div class="report-icon"><i class="fe {{ $r['icon'] }}"></i></div>
                        <h5 class="report-title">{{ $r['title'] }}</h5>
                        <p class="report-desc">{{ $r['desc'] }}</p>
                        <span class="report-link">عرض التقرير <i class="fe fe-arrow-left"></i></span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endforeach
@endsection
