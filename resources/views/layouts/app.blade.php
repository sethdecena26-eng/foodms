<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FoodMS') }} — @yield('title', 'Dashboard')</title>

    {{-- Fonts: Clash Display (headings) + DM Sans (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    {{-- Flowbite + Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <style>
        :root {
            --brand-900: #0f172a;
            --brand-800: #1e293b;
            --brand-700: #334155;
            --brand-600: #475569;
            --accent:    #f97316;   /* vivid orange — food/warmth */
            --accent-2:  #fb923c;
            --success:   #22c55e;
            --danger:    #ef4444;
            --warning:   #eab308;
            --sidebar-w: 260px;
        }

        body { font-family: 'DM Sans', sans-serif; background: #f8fafc; }

        h1, h2, h3, .font-display { font-family: 'Syne', sans-serif; }

        /* ── Sidebar ── */
        #app-sidebar {
            width: var(--sidebar-w);
            background: var(--brand-900);
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(249,115,22,.15) 0%, transparent 70%);
        }

        .sidebar-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: -.5px;
        }

        .sidebar-logo span { color: var(--accent); }

        .nav-section-label {
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #475569;
            padding: .75rem 1rem .35rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .5rem 1rem;
            border-radius: .5rem;
            margin: .1rem .5rem;
            font-size: .875rem;
            font-weight: 500;
            color: #94a3b8;
            transition: all .15s ease;
            text-decoration: none;
        }

        .nav-item:hover {
            background: rgba(255,255,255,.06);
            color: #f1f5f9;
        }

        .nav-item.active {
            background: rgba(249,115,22,.15);
            color: var(--accent);
            font-weight: 600;
        }

        .nav-item svg { flex-shrink: 0; width: 1rem; height: 1rem; }

        /* ── Topbar ── */
        #topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            height: 64px;
        }

        /* ── KPI Cards ── */
        .kpi-card {
            background: white;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            transition: box-shadow .2s, transform .2s;
        }
        .kpi-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
            transform: translateY(-2px);
        }
        .kpi-card .kpi-icon {
            width: 2.5rem; height: 2.5rem;
            border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
        }
        .kpi-card .kpi-value {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            margin-top: .5rem;
        }

        /* ── Low-stock badge ── */
        .low-stock-badge {
            background: #fef2f2;
            color: #dc2626;
            font-size: .7rem;
            font-weight: 700;
            padding: .15rem .45rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* ── Tables ── */
        .fms-table thead th {
            background: #f8fafc;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
            padding: .75rem 1rem;
        }
        .fms-table tbody td { padding: .75rem 1rem; vertical-align: middle; }
        .fms-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .1s; }
        .fms-table tbody tr:hover { background: #fafafa; }

        /* ── POS Grid ── */
        .menu-card {
            background: white;
            border-radius: .875rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all .18s ease;
            overflow: hidden;
        }
        .menu-card:hover { border-color: var(--accent); box-shadow: 0 4px 20px rgba(249,115,22,.15); }
        .menu-card.out-of-stock { opacity: .45; cursor: not-allowed; filter: grayscale(.6); }

        /* ── Cart ── */
        #pos-cart {
            background: white;
            border-left: 1px solid #e2e8f0;
        }

        /* ── Btn primary ── */
        .btn-primary {
            background: var(--accent);
            color: white;
            font-weight: 600;
            border-radius: .625rem;
            padding: .55rem 1.25rem;
            font-size: .875rem;
            transition: background .15s, transform .1s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        .btn-primary:hover { background: var(--accent-2); transform: translateY(-1px); }

        .btn-ghost {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 500;
            border-radius: .625rem;
            padding: .5rem 1rem;
            font-size: .875rem;
            cursor: pointer;
            transition: all .15s;
        }
        .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* ── Notification dot ── */
        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid white;
        }

        /* ── Page fade-in ── */
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .page-content { animation: fadeUp .35s ease both; }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body class="h-full flex overflow-hidden bg-slate-50">

{{-- ═══════════════════════════════════════════════════════ SIDEBAR ═══ --}}
<aside id="app-sidebar" class="flex flex-col h-full flex-shrink-0 overflow-y-auto">

    {{-- Logo --}}
    <div class="flex items-center gap-2.5 px-5 py-5 border-b border-white/10">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:var(--accent)">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M12 3C7 3 3 7.58 3 11c0 1.93.78 3.68 2.05 4.97L6 20h12l.95-4.03C20.22 14.68 21 12.93 21 11c0-3.42-4-8-9-8z"/>
            </svg>
        </div>
        <span class="sidebar-logo text-white">Food<span>MS</span></span>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 py-3">

        {{-- Sales --}}
        <div class="nav-section-label">Sales</div>

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('pos.index') }}"
           class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Point of Sale
        </a>

        <a href="{{ route('orders.index') }}"
           class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Orders
        </a>

        {{-- Kitchen --}}
        <div class="nav-section-label mt-2">Kitchen</div>

        <a href="{{ route('menu-items.index') }}"
           class="nav-item {{ request()->routeIs('menu-items.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Menu & Costing
        </a>

        <a href="{{ route('inventory.index') }}"
           class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Inventory
            @php $lowCount = \App\Models\Ingredient::whereRaw('quantity_in_stock <= low_stock_threshold')->count(); @endphp
            @if($lowCount > 0)
                <span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 leading-none">
                    {{ $lowCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('food-safety.temperature') }}"
           class="nav-item {{ request()->routeIs('food-safety.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Food Safety
        </a>

        <a href="{{ route('waste.index') }}"
           class="nav-item {{ request()->routeIs('waste.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Waste & Expiry
        </a>

        {{-- Admin --}}
        @if(auth()->user()->isAdmin())
        <div class="nav-section-label mt-2">Admin</div>

        <a href="{{ route('reports.sales') }}"
           class="nav-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Sales Report
        </a>

        <a href="{{ route('reports.waste') }}"
           class="nav-item {{ request()->routeIs('reports.waste') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Waste Report
        </a>

        <a href="{{ route('reports.food-safety') }}"
           class="nav-item {{ request()->routeIs('reports.food-safety') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Safety Report
        </a>

        <a href="{{ route('archives.menu-items') }}"
           class="nav-item {{ request()->routeIs('archives.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Archives
            @php
                $archivedCount = \App\Models\MenuItem::onlyTrashed()->count()
                    + \App\Models\Ingredient::onlyTrashed()->count()
                    + \App\Models\User::onlyTrashed()->count();
            @endphp
            @if($archivedCount > 0)
                <span class="ml-auto bg-slate-600 text-white text-xs font-bold rounded-full px-1.5 py-0.5 leading-none">
                    {{ $archivedCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('users.index') }}"
           class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            User Management
        </a>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="border-t border-white/10 px-4 py-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white"
                 style="background: var(--accent)">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-200 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 truncate capitalize">{{ auth()->user()->role?->name ?? 'staff' }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- ═════════════════════════════════════════════════ MAIN COLUMN ═══ --}}
