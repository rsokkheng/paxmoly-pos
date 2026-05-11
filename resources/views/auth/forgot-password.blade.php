<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #0f1117; --surface: #181b23; --border: #272b38; --accent: #f0b429; --accent-d: #c98d00; --text: #e8eaf0; --muted: #6b7280; --danger: #ef4444; --radius: 8px; --mono: 'IBM Plex Mono', monospace; --sans: 'IBM Plex Sans', sans-serif; }
        body { font-family: var(--sans); background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        body::before { content: ''; position: fixed; inset: 0; background-image: linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px); background-size: 40px 40px; opacity: 0.4; pointer-events: none; }
        .wrap { width: 100%; max-width: 400px; position: relative; z-index: 1; }
        .brand { text-align: center; margin-bottom: 28px; }
        .brand-icon { width: 44px; height: 44px; background: var(--accent); border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; color: #000; font-family: var(--mono); margin-bottom: 10px; }
        .brand-name { font-family: var(--mono); font-size: 18px; font-weight: 500; display: block; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 28px; }
        .card-title { font-size: 15px; font-weight: 600; margin-bottom: 8px; }
        .card-desc { font-size: 13px; color: var(--muted); margin-bottom: 22px; line-height: 1.5; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 500; color: var(--muted); font-family: var(--mono); letter-spacing: 0.06em; text-transform: uppercase; }
        .form-control { width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; color: var(--text); font-size: 14px; font-family: var(--sans); outline: none; transition: border-color 0.15s; }
        .form-control:focus { border-color: var(--accent); }
        .form-control::placeholder { color: var(--muted); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 5px; }
        .alert-success { background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.25); color: #22c55e; border-radius: 6px; padding: 12px 14px; font-size: 13px; margin-bottom: 20px; }
        .btn-submit { width: 100%; padding: 11px; background: var(--accent); color: #000; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: var(--sans); transition: background 0.15s; }
        .btn-submit:hover { background: var(--accent-d); }
        .footer-links { display: flex; justify-content: center; margin-top: 18px; }
        .footer-links a { font-size: 12px; color: var(--muted); text-decoration: none; }
        .footer-links a:hover { color: var(--accent); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="brand-icon">S</div>
        <span class="brand-name">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</span>
    </div>

    <div class="card">
        <div class="card-title">Forgot your password?</div>
        <div class="card-desc">Enter your email address and we'll send you a link to reset your password.</div>

        @if(session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input id="email" type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-submit">Send Reset Link</button>
        </form>
    </div>

    <div class="footer-links">
        @if(Route::has('login'))
            <a href="{{ route('login') }}">← Back to sign in</a>
        @endif
    </div>
</div>
</body>
</html>
