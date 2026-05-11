<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password — អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</title>
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
        .card-title { font-size: 15px; font-weight: 600; margin-bottom: 22px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 500; color: var(--muted); font-family: var(--mono); letter-spacing: 0.06em; text-transform: uppercase; }
        .form-control { width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; color: var(--text); font-size: 14px; font-family: var(--sans); outline: none; transition: border-color 0.15s; }
        .form-control:focus { border-color: var(--accent); }
        .form-control::placeholder { color: var(--muted); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 5px; }
        .btn-submit { width: 100%; padding: 11px; background: var(--accent); color: #000; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: var(--sans); transition: background 0.15s; }
        .btn-submit:hover { background: var(--accent-d); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="brand-icon">S</div>
        <span class="brand-name">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</span>
    </div>

    <div class="card">
        <div class="card-title">Set new password</div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input id="email" type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input id="password" type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Min. 8 characters" required autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn-submit">Reset Password</button>
        </form>
    </div>
</div>
</body>
</html>
