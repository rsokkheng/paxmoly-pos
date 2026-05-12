<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark">
<head>
    {{-- Apply saved theme immediately to prevent flash --}}
    <script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System') — អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&family=Noto+Sans+Khmer:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── DARK theme (default) ── */
        :root, [data-theme="dark"] {
            --bg:        #0f1117;
            --surface:   #181b23;
            --surface2:  #1e2230;
            --border:    #272b38;
            --accent:    #f0b429;
            --accent-d:  #c98d00;
            --text:      #e8eaf0;
            --muted:     #6b7280;
            --danger:    #ef4444;
            --success:   #22c55e;
            --info:      #3b82f6;
            --sidebar-w: 240px;
            --radius:    6px;
            --mono:      'IBM Plex Mono', monospace;
            --sans:      'IBM Plex Sans', 'Noto Sans Khmer', sans-serif;
        }

        /* ── LIGHT theme ── */
        [data-theme="light"] {
            --bg:       #f0f2f5;
            --surface:  #ffffff;
            --surface2: #e8eaed;
            --border:   #d1d5db;
            --text:     #111827;
            --muted:    #6b7280;
        }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            font-size: 14px;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand .logo-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: #000; font-weight: 700;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-name {
            font-family: var(--mono);
            font-size: 15px;
            font-weight: 500;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .sidebar-brand .brand-version {
            font-size: 10px;
            color: var(--muted);
            font-family: var(--mono);
        }

        .nav-section {
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }

        .nav-label {
            font-size: 10px;
            font-family: var(--mono);
            letter-spacing: 0.1em;
            color: var(--muted);
            padding: 8px 20px 4px;
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: var(--muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 400;
            border-left: 2px solid transparent;
            transition: all 0.15s;
        }

        .nav-link:hover {
            color: var(--text);
            background: var(--surface2);
            border-left-color: var(--border);
        }

        .nav-link.active {
            color: var(--accent);
            background: rgba(240, 180, 41, 0.08);
            border-left-color: var(--accent);
        }

        .nav-link i { width: 16px; text-align: center; font-size: 13px; }

        .nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid var(--border);
        }

        .user-pill {
            display: flex; align-items: center; gap: 10px;
        }

        .user-avatar {
            width: 30px; height: 30px;
            background: var(--accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600; color: #000;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 500; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--muted); font-family: var(--mono); }

        /* ── MAIN ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── TOPBAR ── */
        .topbar {
            height: 56px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            flex: 1;
        }

        .topbar-breadcrumb {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--muted);
        }

        .topbar-breadcrumb span { color: var(--muted); }
        .topbar-breadcrumb .sep { margin: 0 6px; opacity: 0.4; }

        .topbar-actions { display: flex; align-items: center; gap: 8px; }

        /* ── CONTENT ── */
        .content {
            padding: 28px;
            flex: 1;
        }

        /* ── CARDS ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            font-family: var(--mono);
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s;
            font-family: var(--sans);
        }

        .btn-primary { background: var(--accent); color: #000; }
        .btn-primary:hover { background: var(--accent-d); }
        .btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
        .btn-danger { background: rgba(239,68,68,.15); color: var(--danger); border: 1px solid rgba(239,68,68,.25); }
        .btn-danger:hover { background: rgba(239,68,68,.25); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-icon { padding: 7px; width: 32px; height: 32px; justify-content: center; }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: var(--bg);
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-family: var(--mono);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
            border-bottom: 1px solid var(--border);
        }

        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
        tbody tr:hover { background: var(--surface2); }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 11px 14px; font-size: 13.5px; vertical-align: middle; }

        .td-mono { font-family: var(--mono); font-size: 12px; color: var(--muted); }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-family: var(--mono);
            font-weight: 500;
        }

        .badge-success { background: rgba(34,197,94,.15); color: var(--success); }
        .badge-danger  { background: rgba(239,68,68,.15);  color: var(--danger); }
        .badge-info    { background: rgba(59,130,246,.15); color: var(--info); }
        .badge-warn    { background: rgba(240,180,41,.15); color: var(--accent); }
        .badge-muted   { background: var(--surface2); color: var(--muted); }

        /* ── FORMS ── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 500; color: var(--muted); font-family: var(--mono); letter-spacing: 0.04em; }
        .form-control {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 9px 12px;
            color: var(--text);
            font-size: 13.5px;
            font-family: var(--sans);
            outline: none;
            transition: border-color 0.15s;
        }
        .form-control:focus { border-color: var(--accent); }
        .form-control::placeholder { color: var(--muted); }
        select.form-control { cursor: pointer; }
        textarea.form-control { resize: vertical; min-height: 90px; }

        .form-row { display: grid; gap: 16px; }
        .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
        .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }

        .form-text { font-size: 11px; color: var(--muted); margin-top: 4px; }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            border: 1px solid;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-success { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.25); color: var(--success); }
        .alert-danger  { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.25); color: var(--danger); }

        /* ── STATS GRID ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--accent);
        }

        .stat-label { font-size: 11px; color: var(--muted); font-family: var(--mono); letter-spacing: 0.06em; text-transform: uppercase; }
        .stat-value { font-size: 26px; font-weight: 600; color: var(--text); margin: 6px 0 4px; font-family: var(--mono); }
        .stat-sub { font-size: 11px; color: var(--muted); }

        /* ── PAGINATION ── */
        .pagination { display: flex; align-items: center; gap: 4px; margin-top: 20px; }
        .page-btn {
            min-width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--radius);
            font-size: 12px;
            font-family: var(--mono);
            text-decoration: none;
            color: var(--muted);
            border: 1px solid var(--border);
            background: var(--surface2);
            transition: all 0.15s;
            padding: 0 8px;
        }
        .page-btn:hover, .page-btn.active { background: var(--accent); color: #000; border-color: var(--accent); }

        /* ── SEARCH ── */
        .search-box {
            position: relative;
        }
        .search-box i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 12px; }
        .search-box .form-control { padding-left: 30px; }

        /* ── UTILITY ── */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .text-muted { color: var(--muted); }
        .text-accent { color: var(--accent); }
        .text-danger { color: var(--danger); }
        .text-success { color: var(--success); }
        .mono { font-family: var(--mono); }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .w-full { width: 100%; }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--muted); }

        /* ── THEME & LANGUAGE SWITCHERS ── */
        .sidebar-controls { margin-top: 12px; display: flex; flex-direction: column; gap: 6px; }
        .ctrl-row { display: flex; gap: 4px; }
        .ctrl-label { font-size: 10px; font-family: var(--mono); color: var(--muted); letter-spacing: .06em; text-transform: uppercase; margin-bottom: 2px; }
        .ctrl-btn {
            flex: 1; padding: 5px 6px; border-radius: var(--radius);
            font-size: 11px; font-family: var(--mono); font-weight: 500;
            text-align: center; text-decoration: none; cursor: pointer;
            border: 1px solid var(--border); color: var(--muted);
            background: var(--surface2); transition: all 0.15s;
            display: flex; align-items: center; justify-content: center; gap: 4px;
        }
        .ctrl-btn:hover { border-color: var(--accent); color: var(--accent); }
        .ctrl-btn.active { background: var(--accent); color: #000; border-color: var(--accent); }
    </style>
    @stack('styles')
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">S</div>
        <div>
            <div class="brand-name">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</div>
            <div class="brand-version">v1.0.0</div>
        </div>
    </div>

    <div class="nav-section">
        <div class="nav-label">{{ __('ui.section_overview') }}</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> {{ __('ui.nav_dashboard') }}
        </a>
        @can('process_sale')
        <a href="{{ route('sales.create') }}" class="nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> {{ __('ui.nav_pos') }}
        </a>
        @endcan
        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> {{ __('ui.nav_sales') }}
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-label">{{ __('ui.section_inventory') }}</div>
        @can('manage_products')
        @php $navOutOfStock = \App\Models\Product::where('is_active', true)->where('stock_quantity', '<=', 0)->count(); @endphp
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="fas fa-box"></i> {{ __('ui.nav_products') }}
            @if($navOutOfStock > 0)
                <span class="nav-badge">{{ $navOutOfStock }}</span>
            @endif
        </a>
      
        <a href="{{ route('product-sets.index') }}" class="nav-link {{ request()->routeIs('product-sets.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i> Product Sets
        </a>
        <a href="{{ route('product-qr.index') }}" class="nav-link {{ request()->routeIs('product-qr.*') ? 'active' : '' }}">
            <i class="fas fa-qrcode"></i> QR Codes
        </a>
        @endcan
        @can('manage_purchases')
        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
            <i class="fas fa-truck-loading"></i> {{ __('ui.nav_purchases') }}
        </a>
        @endcan
        <a href="{{ route('inventory.movements') }}" class="nav-link {{ request()->routeIs('inventory.movements') ? 'active' : '' }}">
            <i class="fas fa-exchange-alt"></i> {{ __('ui.nav_movements') }}
        </a>
        @can('adjust_stock')
        <a href="{{ route('inventory.adjustments.create') }}" class="nav-link {{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }}">
            <i class="fas fa-sliders-h"></i> {{ __('ui.nav_adjust') }}
        </a>
        <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
            <i class="fas fa-award"></i> {{ __('ui.nav_brands') }}
        </a>
        @endcan
        @can('manage_categories')
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-tag"></i> {{ __('ui.nav_categories') }}
        </a>
        @endcan
        @can('manage_units')
        <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
            <i class="fas fa-ruler"></i> {{ __('ui.nav_units') }}
        </a>
        @endcan
        @can('manage_discounts')
        <a href="{{ route('discounts.index') }}" class="nav-link {{ request()->routeIs('discounts.*') ? 'active' : '' }}">
            <i class="fas fa-percent"></i> {{ __('ui.nav_discounts') }}
        </a>
        @endcan
        @can('manage_users')
        <a href="{{ route('taxes.index') }}" class="nav-link {{ request()->routeIs('taxes.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> {{ __('ui.nav_taxes') }}
        </a>
        @endcan
    </div>

    <div class="nav-section">
        <div class="nav-label">{{ __('ui.section_people') }}</div>
        @can('manage_suppliers')
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> {{ __('ui.nav_suppliers') }}
        </a>
        @endcan
        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> {{ __('ui.nav_customers') }}
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-label">{{ __('ui.section_reports') }}</div>
        @can('view_reports')
        <a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> {{ __('ui.nav_report_sales') }}
        </a>
        <a href="{{ route('reports.sales_period') }}" class="nav-link {{ request()->routeIs('reports.sales_period') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> {{ __('ui.nav_report_period') }}
        </a>
        <a href="{{ route('reports.top_products') }}" class="nav-link {{ request()->routeIs('reports.top_products') ? 'active' : '' }}">
            <i class="fas fa-trophy"></i> {{ __('ui.nav_report_top') }}
        </a>
        @endcan
        @can('view_profit_report')
        <a href="{{ route('reports.profit') }}" class="nav-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
            <i class="fas fa-coins"></i> {{ __('ui.nav_report_profit') }}
        </a>
        @endcan
        @can('view_reports')
        <a href="{{ route('reports.stock') }}" class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i> {{ __('ui.nav_report_stock') }}
        </a>
        @endcan
    </div>

    @can('manage_users')
    <div class="nav-section">
        <div class="nav-label">System</div>
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> Users
        </a>
    </div>
    @endcan

    <div class="sidebar-footer">
        <div class="user-pill">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="user-role">{{ auth()->user()->role_label }}</div>
            </div>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               style="color: var(--muted); text-decoration: none; font-size: 12px;" title="{{ __('ui.cancel') }}">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

        <div class="sidebar-controls">
            {{-- Theme Switcher --}}
            <div>
                <div class="ctrl-label">Mode</div>
                <div class="ctrl-row">
                    <button onclick="setTheme('dark')"  id="btn-dark"  class="ctrl-btn">
                        <i class="fas fa-moon"></i> Dark
                    </button>
                    <button onclick="setTheme('light')" id="btn-light" class="ctrl-btn">
                        <i class="fas fa-sun"></i> Light
                    </button>
                </div>
            </div>
            {{-- Language Switcher --}}
            <div>
                <div class="ctrl-label">{{ __('ui.language') }}</div>
                <div class="ctrl-row">
                    <a href="{{ route('language.switch', 'en') }}"
                       class="ctrl-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        🇬🇧 EN
                    </a>
                    <a href="{{ route('language.switch', 'km') }}"
                       class="ctrl-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}">
                        🇰🇭 ខ្មែរ
                    </a>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <header class="topbar">
        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-breadcrumb">
                <span>អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</span>
                @hasSection('breadcrumb')
                    <span class="sep">/</span>@yield('breadcrumb')
                @endif
            </div>
        </div>
        <div class="topbar-actions">
            @yield('topbar-actions')
        </div>
    </header>

    <main class="content">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    document.getElementById('btn-dark').classList.toggle('active',  theme === 'dark');
    document.getElementById('btn-light').classList.toggle('active', theme === 'light');
}
// Sync button state to current theme on load
(function () {
    var t = localStorage.getItem('theme') || 'dark';
    document.getElementById('btn-dark').classList.toggle('active',  t === 'dark');
    document.getElementById('btn-light').classList.toggle('active', t === 'light');
})();
</script>
</body>
</html>
