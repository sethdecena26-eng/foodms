<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access Denied · FoodMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f172a;
            background-image:
                radial-gradient(ellipse 60% 40% at 50% 0%, rgba(249,115,22,.12) 0%, transparent 70%),
                radial-gradient(ellipse 40% 40% at 20% 80%, rgba(249,115,22,.06) 0%, transparent 70%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            text-align: center;
            max-width: 420px;
            width: 100%;
            animation: fadeUp .4s ease both;
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .icon-wrap {
            width: 80px; height: 80px;
            border-radius: 1.25rem;
            background: rgba(249,115,22,.15);
            border: 1px solid rgba(249,115,22,.25);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .icon-wrap svg { width: 36px; height: 36px; color: #f97316; }

        .code {
            font-family: 'Syne', sans-serif;
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            color: #f97316;
            letter-spacing: -3px;
            margin-bottom: .5rem;
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: .75rem;
        }

        p {
            color: #64748b;
            font-size: .9rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }

        .btn-primary {
            display: inline-flex; align-items: center; gap: .4rem;
            background: #f97316; color: white;
            font-weight: 600; font-size: .875rem;
            padding: .65rem 1.5rem;
            border-radius: .625rem;
            text-decoration: none;
            transition: background .15s, transform .1s;
        }
        .btn-primary:hover { background: #fb923c; transform: translateY(-1px); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: .4rem;
            background: transparent;
            color: #64748b;
            font-weight: 500; font-size: .875rem;
            padding: .65rem 1.5rem;
            border-radius: .625rem;
            border: 1px solid #1e293b;
            text-decoration: none;
            transition: all .15s;
        }
        .btn-ghost:hover { border-color: #334155; color: #94a3b8; }

        .divider {
            border: none;
            border-top: 1px solid #1e293b;
            margin: 2rem 0;
        }

        .role-hint {
            display: inline-flex; align-items: center; gap: .4rem;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: .5rem;
            padding: .4rem .75rem;
            font-size: .75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>

    <div class="code">403</div>
    <h1>Access Denied</h1>
    <p>
        You don't have permission to view this page.<br>
        This area requires a higher access level than your current role allows.
    </p>

    @auth
    <div style="margin-bottom:1.5rem">
        <span class="role-hint">
            <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Signed in as {{ auth()->user()->name }}
            ({{ ucfirst(auth()->user()->role?->name ?? 'no role') }})
        </span>
    </div>
    @endauth

    <div class="actions">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
           class="btn-ghost">
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Go Back
        </a>
        <a href="{{ route('dashboard') }}" class="btn-primary">
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
    </div>

    <hr class="divider">
    <p style="font-size:.75rem; margin:0">
        If you believe this is a mistake, contact your system administrator.
    </p>
</div>
</body>
</html>