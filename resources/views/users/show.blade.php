@extends('layouts.app')

@section('title', $user->name)
@section('page-title', $user->name)
@section('page-subtitle', ucfirst($user->role?->name ?? 'No role') . ' · Member since ' . $user->created_at->format('M Y'))

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Profile card --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-slate-100 p-6 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold text-white mx-auto mb-3"
                 style="background:var(--accent)">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 class="font-display font-bold text-xl text-slate-800">{{ $user->name }}</h2>
            <p class="text-slate-400 text-sm mt-0.5">{{ $user->email }}</p>
            <span class="inline-block mt-2 text-xs font-bold px-3 py-1 rounded-full capitalize
                {{ $user->role?->name === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-600' }}">
                {{ $user->role?->label ?? 'No Role' }}
            </span>

            <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-slate-100">
                <div>
                    <p class="text-lg font-display font-bold text-slate-800">{{ number_format($orderStats->total_orders ?? 0) }}</p>
                    <p class="text-xs text-slate-400">Orders</p>
                </div>
                <div>
                    <p class="text-lg font-display font-bold text-slate-800">₱{{ number_format($orderStats->total_revenue ?? 0, 0) }}</p>
                    <p class="text-xs text-slate-400">Revenue</p>
                </div>
                <div>
                    <p class="text-lg font-display font-bold text-emerald-600">₱{{ number_format($orderStats->total_profit ?? 0, 0) }}</p>
                    <p class="text-xs text-slate-400">Profit</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-2">
            <a href="{{ route('users.edit', $user) }}" class="btn-ghost w-full text-center block">Edit Account</a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('users.destroy', $user) }}"
                  onsubmit="return confirm('Archive {{ addslashes($user->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full py-2 px-4 rounded-lg text-sm font-semibold text-amber-600 border border-amber-200 hover:bg-amber-50 transition-colors">
                    Archive User
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-display font-bold text-slate-800">Recent Orders</h3>
                <p class="text-xs text-slate-400 mt-0.5">Last 10 transactions processed by {{ $user->name }}</p>
            </div>
            <table class="fms-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Order #</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Profit</th>
                        <th class="text-center">Payment</th>
                        <th class="text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    @php
                        $statusClasses = ['completed'=>'badge-green','pending'=>'badge-amber','cancelled'=>'badge-slate'];
                    @endphp
                    <tr>
                        <td><span class="font-mono text-xs font-semibold text-slate-700">{{ $order->order_number }}</span></td>
                        <td class="text-center">
                            <span class="badge {{ $statusClasses[$order->status] ?? 'badge-slate' }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="text-right font-semibold text-slate-700">₱{{ number_format($order->total_amount, 2) }}</td>
                        <td class="text-right font-semibold text-emerald-600">₱{{ number_format($order->net_profit, 2) }}</td>
                        <td class="text-center capitalize text-sm text-slate-500">{{ $order->payment_method }}</td>
                        <td class="text-sm text-slate-400">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-slate-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection