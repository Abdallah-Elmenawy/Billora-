<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll">
    <div class="main-sidebar-header active">
        <a class="desktop-logo logo-light active" href="{{ route('dashboard') }}">
            <span class="main-logo tx-20 font-weight-bold text-primary">{{ optional(company())->name ?? 'Billora' }}</span>
        </a>
        <a class="desktop-logo logo-dark active" href="{{ route('dashboard') }}">
            <span class="main-logo tx-20 font-weight-bold text-white">{{ optional(company())->name ?? 'Billora' }}</span>
        </a>
    </div>
    <div class="main-sidemenu">
        <div class="app-sidebar__user clearfix">
            <div class="dropdown user-pro-body">
                <div>
                    <img alt="user-img" class="avatar avatar-xl brround" src="{{ URL::asset('assets/img/faces/6.jpg') }}">
                </div>
                <div class="user-info">
                    <h4 class="font-weight-semibold mt-3 mb-0">{{ Auth::user()->name }}</h4>
                    <span class="mb-0 text-muted">{{ Auth::user()->role->name ?? 'مستخدم' }}</span>
                </div>
            </div>
        </div>
        <ul class="side-menu">
            <li class="side-item side-item-category">الرئيسية</li>
            <li class="slide"><a class="side-menu__item" href="{{ route('dashboard') }}"><i class="side-menu__icon fe fe-home"></i><span class="side-menu__label">لوحة التحكم</span></a></li>
            @if(auth()->user()->hasPermission('sales.view'))
            <li class="slide"><a class="side-menu__item" href="{{ route('pos.index') }}"><i class="side-menu__icon fe fe-monitor"></i><span class="side-menu__label">نقطة البيع</span></a></li>
            @endif
            @if(auth()->user()->hasPermission('customers.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">العملاء</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('customers.index') }}">قائمة العملاء</a></li>
                    <li><a class="slide-item" href="{{ route('customers.create') }}">إضافة عميل</a></li>
                    <li><a class="slide-item" href="{{ route('customers.accounts') }}">حسابات العملاء</a></li>
                </ul>
            </li>
            @endif
            @if(auth()->user()->hasPermission('suppliers.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-truck"></i><span class="side-menu__label">الموردون</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('suppliers.index') }}">قائمة الموردين</a></li>
                    <li><a class="slide-item" href="{{ route('suppliers.create') }}">إضافة مورد</a></li>
                    <li><a class="slide-item" href="{{ route('suppliers.accounts') }}">حسابات الموردين</a></li>
                </ul>
            </li>
            @endif
            @if(auth()->user()->hasPermission('products.view') || auth()->user()->hasPermission('inventory.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-box"></i><span class="side-menu__label">المنتجات والمخزون</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    @if(auth()->user()->hasPermission('products.view'))
                    <li><a class="slide-item" href="{{ route('products.index') }}">المنتجات</a></li>
                    <li><a class="slide-item" href="{{ route('categories.index') }}">التصنيفات</a></li>
                    @endif
                    @if(auth()->user()->hasPermission('inventory.view'))
                    <li><a class="slide-item" href="{{ route('inventory.index') }}">حركات المخزون</a></li>
                    @endif
                </ul>
            </li>
            @endif
            @if(auth()->user()->hasPermission('sales.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-shopping-cart"></i><span class="side-menu__label">المبيعات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('pos.index') }}">نقطة البيع</a></li>
                    <li><a class="slide-item" href="{{ route('sales.index') }}">فواتير المبيعات</a></li>
                    <li><a class="slide-item" href="{{ route('sales.create') }}">إنشاء فاتورة</a></li>
                    <li><a class="slide-item" href="{{ route('sales-returns.index') }}">مرتجعات المبيعات</a></li>
                </ul>
            </li>
            @endif
            @if(auth()->user()->hasPermission('purchases.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-file-text"></i><span class="side-menu__label">المشتريات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('purchases.index') }}">فواتير المشتريات</a></li>
                    <li><a class="slide-item" href="{{ route('purchases.create') }}">إنشاء فاتورة</a></li>
                    <li><a class="slide-item" href="{{ route('purchase-returns.index') }}">مرتجعات المشتريات</a></li>
                </ul>
            </li>
            @endif
            <li class="side-item side-item-category">المالية</li>
            @if(auth()->user()->hasPermission('accounting.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-book-open"></i><span class="side-menu__label">الحسابات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('accounts.index') }}">دليل الحسابات</a></li>
                    <li><a class="slide-item" href="{{ route('journals.index') }}">القيود المحاسبية</a></li>
                    <li><a class="slide-item" href="{{ route('accounts.receivables') }}">الحسابات المدينة</a></li>
                    <li><a class="slide-item" href="{{ route('accounts.payables') }}">الحسابات الدائنة</a></li>
                    <li><a class="slide-item" href="{{ route('reports.ledger') }}">دفتر الأستاذ</a></li>
                </ul>
            </li>
            @endif
            @if(auth()->user()->hasPermission('treasury.view'))
            <li class="slide"><a class="side-menu__item" href="{{ route('treasury.index') }}"><i class="side-menu__icon fe fe-credit-card"></i><span class="side-menu__label">الخزينة</span></a></li>
            @endif
            @if(auth()->user()->hasPermission('expenses.view'))
            <li class="slide"><a class="side-menu__item" href="{{ route('expenses.index') }}"><i class="side-menu__icon fe fe-minus-circle"></i><span class="side-menu__label">المصروفات والإيرادات</span></a></li>
            @endif
            @if(auth()->user()->hasPermission('reports.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-bar-chart-2"></i><span class="side-menu__label">التقارير</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('reports.index') }}">كل التقارير</a></li>
                    <li><a class="slide-item" href="{{ route('reports.sales') }}">تقرير المبيعات</a></li>
                    <li><a class="slide-item" href="{{ route('reports.purchases') }}">تقرير المشتريات</a></li>
                    <li><a class="slide-item" href="{{ route('reports.inventory') }}">تقرير المخزون</a></li>
                    <li><a class="slide-item" href="{{ route('reports.profit') }}">الأرباح والخسائر</a></li>
                    <li><a class="slide-item" href="{{ route('reports.financial') }}">التقارير المالية</a></li>
                </ul>
            </li>
            @endif
            <li class="side-item side-item-category">الإدارة</li>
            @if(auth()->user()->hasPermission('users.view'))
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="#"><i class="side-menu__icon fe fe-user-check"></i><span class="side-menu__label">المستخدمون والصلاحيات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('users.index') }}">المستخدمون</a></li>
                    <li><a class="slide-item" href="{{ route('roles.index') }}">الأدوار والصلاحيات</a></li>
                    <li><a class="slide-item" href="{{ route('logs.index') }}">سجل العمليات</a></li>
                </ul>
            </li>
            @endif
            @if(auth()->user()->hasPermission('settings.update'))
            <li class="slide"><a class="side-menu__item" href="{{ route('settings.index') }}"><i class="side-menu__icon fe fe-settings"></i><span class="side-menu__label">الإعدادات</span></a></li>
            @endif
            <li class="slide"><a class="side-menu__item" href="{{ route('profile.edit') }}"><i class="side-menu__icon fe fe-user"></i><span class="side-menu__label">الملف الشخصي</span></a></li>
        </ul>
    </div>
</aside>
