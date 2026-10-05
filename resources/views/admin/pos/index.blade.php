@extends('layouts.master')

@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'نقطة البيع',
        'subtitle' => 'بحث سريع، تصنيفات، صالة، تيك أواي، دليفري، وفاتورة فورية.',
        'action' => '<span class="pos-cashier">الكاشير <strong>'.e(auth()->user()->name).'</strong></span>',
    ])
@endsection

@section('content')
<div class="pos-shell">
    <div class="pos-layout">
        <div class="pos-catalog">
            <div class="pos-search">
                <input type="search" id="posSearch" class="form-control" placeholder="ابحث عن مشروب أو منتج...">
            </div>
            <div class="pos-cats" id="posCats">
                <button type="button" class="pos-chip is-active" data-cat="">كل التصنيفات</button>
                @foreach($categories as $category)
                    <button type="button" class="pos-chip" data-cat="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>
            <div class="pos-grid" id="posGrid"></div>
            <div class="pos-empty" id="posEmpty" hidden>لا توجد منتجات مطابقة.</div>
        </div>

        <aside class="pos-cart card">
            <div class="pos-cart-head">
                <h5>الطلب</h5>
                <div class="pos-types" id="posTypes">
                    <button type="button" class="pos-type is-active" data-type="dine_in">صالة</button>
                    <button type="button" class="pos-type" data-type="takeaway">تيك أواي</button>
                    <button type="button" class="pos-type" data-type="delivery">دليفري</button>
                </div>
            </div>

            <div class="pos-fields">
                <select name="customer_id" id="posCustomer" class="form-control" form="posCheckoutForm" required>
                    <option value="">اختر العميل</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $customers->first()?->id) == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
                @if($treasuries->count() > 1)
                    <select name="treasury_id" id="posTreasury" class="form-control" form="posCheckoutForm">
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="pos-lines" id="posLines">
                <p class="pos-lines-empty">أضف منتجات من القائمة</p>
            </div>

            <div class="pos-summary">
                <div class="pos-sum-row"><span>الإجمالي الفرعي</span><strong id="posSubtotal">0.00 ج.م</strong></div>
                <div class="pos-sum-row pos-discount-row">
                    <span>خصم</span>
                    <input type="number" min="0" step="0.01" id="posDiscount" class="form-control" value="0">
                </div>
                <div class="pos-sum-row pos-total-row"><span>الإجمالى</span><strong id="posTotal">0.00 ج.م</strong></div>

                <div class="pos-pay-status">
                    <span>حالة الفاتورة</span>
                    <div class="pos-types" id="posPayStatus">
                        <button type="button" class="pos-type is-active" data-pay="paid">مدفوع</button>
                        <button type="button" class="pos-type" data-pay="unpaid">غير مدفوع</button>
                    </div>
                </div>
                <div class="pos-sum-row pos-discount-row" id="posPaidWrap">
                    <span>المبلغ من العميل</span>
                    <input type="number" min="0" step="0.01" id="posPaidAmount" class="form-control" value="0">
                </div>
                <div class="pos-sum-row" id="posRemainingRow"><span>المتبقي على العميل</span><strong id="posRemaining">0.00 ج.م</strong></div>
                <div class="pos-sum-row" id="posChangeRow" hidden><span>الباقي للعميل</span><strong id="posChange">0.00 ج.م</strong></div>
            </div>

            <form id="posCheckoutForm" method="POST" action="{{ route('pos.checkout') }}">
                @csrf
                <input type="hidden" name="order_type" id="posOrderType" value="dine_in">
                @if($treasuries->count() === 1)
                    <input type="hidden" name="treasury_id" value="{{ $treasuries->first()->id }}">
                @elseif($treasuries->isEmpty())
                    <p class="text-danger tx-12 mb-2">أضف خزينة نشطة قبل التحصيل.</p>
                @endif
                <div id="posItemInputs"></div>
                <input type="hidden" name="discount" id="posDiscountHidden" value="0">
                <input type="hidden" name="payment_status" id="posPaymentStatus" value="paid">
                <input type="hidden" name="paid_amount" id="posPaidHidden" value="0">
                <button type="submit" class="btn btn-primary pos-pay" id="posPayBtn"
                    @disabled($customers->isEmpty())
                    @if($customers->isEmpty()) data-locked="1" @endif
                    data-no-treasury="{{ $treasuries->isEmpty() ? '1' : '0' }}">
                    تأكيد وتحصيل
                </button>
            </form>
        </aside>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const products = @json($posProducts);
    const symbol = @json(company()?->currency_symbol ?? 'ج.م');
    const cart = new Map();
    let categoryId = '';
    let query = '';

    const grid = document.getElementById('posGrid');
    const empty = document.getElementById('posEmpty');
    const lines = document.getElementById('posLines');
    const subtotalEl = document.getElementById('posSubtotal');
    const totalEl = document.getElementById('posTotal');
    const discountEl = document.getElementById('posDiscount');
    const paidEl = document.getElementById('posPaidAmount');
    const remainingEl = document.getElementById('posRemaining');
    const changeEl = document.getElementById('posChange');
    const changeRow = document.getElementById('posChangeRow');
    const paidWrap = document.getElementById('posPaidWrap');
    const treasuryWrap = document.getElementById('posTreasury');
    const form = document.getElementById('posCheckoutForm');
    const itemInputs = document.getElementById('posItemInputs');
    const payBtn = document.getElementById('posPayBtn');
    let paymentStatus = 'paid';
    let lastTotal = 0;

    function money(n) {
        return Number(n).toFixed(2) + ' ' + symbol;
    }

    function filtered() {
        const q = query.trim();
        return products.filter(function (p) {
            const catOk = !categoryId || String(p.category_id) === String(categoryId);
            const qOk = !q || p.name.indexOf(q) !== -1 || (p.sku && String(p.sku).indexOf(q) !== -1);
            return catOk && qOk;
        });
    }

    function hue(id) {
        return (Number(id) * 47) % 360;
    }

    function renderGrid() {
        const list = filtered();
        grid.innerHTML = '';
        empty.hidden = list.length > 0;
        list.forEach(function (p) {
            const inCart = cart.has(p.id);
            const out = p.type === 'product' && p.stock <= 0;
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'pos-product' + (inCart ? ' is-in-cart' : '') + (out ? ' is-out' : '');
            card.disabled = out;
            card.innerHTML =
                '<span class="pos-product-photo" style="background:linear-gradient(145deg,hsl(' + hue(p.id) + ',55%,46%),hsl(' + ((hue(p.id)+40)%360) + ',50%,32%))">' +
                    '<span>' + (p.letter || '') + '</span>' +
                '</span>' +
                '<span class="pos-product-meta">' +
                    '<span class="pos-product-name">' + p.name + '</span>' +
                    '<span class="pos-product-price">' + money(p.price) + '</span>' +
                '</span>';
            card.addEventListener('click', function () { addItem(p); });
            grid.appendChild(card);
        });
    }

    function addItem(p) {
        const current = cart.get(p.id);
        const qty = current ? current.qty + 1 : 1;
        if (p.type === 'product' && qty > p.stock) {
            return;
        }
        cart.set(p.id, { product: p, qty: qty });
        renderAll();
    }

    function changeQty(id, delta) {
        const line = cart.get(id);
        if (!line) return;
        const next = line.qty + delta;
        if (next <= 0) {
            cart.delete(id);
        } else if (line.product.type === 'product' && next > line.product.stock) {
            return;
        } else {
            line.qty = next;
        }
        renderAll();
    }

    function totals() {
        let sub = 0;
        let tax = 0;
        cart.forEach(function (line) {
            sub += line.qty * line.product.price;
            tax += line.qty * line.product.price * ((Number(line.product.tax_rate) || 0) / 100);
        });
        const discount = Math.max(0, Number(discountEl.value || 0));
        return { sub: sub, tax: tax, discount: discount, total: Math.max(sub + tax - discount, 0) };
    }

    function renderLines() {
        if (cart.size === 0) {
            lines.innerHTML = '<p class="pos-lines-empty">أضف منتجات من القائمة</p>';
            return;
        }
        lines.innerHTML = '';
        cart.forEach(function (line) {
            const row = document.createElement('div');
            row.className = 'pos-line';
            row.innerHTML =
                '<div class="pos-line-info">' +
                    '<strong>' + line.product.name + '</strong>' +
                    '<div class="pos-qty">' +
                        '<button type="button" class="pos-qty-btn" data-act="minus">−</button>' +
                        '<span>' + line.qty + '</span>' +
                        '<button type="button" class="pos-qty-btn" data-act="plus">+</button>' +
                    '</div>' +
                '</div>' +
                '<strong class="pos-line-price">' + money(line.qty * line.product.price) + '</strong>';
            row.querySelector('[data-act="minus"]').addEventListener('click', function () { changeQty(line.product.id, -1); });
            row.querySelector('[data-act="plus"]').addEventListener('click', function () { changeQty(line.product.id, 1); });
            lines.appendChild(row);
        });
    }

    function renderTotals() {
        const t = totals();
        subtotalEl.textContent = money(t.sub);
        totalEl.textContent = money(t.total);
        document.getElementById('posDiscountHidden').value = t.discount;
        document.getElementById('posPaymentStatus').value = paymentStatus;

        if (paymentStatus === 'unpaid') {
            paidEl.value = '0';
            paidEl.disabled = true;
            paidWrap.hidden = true;
        } else {
            paidEl.disabled = false;
            paidWrap.hidden = false;
            if (!paidEl.value || Number(paidEl.value) === lastTotal) {
                paidEl.value = t.total.toFixed(2);
            }
        }
        lastTotal = t.total;

        const given = paymentStatus === 'unpaid' ? 0 : Math.max(0, Number(paidEl.value || 0));
        const collected = Math.min(given, t.total);
        remainingEl.textContent = money(Math.max(t.total - collected, 0));
        const change = Math.max(given - t.total, 0);
        changeEl.textContent = money(change);
        changeRow.hidden = change <= 0;
        document.getElementById('posPaidHidden').value = collected;

        const needsTreasury = paymentStatus === 'paid' && collected > 0;
        if (treasuryWrap) {
            treasuryWrap.hidden = !needsTreasury;
            treasuryWrap.required = needsTreasury;
        }

        const noTreasury = payBtn.getAttribute('data-no-treasury') === '1';
        payBtn.disabled = payBtn.getAttribute('data-locked') === '1' || cart.size === 0 || (needsTreasury && noTreasury);
        payBtn.textContent = paymentStatus === 'unpaid' || collected <= 0 ? 'تأكيد الطلب' : 'تأكيد وتحصيل';
    }

    function renderAll() {
        renderGrid();
        renderLines();
        renderTotals();
    }

    document.getElementById('posSearch').addEventListener('input', function (e) {
        query = e.target.value;
        renderGrid();
    });

    document.getElementById('posCats').addEventListener('click', function (e) {
        const btn = e.target.closest('.pos-chip');
        if (!btn) return;
        document.querySelectorAll('.pos-chip').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        categoryId = btn.getAttribute('data-cat') || '';
        renderGrid();
    });

    document.getElementById('posTypes').addEventListener('click', function (e) {
        const btn = e.target.closest('.pos-type');
        if (!btn) return;
        document.querySelectorAll('#posTypes .pos-type').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        document.getElementById('posOrderType').value = btn.getAttribute('data-type');
    });

    document.getElementById('posPayStatus').addEventListener('click', function (e) {
        const btn = e.target.closest('.pos-type');
        if (!btn) return;
        document.querySelectorAll('#posPayStatus .pos-type').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        paymentStatus = btn.getAttribute('data-pay');
        if (paymentStatus === 'paid') {
            paidEl.value = totals().total.toFixed(2);
        }
        renderTotals();
    });

    discountEl.addEventListener('input', renderTotals);
    paidEl.addEventListener('input', renderTotals);

    form.addEventListener('submit', function (e) {
        if (cart.size === 0) {
            e.preventDefault();
            return;
        }
        itemInputs.innerHTML = '';
        let i = 0;
        cart.forEach(function (line) {
            const pid = document.createElement('input');
            pid.type = 'hidden';
            pid.name = 'items[' + i + '][product_id]';
            pid.value = line.product.id;
            const qty = document.createElement('input');
            qty.type = 'hidden';
            qty.name = 'items[' + i + '][qty]';
            qty.value = line.qty;
            itemInputs.appendChild(pid);
            itemInputs.appendChild(qty);
            i += 1;
        });
        payBtn.disabled = true;
        payBtn.textContent = 'جاري التأكيد...';
    });

    renderAll();
})();
</script>
@endsection
