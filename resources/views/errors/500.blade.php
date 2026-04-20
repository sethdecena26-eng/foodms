<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Server Error · FoodMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'DM Sans',sans-serif; background:#0f172a; background-image:radial-gradient(ellipse 60% 40% at 50% 0%,rgba(239,68,68,.1) 0%,transparent 70%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .card { text-align:center; max-width:420px; width:100%; animation:fadeUp .4s ease both; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .code { font-family:'Syne',sans-serif; font-size:5rem; font-weight:800; line-height:1; color:#ef4444; letter-spacing:-3px; margin-bottom:.5rem; }
        h1 { font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:700; color:#f1f5f9; margin-bottom:.75rem; }
        p { color:#64748b; font-size:.9rem; line-height:1.7; margin-bottom:2rem; }
        .actions { display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; }
        .btn-primary { display:inline-flex; align-items:center; gap:.4rem; background:#f97316; color:white; font-weight:600; font-size:.875rem; padding:.65rem 1.5rem; border-radius:.625rem; text-decoration:none; }
        .btn-ghost { display:inline-flex; align-items:center; gap:.4rem; background:transparent; color:#64748b; font-weight:500; font-size:.875rem; padding:.65rem 1.5rem; border-radius:.625rem; border:1px solid #1e293b; text-decoration:none; }
    </style>
</head>
<body>
<div class="card">
    <div class="code">500</div>
    <h1>Server Error</h1>
    <p>Something went wrong on our end. The error has been logged and will be investigated.</p>
    <div class="actions">
        <a href="javascript:history.back()" class="btn-ghost">← Go Back</a>
        <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
    </div>
</div>
</body>
</html>