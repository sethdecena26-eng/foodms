<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt — {{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 2rem;
        }
        .receipt {
            background: white;
            width: 320px;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
        }
        .receipt-header {
            background: #0f172a;
            background-image: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(249,115,22,.2) 0%, transparent 70%);
            color: white;
            text-align: center;
            padding: 1.5rem 1rem 1.25rem;
        }
        .receipt-logo { font-weight: 800; font-size: 1.1rem; letter-spacing: -.5px; }
        .receipt-logo span { color: #f97316; }
        .receipt-num { font-family: 'DM Mono', monospace; font-size: .75rem; color: #94a3b8; margin-top: .35rem; }
        .receipt-body { padding: 1rem; }
        .receipt-meta { font-size: .7rem; color: #94a3b8; text-align: center; margin-bottom: .75rem; }
        .divider { border: none; border-top: 1px dashed #e2e8f0; margin: .75rem 0; }
        .line-item { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: .4rem; }
        .line-item .name { font-size: .8rem; color: #334155; flex: 1; }
        .line-item .qty  { font-size: .75rem; color: #94a3b8; margin: 0 .5rem; }
        .line-item .amt  { font-size: .8rem; font-weight: 600; color: #1e293b; font-family: 'DM Mono', monospace; }
        .total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: .875rem; }
        .total-amt { color: #f97316; font-family: 'DM Mono', monospace; }
        .change-row { display: flex; justify-content: space-between; font-size: .8rem; color: #22c55e; font-weight: 600; margin-top: .35rem; }
        .profit-row { display: flex; justify-content: space-between; font-size: .7rem; color: #94a3b8; margin-top: .2rem; }
        .receipt-footer { text-align: center; padding: .75rem 1rem 1.25rem; border-top: 1px dashed #e2e8f0; }
        .receipt-footer p { font-size: .7rem; color: #94a3b8; line-height: 1.6; }
        .btn-bar { display: flex; gap: .5rem; padding: 0 1rem 1rem; }
        .btn-print { flex: 1; padding: .55rem; background: #f97316; color: white; border: none; border-radius: .5rem; font-size: .8rem; font-weight: 600; cursor: pointer; }
        .btn-new   { flex: 1; padding: .55rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius: .5rem; font-size: .8rem; font-weight: 600; cursor: pointer; text-decoration: none; display: block; text-align: center; }
        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; width: 100%; border-radius: 0; }
            .btn-bar { display: none; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        <div class="receipt-logo">Food<span>MS</span></div>
        <div class="receipt-num">{{ $order->order_number }}</div>
    </div>

    <div class="receipt-body">
        <p class="receipt-meta">
            {{ $order->completed_at?->format('F j, Y — h:i A') }}<br>
            Cashier: {{ $order->user->name }} · {{ ucfirst($order->payment_method) }}
        </p>

        <hr class="divider">

        @foreach($order->items as $item)
        <div class="line-item">
            <span class="name">{{ $item->menuItem->name }}</span>
            <span class="qty">×{{ $item->quantity }}</span>
            <span class="amt">₱{{ number_format($item->line_total, 2) }}</span>
        </div>
        @endforeach

        <hr class="divider">

        @if($order->discount_amount > 0)
        <div class="line-item" style="color:#22c55e">
            <span class="name" style="color:#22c55e">Discount</span>
            <span class="amt" style="color:#22c55e">−₱{{ number_format($order->discount_amount, 2) }}</span>
        </div>
        @endif

        <div class="total-row">
            <span>TOTAL</span>
            <span class="total-amt">₱{{ number_format($order->total_amount, 2) }}</span>
        </div>

        @if($order->payment_method === 'cash')
        <div class="change-row">
            <span>Tendered</span>
            <span>₱{{ number_format($order->amount_tendered, 2) }}</span>
        </div>
        <div class="change-row">
            <span>Change</span>
            <span>₱{{ number_format($order->change_due, 2) }}</span>
        </div>
        @endif

        <div class="profit-row">
            <span>Net Profit</span>
            <span>₱{{ number_format($order->net_profit, 2) }}</span>
        </div>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your order!<br>Come back soon 🍔</p>
    </div>

    <div class="btn-bar">
        <button class="btn-print" onclick="window.print()">🖨 Print</button>
        <a href="{{ route('pos.index') }}" class="btn-new">New Order</a>
    </div>
</div>
</body>
</html>