<div class="flex flex-col flex-1 min-w-0 overflow-hidden">

    {{-- ── Topbar ──────────────────────────────────────────────────── --}}
    <header id="topbar" class="flex items-center justify-between px-6 flex-shrink-0 z-10">
        <div>
            <h1 class="text-lg font-display font-bold text-slate-800">@yield('page-title', 'Dashboard')</h1>
            <p class="text-xs text-slate-400">@yield('page-subtitle', now()->format('l, F j, Y'))</p>
        </div>

        <div class="flex items-center gap-3">

            {{-- Low-stock bell --}}
            <div class="relative">
                <button id="notifBtn" type="button"
                        class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors relative">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($lowCount > 0)
                        <span class="notif-dot"></span>
                    @endif
                </button>

                {{-- Dropdown --}}
                <div id="notifDropdown"
                     class="hidden absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <span class="font-semibold text-sm text-slate-700">Alerts</span>
                        @php
                            $tempAlertsToday = \App\Models\TemperatureLog::where('log_date', today()->format('Y-m-d'))
                                ->whereRaw('temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius')
                                ->count();
                            $totalAlerts = $lowCount + $tempAlertsToday;
                        @endphp
                        <span class="text-xs bg-red-100 text-red-600 font-bold px-2 py-0.5 rounded-full">{{ $totalAlerts }}</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto divide-y divide-slate-50">
                        {{-- Temperature alerts --}}
                        @if($tempAlertsToday > 0)
                        <div class="px-4 py-2 bg-red-50">
                            <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-1">🌡️ Temperature Alerts Today</p>
                            @foreach(\App\Models\TemperatureLog::where('log_date', today()->format('Y-m-d'))->whereRaw('temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius')->get() as $alert)
                            <div class="flex items-center justify-between py-1">
                                <p class="text-sm font-medium text-red-700">{{ $alert->location }}</p>
                                <span class="text-xs font-bold text-red-600">{{ $alert->temperature_celsius }}°C</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        {{-- Low stock alerts --}}
                        @if($lowCount > 0)
                        <div class="px-4 py-2">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">📦 Low Stock</p>
                        </div>
                        @endif
                        @foreach(\App\Models\Ingredient::whereRaw('quantity_in_stock <= low_stock_threshold')->get() as $ing)
                        <div class="px-4 py-2.5 flex items-center justify-between hover:bg-slate-50">
                            <div>
                                <p class="text-sm font-medium text-slate-700">{{ $ing->name }}</p>
                                <p class="text-xs text-slate-400">{{ $ing->quantity_in_stock }} / {{ $ing->low_stock_threshold }} {{ $ing->unit }}</p>
                            </div>
                            <span class="low-stock-badge">Low</span>
                        </div>
                        @endforeach
                        @if($totalAlerts === 0)
                        <div class="px-4 py-6 text-center text-sm text-slate-400">All clear ✓</div>
                        @endif
                    </div>
                    <div class="px-4 py-2 border-t border-slate-100">
                        <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-orange-500 hover:underline">
                            View Inventory →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Profile --}}
            <div class="relative">
                <button id="profileBtn" type="button"
                        class="flex items-center gap-2 rounded-full pl-1 pr-3 py-1 hover:bg-slate-100 transition-colors">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                         style="background:var(--accent)">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="profileDropdown"
                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="/profile"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        My Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ── Flash messages ──────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="mx-6 mt-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700" x-data x-init="setTimeout(() => $el.remove(), 4000)">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Page body ───────────────────────────────────────────────── --}}
    <main class="flex-1 overflow-y-auto p-6 page-content">
        @yield('content')
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Notification & Profile dropdowns
['notif','profile'].forEach(key => {
    const btn  = document.getElementById(key+'Btn');
    const drop = document.getElementById(key+'Dropdown');
    if (!btn) return;
    btn.addEventListener('click', e => {
        e.stopPropagation();
        drop.classList.toggle('hidden');
    });
});
document.addEventListener('click', () => {
    document.getElementById('notifDropdown')?.classList.add('hidden');
    document.getElementById('profileDropdown')?.classList.add('hidden');
});
</script>

@livewireScripts
@stack('scripts')
</body>
</html>