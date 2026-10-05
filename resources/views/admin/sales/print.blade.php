<!DOCTYPE html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><title>{{ $invoice->number }}</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
<style>body{font-family:Cairo,sans-serif;padding:24px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px}</style>
</head><body>
<h2>{{ optional(company())->name }} — فاتورة مبيعات {{ $invoice->number }}</h2>
<p>العميل: {{ $invoice->customer->name }} — التاريخ: {{ optional($invoice->invoice_date)->format('Y-m-d') }}</p>
<table><thead><tr><th>#</th><th>المنتج</th><th>كمية</th><th>سعر</th><th>إجمالي</th></tr></thead><tbody>
@foreach($invoice->items as $item)
<tr><td>{{ $loop->iteration }}</td><td>{{ $item->product->name }}</td><td>{{ $item->qty }}</td><td>{{ money($item->unit_price) }}</td><td>{{ money($item->total) }}</td></tr>
@endforeach
</tbody></table>
<p>الإجمالي: {{ money($invoice->total) }} — المدفوع: {{ money($invoice->paid) }} — المتبقي: {{ money($invoice->remaining) }}</p>
<script>window.print()</script>
</body></html>
