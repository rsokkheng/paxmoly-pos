<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — Paxmoly POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #0f1117; --surface: #181b23; --border: #272b38; --accent: #f0b429; --accent-d: #c98d00; --text: #e8eaf0; --muted: #6b7280; --radius: 8px; --mono: 'IBM Plex Mono', monospace; --sans: 'IBM Plex Sans', sans-serif; }
        body { font-family: var(--sans); background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        body::before { content: ''; position: fixed; inset: 0; background-image: linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px); background-size: 40px 40px; opacity: 0.4; pointer-events: none; }
        .wrap { width: 100%; max-width: 440px; position: relative; z-index: 1; text-align: center; }
        .brand-icon { width: 52px; height: 52px; background: var(--accent); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: #000; font-family: var(--mono); margin-bottom: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 32px; text-align: left; }
        .card-icon { font-size: 36px; text-align: center; margin-bottom: 16px; }
        .card-title { font-size: 16px; font-weight: 600; margin-bottom: 10px; text-align: center; }
        .card-desc { font-size: 13px; color: var(--muted); line-height: 1.6; text-align: center; margin-bottom: 24px; }
        .alert-success { background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.25); color: #22c55e; border-radius: 6px; padding: 12px 14px; font-size: 13px; margin-bottom: 20px; text-align: center; }
        .btn-primary { display: block; width: 100%; padding: 11px; background: var(--accent); color: #000; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: var(--sans); text-align: center; text-decoration: none; transition: background 0.15s; margin-bottom: 10px; }
        .btn-primary:hover { background: var(--accent-d); }
        .btn-link { display: block; text-align: center; font-size: 12px; color: var(--muted); text-decoration: none; margin-top: 8px; }
        .btn-link:hover { color: var(--accent); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand-icon">S</div>

    <div class="card">
        <div class="card-icon">📧</div>
        <div class="card-title">Verify your email address</div>
        <div class="card-desc">
            Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent you. If you didn't receive the email, we'll send another one.
        </div>

        @if(session('status') == 'verification-link-sent')
            <div class="alert-success">A new verification link has been sent to your email address.</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-link">Sign out</button>
        </form>
    </div>
</div>
</body>
</html>
