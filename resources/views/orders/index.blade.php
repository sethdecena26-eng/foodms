@extends('layouts.app')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', 'View and manage all transactions')

@section('content')

{{-- Stats bar --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Today's Orders</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $todayCount }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Today's Revenue</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">₱{{ number_format($todayRevenue, 0) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Today's Profit</p>
        <p class="text-2xl font-display font-bold text-emerald-600 mt-1">₱{{ number_format($todayProfit, 0) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Cancelled</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $cancelledCount }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
        <form method="GET" class="flex flex-wrap gap-2 items-center">
            {{-- Status filter --}}
            <select name="status"
                    class="text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                <option value="">All Statuses</option>
                <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Pending</option>
                <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
            </select>

            {{-- Date range --}}
            <input type="date" name="from" value="{{ request('from') }}"
                   class="text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
            <span class="text-slate-400 text-sm">to</span>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">

            <button type="submit" class="btn-primary py-2">Filter</button>
            <a href="{{ route('orders.index') }}" class="btn-ghost py-2">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Order #</th>
                    <th class="text-left">Cashier</th>
                    <th class="text-left">Items</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Cost</th>
                    <th class="text-right">Profit</th>
                    <th class="text-center">Payment</th>
                    <th class="text-center">Status</th>
                    <th class="text-left">Date & Time</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $statusClasses = [
                        'completed' => 'badge-green',
                        'pending'   => 'badge-amber',
                        'cancelled' => 'badge-slate',
                    ];
                    $paymentIcons = [
                        'cash'  => 'Cash',
                        'card'  => 'Card',
                        'gcash' => 'Gcash',
                        'other' => 'Other',
                    ];
                @endphp
                <tr>
                    <td>
                        <span class="font-mono text-xs font-semibold text-slate-700">{{ $order->order_number }}</span>
                    </td>
                    <td class="text-sm text-slate-600">{{ $order->user->name }}</td>
                    <td>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">
                            {{ $order->items_count }} item{{ $order->items_count !== 1 ? 's' : '' }}
                        </span>
                    </td>
                    <td class="text-right font-semibold text-slate-800">₱{{ number_format($order->total_amount, 2) }}</td>
                    <td class="text-right text-sm text-slate-400">₱{{ number_format($order->total_cost, 2) }}</td>
                    <td class="text-right font-semibold {{ $order->net_profit >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        ₱{{ number_format($order->net_profit, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="text-sm" title="{{ ucfirst($order->payment_method) }}">
                            {{ $paymentIcons[$order->payment_method] ?? '💱' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $statusClasses[$order->status] ?? 'badge-slate' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <p class="text-xs text-slate-600">{{ $order->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $order->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('orders.receipt', $order) }}"
                               target="_blank"
                               class="text-xs text-blue-500 hover:underline font-medium">
                                Receipt
                            </a>
                            @if($order->status !== 'cancelled' && auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                                  onsubmit="return confirm('Cancel order {{ $order->order_number }}?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600">Cancel</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-12 text-slate-400">
                        No orders found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </p>
        {{ $orders->links() }}
    </div>
</div>

@endsection