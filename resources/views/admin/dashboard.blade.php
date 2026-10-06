@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'لوحة التحكم', 'subtitle' => 'ملخص النشاط المالي'])
@endsection
@section('content')
    <div class="row row-sm">
        @foreach ($kpiCards as $card)
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="dash-stat dash-stat-{{ $card['color'] }}">
                    <div class="dash-stat-title">
                        <span class="dash-stat-icon"><i class="fe {{ $card['icon'] }}"></i></span>
                        <span>{{ $card['title'] }}</span>
                    </div>
                    <div class="dash-stat-mid">
                        <div class="dash-stat-amount">
                            <div class="dash-stat-value">{{ money($card['value']) }}</div>
                            <div class="dash-stat-count">{{ $card['count'] }}</div>
                        </div>
                        <div class="dash-stat-pct">
                            <span>{{ $card['percent'] }}%</span>
                            <i class="fe {{ $card['up'] ? 'fe-arrow-up' : 'fe-arrow-down' }}"></i>
                        </div>
                    </div>
        @php
            $waveLines = [
                'M0 50 C 40 16, 80 72, 120 38 C 160 10, 200 68, 240 36 C 270 16, 298 58, 320 30',
                'M0 34 C 36 66, 72 12, 108 50 C 148 80, 184 18, 224 52 C 258 74, 290 20, 320 46',
                'M0 56 C 30 28, 60 28, 90 56 C 120 84, 150 84, 180 56 C 210 28, 240 28, 270 56 C 295 76, 310 48, 320 40',
                'M0 42 C 25 70, 55 18, 90 46 C 125 74, 155 14, 190 44 C 225 72, 260 16, 295 48 C 308 58, 316 36, 320 28',
                'M0 60 C 50 8, 90 8, 130 50 C 170 88, 210 20, 250 48 C 280 68, 300 22, 320 38',
                'M0 28 C 40 8, 70 72, 110 40 C 145 12, 175 64, 210 36 C 245 10, 275 70, 320 32',
                'M0 46 C 20 20, 45 72, 75 44 C 100 22, 125 68, 155 40 C 185 16, 215 70, 250 42 C 280 20, 300 62, 320 36',
                'M0 38 C 55 78, 85 10, 140 48 C 190 80, 220 14, 270 46 C 295 62, 310 24, 320 34',
            ];
            $waveLine = $waveLines[$loop->index] ?? $waveLines[0];
        @endphp
                    <svg class="dash-stat-wave" viewBox="0 0 320 80" preserveAspectRatio="none" aria-hidden="true">
                        <path d="{{ $waveLine }} V 80 H 0 Z" fill="rgba(255,255,255,0.18)"/>
                        <path d="{{ $waveLine }}" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="2.2"/>
                    </svg>
                </div>
            </div>
        @endforeach
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">حركة 7 أيام</h4></div>
                <div class="card-body"><canvas id="dashChart" height="110"></canvas></div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fe fe-shopping-cart"></i> آخر فواتير المبيعات</h4>
                    <a href="{{ route('sales.index') }}" class="count-badge">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th class="col-serial">#</th><th>الرقم</th><th>العميل</th><th>الإجمالي</th><th>الحالة</th><th class="col-actions">الإجراءات</th></tr></thead>
                        <tbody>
                        @forelse($latestSales as $inv)
                            <tr>
                                <td class="col-serial">{{ row_no($latestSales, $loop) }}</td>
                                <td class="cell-strong"><a href="{{ route('sales.show', $inv) }}">{{ $inv->number }}</a></td>
                                <td>{{ $inv->customer->name ?? '-' }}</td>
                                <td class="cell-money">{{ money($inv->total) }}</td>
                                <td><span class="badge badge-{{ invoice_status_badge($inv->status) }}">{{ invoice_status_label($inv->status) }}</span></td>
                                <td class="col-actions">
                                    <div class="btn-actions">
                                        <x-view-link :href="route('sales.show', $inv)" title="عرض الفاتورة" />
                                        <x-print-link :href="route('sales.print', $inv)" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-file-text"></i>لا توجد فواتير بعد</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fe fe-credit-card"></i> آخر التحصيلات والمدفوعات</h4>
                    <a href="{{ route('treasury.index') }}" class="count-badge">الخزينة</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th class="col-serial">#</th><th>النوع</th><th>الطرف</th><th>المبلغ</th><th>الخزينة</th></tr></thead>
                        <tbody>
                        @forelse($latestPayments as $pay)
                            <tr>
                                <td class="col-serial">{{ row_no($latestPayments, $loop) }}</td>
                                <td><span class="badge badge-{{ $pay->type === 'receipt' ? 'success' : 'danger' }}">{{ $pay->type === 'receipt' ? 'تحصيل' : 'صرف' }}</span></td>
                                <td>{{ $pay->party->name ?? '-' }}</td>
                                <td class="cell-money {{ $pay->type === 'receipt' ? 'is-pos' : 'is-neg' }}">{{ money($pay->amount) }}</td>
                                <td>{{ $pay->treasury->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="cell-empty text-center text-muted"><i class="fe fe-credit-card"></i>لا توجد حركات</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">منتجات منخفضة المخزون</h4></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($lowStock as $p)
                            <li class="mb-2 d-flex justify-content-between"><span>{{ $p->name }}</span><span class="text-danger">{{ $p->current_stock }} / {{ $p->min_stock }}</span></li>
                        @empty
                            <li class="text-muted">لا توجد تنبيهات</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">أكثر المنتجات مبيعاً</h4></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($topProducts as $row)
                            <li class="mb-2 d-flex justify-content-between"><span>{{ $row->product->name ?? '-' }}</span><span>{{ $row->qty_sold }}</span></li>
                        @empty
                            <li class="text-muted">لا توجد بيانات</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">آخر النشاطات</h4></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($activities as $log)
                            <li class="mb-2">
                                <div class="tx-13">{{ $log->description }}</div>
                                <small class="text-muted">{{ $log->user->name ?? '-' }} — {{ $log->created_at->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="text-muted">لا يوجد نشاط</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('dashChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {label: 'مبيعات', data: @json($chartSales), borderColor: '#3B82F6', tension: .3},
                {label: 'مشتريات', data: @json($chartPurchases), borderColor: '#EC4899', tension: .3},
                {label: 'مصروفات', data: @json($chartExpenses), borderColor: '#10B981', tension: .3},
                {label: 'إيرادات', data: @json($chartRevenues), borderColor: '#F97316', tension: .3},
            ]
        },
        options: {responsive: true, plugins: {legend: {position: 'bottom'}}}
    });
}
</script>
@endsection
