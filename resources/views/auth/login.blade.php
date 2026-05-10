<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Paxmoly POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0f1117;
            --surface:  #181b23;
            --border:   #272b38;
            --accent:   #f0b429;
            --accent-d: #c98d00;
            --text:     #e8eaf0;
            --muted:    #6b7280;
            --danger:   #ef4444;
            --radius:   8px;
            --mono:     'IBM Plex Mono', monospace;
            --sans:     'IBM Plex Sans', sans-serif;
        }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Subtle grid background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.4;
            pointer-events: none;
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 48px; height: 48px;
            background: var(--accent);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: #000;
            font-family: var(--mono);
            margin-bottom: 12px;
        }

        .brand-name {
            font-family: var(--mono);
            font-size: 20px;
            font-weight: 500;
            letter-spacing: -0.02em;
            display: block;
        }

        .brand-sub {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--text);
        }

        .alert-danger {
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.25);
            color: var(--danger);
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
            font-family: var(--mono);
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px 12px;
            color: var(--text);
            font-size: 14px;
            font-family: var(--sans);
            outline: none;
            transition: border-color 0.15s;
        }

        .form-control:focus { border-color: var(--accent); }
        .form-control::placeholder { color: var(--muted); }

        .form-control.is-invalid { border-color: var(--danger); }

        .invalid-feedback {
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .form-check input[type=checkbox] {
            width: 16px; height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .form-check label {
            font-size: 13px;
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }

        .btn-submit {
            width: 100%;
            padding: 11px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--sans);
            transition: background 0.15s;
            letter-spacing: 0.01em;
        }

        .btn-submit:hover { background: var(--accent-d); }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .footer-links a {
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.15s;
        }

        .footer-links a:hover { color: var(--accent); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: var(--muted);
            font-size: 12px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .copyright {
            text-align: center;
            margin-top: 24px;
            font-size: 11px;
            color: var(--muted);
            font-family: var(--mono);
        }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="brand">
        <div class="brand-icon">S</div>
        <span class="brand-name">Paxmoly POS</span>
        <div class="brand-sub">Point of Sale &amp; Inventory Management</div>
    </div>

    <div class="card">
        <div class="card-title">Sign in to your account</div>

        @if($errors->any())
            <div class="alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if(session('status'))
            <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#22c55e;border-radius:6px;padding:12px 14px;font-size:13px;margin-bottom:20px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                    autocomplete="email"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>

    <div class="footer-links">
        @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}">Forgot password?</a>
        @endif
        @if(Route::has('register'))
            <a href="{{ route('register') }}">Create account</a>
        @endif
    </div>

    <div class="copyright">© {{ date('Y') }} Paxmoly POS. All rights reserved.</div>
</div>

</body>
</html>
