<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark">
<head>
    <script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Point of Sale — អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root, [data-theme="dark"] {
            --bg: #0f1117; --surface: #181b23; --surface2: #1e2230; --border: #272b38;
            --accent: #f0b429; --accent-d: #c98d00; --text: #e8eaf0; --muted: #6b7280;
            --danger: #ef4444; --success: #22c55e;
            --mono: 'IBM Plex Mono', monospace; --sans: 'IBM Plex Sans', sans-serif;
            --radius: 6px;
        }
        [data-theme="light"] {
            --bg: #f0f2f5; --surface: #ffffff; --surface2: #e8eaed; --border: #d1d5db;
            --text: #111827; --muted: #6b7280;
        }
        .theme-btn {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 5px 10px; color: var(--muted);
            font-size: 12px; cursor: pointer; display: flex; align-items: center;
            gap: 5px; transition: all 0.15s; font-family: var(--sans);
        }
        .theme-btn:hover { border-color: var(--accent); color: var(--accent); }
        body { font-family: var(--sans); background: var(--bg); color: var(--text); height: 100vh; display: flex; flex-direction: column; overflow: hidden; font-size: 14px; }
        .pos-header { height: 52px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 20px; gap: 16px; flex-shrink: 0; }
        .pos-header .brand { font-family: var(--mono); font-size: 14px; font-weight: 500; color: var(--accent); }
        .pos-header .sep { color: var(--border); margin: 0 4px; }
        .pos-header .title { color: var(--muted); font-size: 13px; }
        .pos-header .spacer { flex: 1; }
        .pos-header .clock-chip {
            font-family: var(--mono); font-size: 13px; letter-spacing: .03em;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 4px 10px; color: var(--text);
        }
        .pos-header .cashier-info {
            font-size: 12px; color: var(--muted);
            display: flex; align-items: center; gap: 6px;
        }
        .pos-header .cashier-info i { color: var(--accent); font-size: 13px; }
        .pos-body { flex: 1; display: grid; grid-template-columns: 1fr 380px; overflow: hidden; }
        .pos-left { display: flex; flex-direction: column; border-right: 1px solid var(--border); overflow: hidden; }
        .pos-search { padding: 14px 16px; border-bottom: 1px solid var(--border); display: flex; gap: 8px; }
        .pos-search input { flex: 1; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 8px 12px; color: var(--text); font-size: 13.5px; font-family: var(--sans); outline: none; }
        .pos-search input:focus { border-color: var(--accent); }
        .category-tabs { display: flex; gap: 6px; overflow-x: auto; padding: 10px 16px; border-bottom: 1px solid var(--border); flex-shrink: 0; scrollbar-width: none; }
        .category-tabs::-webkit-scrollbar { display: none; }
        .cat-tab { padding: 4px 12px; border-radius: 20px; border: 1px solid var(--border); font-size: 12px; cursor: pointer; white-space: nowrap; color: var(--muted); background: transparent; font-family: var(--sans); transition: all 0.15s; }
        .cat-tab:hover, .cat-tab.active { background: var(--accent); border-color: var(--accent); color: #000; }
        .product-grid { flex: 1; overflow-y: auto; padding: 14px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; align-content: start; }
        .product-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all 0.15s; text-align: center; user-select: none; overflow: hidden; display: flex; flex-direction: column; }
        .product-card:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.3); }
        .product-card:active { transform: translateY(0); }
        /* Image area */
        .product-card .p-img { width: 100%; height: 100px; object-fit: cover; display: block; background: var(--surface2); flex-shrink: 0; }
        .product-card .p-img-placeholder { width: 100%; height: 100px; display: flex; align-items: center; justify-content: center; background: var(--surface2); color: var(--border); font-size: 28px; flex-shrink: 0; }
        /* Info area */
        .product-card .p-info { padding: 10px 10px 12px; flex: 1; display: flex; flex-direction: column; gap: 3px; }
        .product-card .p-name { font-size: 12px; font-weight: 600; line-height: 1.35; color: var(--text); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .product-card .p-price { font-family: var(--mono); font-size: 14px; color: var(--accent); font-weight: 500; margin-top: 2px; }
        .product-card .p-stock { font-size: 10px; color: var(--muted); }
        .product-card.out-of-stock { opacity: 0.4; pointer-events: none; }
        .p-unit-btns { display: flex; gap: 4px; padding: 6px 8px 8px; }
        .p-unit-btn { flex: 1; padding: 5px 4px; font-size: 10px; font-weight: 700; font-family: var(--mono); border: 1px solid var(--accent); border-radius: 4px; background: transparent; color: var(--accent); cursor: pointer; transition: all 0.15s; white-space: nowrap; }
        .p-unit-btn:hover { background: var(--accent); color: #000; }
        .no-products { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px; color: var(--muted); gap: 8px; font-size: 13px; }

        /* ── Product Set card styles ── */
        .p-img-wrap { position: relative; width: 100%; height: 100px; flex-shrink: 0; overflow: hidden; background: var(--surface2); }
        .p-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .p-img-wrap .p-img-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .p-stock-badge { position: absolute; bottom: 6px; right: 6px; font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 10px; letter-spacing: .02em; pointer-events: none; }
        .p-stock-badge.ok  { background: rgba(34,197,94,.92);  color: #fff; }
        .p-stock-badge.low { background: rgba(245,158,11,.92); color: #000; }
        .p-stock-badge.out { background: rgba(239,68,68,.92);  color: #fff; }
        .p-set-badge { position: absolute; top: 6px; left: 6px; background: var(--accent); color: #000; font-size: 8px; font-weight: 700; padding: 2px 6px; border-radius: 10px; pointer-events: none; }
        .p-brand { font-size: 10px; color: var(--muted); margin-top: 1px; }
        .p-price-sub { font-size: 9px; color: var(--muted); margin-top: -1px; }
        .btn-unit-label { display: block; font-size: 9px; opacity: .7; margin-top: 2px; font-weight: 400; }

        /* ── Sets section header ── */
        .sets-section { grid-column: 1/-1; }
        .sets-section-label { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; color: var(--muted); letter-spacing: .06em; text-transform: uppercase; padding: 12px 0 10px; border-top: 1px solid var(--border); margin-top: 4px; }
        .sets-section-label i { color: var(--accent); font-size: 13px; }
        .sets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)); gap: 10px; }
        .pos-right { display: flex; flex-direction: column; overflow: hidden; }
        .cart-header { padding: 14px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .cart-title { font-family: var(--mono); font-size: 13px; font-weight: 500; }
        .cart-count { background: var(--accent); color: #000; font-size: 11px; font-weight: 600; font-family: var(--mono); border-radius: 10px; padding: 1px 7px; }
        .cart-customer { padding: 10px 16px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
        .cart-customer select { width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 7px 10px; color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; }
        .cart-customer select:focus { border-color: var(--accent); }
        .cart-items { flex: 1; overflow-y: auto; padding: 8px 0; }
        .cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--muted); gap: 8px; }
        .cart-empty i { font-size: 32px; opacity: 0.3; }
        .cart-item { border-bottom: 1px solid var(--border); }
        .ci-main { padding: 10px 16px; display: flex; align-items: center; gap: 10px; }
        .ci-name { flex: 1; min-width: 0; }
        .ci-name .name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ci-name .price { font-size: 11px; color: var(--muted); font-family: var(--mono); }
        .ci-qty { display: flex; align-items: center; gap: 4px; }
        .qty-btn { width: 24px; height: 24px; border: 1px solid var(--border); background: var(--surface2); color: var(--text); border-radius: 4px; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.1s; }
        .qty-btn:hover { border-color: var(--accent); color: var(--accent); }
        .qty-val { font-family: var(--mono); font-size: 13px; width: 28px; text-align: center; }
        .ci-total { font-family: var(--mono); font-size: 13px; font-weight: 500; min-width: 55px; text-align: right; }
        .ci-remove { color: var(--muted); cursor: pointer; font-size: 12px; padding: 4px; transition: color 0.15s; }
        .ci-remove:hover { color: var(--danger); }
        .ci-disc-btn { width: 26px; height: 26px; border: 1px solid var(--border); background: transparent; color: var(--muted); border-radius: 4px; cursor: pointer; font-size: 11px; display: flex; align-items: center; justify-content: center; transition: all 0.15s; flex-shrink: 0; }
        .ci-disc-btn:hover { border-color: var(--accent); color: var(--accent); }
        .ci-disc-btn.active { border-color: var(--danger); color: var(--danger); background: rgba(220,53,69,.08); }
        .ci-disc-badge { font-size: 10px; color: var(--danger); font-weight: 700; margin-left: 3px; }
        /* Discount popup modal */
        .disc-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 600; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.18s; }
        .disc-modal-overlay.open { opacity: 1; pointer-events: all; }
        .disc-modal { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 22px 24px 20px; width: 300px; box-shadow: 0 20px 60px rgba(0,0,0,.4); transform: scale(.95); transition: transform 0.18s; }
        .disc-modal-overlay.open .disc-modal { transform: scale(1); }
        .disc-modal-title { font-size: 13px; font-weight: 700; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .disc-modal-sub { font-size: 11px; color: var(--muted); margin-bottom: 16px; font-family: var(--mono); }
        .disc-type-row { display: flex; gap: 6px; margin-bottom: 14px; }
        .disc-type-btn { flex: 1; padding: 7px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface2); color: var(--muted); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
        .disc-type-btn.active { border-color: var(--accent); background: var(--accent); color: #000; }
        .disc-val-row { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .disc-val-row input { flex: 1; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; color: var(--text); font-size: 18px; font-family: var(--mono); font-weight: 700; outline: none; text-align: right; }
        .disc-val-row input:focus { border-color: var(--accent); }
        .disc-val-row .disc-unit { font-size: 18px; font-weight: 700; color: var(--accent); min-width: 20px; }
        .disc-preview { font-size: 12px; color: var(--danger); font-family: var(--mono); font-weight: 600; min-height: 18px; margin-bottom: 16px; }
        .disc-modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .disc-modal-actions button { padding: 9px; border-radius: var(--radius); border: 1px solid var(--border); background: var(--surface2); color: var(--text); font-size: 13px; cursor: pointer; transition: all 0.15s; }
        .disc-modal-actions button.primary { background: var(--accent); border-color: var(--accent); color: #000; font-weight: 700; }
        .disc-modal-actions button:hover:not(.primary) { border-color: var(--accent); }
        .cart-totals { padding: 14px 16px; border-top: 1px solid var(--border); flex-shrink: 0; }
        .total-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; }
        .total-row .label { color: var(--muted); }
        .total-row .value { font-family: var(--mono); }
        .total-row.grand { font-size: 18px; font-weight: 700; margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border); }
        .total-row.grand .value { color: var(--accent); }
        .discount-row { display: flex; gap: 6px; margin-bottom: 10px; }
        .discount-row input { flex: 1; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 6px 10px; color: var(--text); font-family: var(--mono); font-size: 12px; outline: none; }
        .discount-row input:focus { border-color: var(--accent); }
        .discount-row button { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius); padding: 6px 10px; color: var(--text); cursor: pointer; font-size: 12px; transition: all 0.15s; }
        .discount-row button:hover { border-color: var(--accent); color: var(--accent); }
        .cart-payment { padding: 14px 16px; border-top: 1px solid var(--border); flex-shrink: 0; }
        .payment-methods { display: grid; grid-template-columns: repeat(4,1fr); gap: 6px; margin-bottom: 10px; }
        .pay-method { padding: 7px 4px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface2); color: var(--muted); font-size: 11px; text-align: center; cursor: pointer; transition: all 0.15s; }
        .pay-method.active { border-color: var(--accent); color: var(--accent); background: rgba(240,180,41,.1); }
        .pay-method i { display: block; font-size: 14px; margin-bottom: 2px; }
        .tendered-row { display: flex; gap: 8px; align-items: center; margin-bottom: 10px; }
        .tendered-row label { font-size: 12px; color: var(--muted); white-space: nowrap; }
        .tendered-row input { flex: 1; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 6px 10px; color: var(--text); font-family: var(--mono); font-size: 14px; outline: none; text-align: right; }
        .tendered-row input:focus { border-color: var(--accent); }
        .btn-checkout { width: 100%; padding: 12px; background: var(--accent); color: #000; border: none; border-radius: var(--radius); font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-family: var(--sans); transition: background 0.15s; }
        .btn-checkout:hover { background: var(--accent-d); }
        .btn-checkout:disabled { opacity: 0.4; cursor: not-allowed; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 200; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 28px; width: 380px; }
        .modal h3 { font-family: var(--mono); font-size: 16px; margin-bottom: 16px; }
        .modal-total { font-size: 36px; font-weight: 700; color: var(--accent); font-family: var(--mono); text-align: center; margin: 12px 0; }
        .modal-change-row { text-align: center; padding: 12px; background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.2); border-radius: var(--radius); margin-bottom: 16px; }
        .modal-change-label { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
        .modal-change-val { font-size: 24px; font-weight: 700; color: var(--success); font-family: var(--mono); }
        .modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 16px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 16px; border-radius: var(--radius); font-size: 13px; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; font-family: var(--sans); }
        .btn-primary { background: var(--accent); color: #000; }
        .btn-primary:hover { background: var(--accent-d); }
        .btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--accent); }

        /* ── Toast notifications ── */
        #toastContainer {
            position: fixed;
            top: 64px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 10px;
            font-family: var(--sans);
            box-shadow: 0 8px 32px rgba(0,0,0,.4);
            animation: toastIn 0.25s cubic-bezier(.34,1.56,.64,1) forwards;
            pointer-events: none;
            min-width: 280px;
            max-width: 360px;
        }
        .toast-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .toast-body { flex: 1; min-width: 0; }
        .toast-title { font-size: 13px; font-weight: 700; line-height: 1.3; margin-bottom: 3px; }
        .toast-detail { font-size: 12px; opacity: .85; line-height: 1.4; }
        .toast.danger  { background: #ef4444; color: #fff; border: 1px solid rgba(255,255,255,.18); }
        .toast.warning { background: #f59e0b; color: #000; border: 1px solid rgba(0,0,0,.08); }
        .toast.success { background: #22c55e; color: #fff; border: 1px solid rgba(255,255,255,.18); }
        .toast.fade-out { animation: toastOut 0.28s ease forwards; }
        @keyframes toastIn  { from { opacity:0; transform:translateX(24px) scale(.95); } to { opacity:1; transform:translateX(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0) scale(1); } to { opacity:0; transform:translateX(16px) scale(.97); } }
        @media (max-width: 767px) {
            #toastContainer { right: 12px; left: 12px; align-items: stretch; }
            .toast { min-width: 0; max-width: 100%; }
        }

        /* ── CART BACK BUTTON (mobile only, hidden by default) ── */
        .cart-back-btn {
            display: none;
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 18px;
            padding: 4px 10px 4px 0;
            line-height: 1;
            transition: color 0.15s;
            flex-shrink: 0;
        }
        .cart-back-btn:active { color: var(--text); }

        /* ── FAB CART (mobile only, hidden by default) ── */
        .fab-cart {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 140;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 28px;
            padding: 13px 28px;
            font-size: 14px;
            font-weight: 700;
            font-family: var(--sans);
            cursor: pointer;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 24px rgba(240,180,41,.45);
            transition: box-shadow 0.15s;
            white-space: nowrap;
        }
        .fab-cart:active { box-shadow: 0 2px 12px rgba(240,180,41,.3); }
        .fab-badge {
            background: rgba(0,0,0,.25);
            border-radius: 10px;
            padding: 1px 8px;
            font-size: 12px;
            font-family: var(--mono);
        }

        /* ── TABLET: narrower cart, same two-column ── */
        @media (min-width: 768px) and (max-width: 1023px) {
            .pos-body { grid-template-columns: 1fr 300px; }
            .qty-btn  { width: 30px; height: 30px; }
            .product-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
            .pay-method { padding: 9px 4px; }
            .pay-method i { font-size: 16px; }
        }

        /* ── MOBILE: slide-in cart panel ── */
        @media (max-width: 767px) {
            body { overflow: hidden; }

            .pos-header { padding: 0 12px; gap: 8px; height: 50px; }
            .pos-header .brand { font-size: 13px; }
            .pos-header .sep,
            .pos-header .title,
            .pos-header .clock-chip,
            .pos-header .cashier-info { display: none; }

            .pos-body { grid-template-columns: 1fr; }
            .pos-left  { border-right: none; }

            /* Cart slides in from the right */
            .pos-right {
                position: fixed;
                top: 50px; left: 0; right: 0; bottom: 0;
                z-index: 150;
                transform: translateX(100%);
                transition: transform 0.28s cubic-bezier(.4,0,.2,1);
                background: var(--surface);
            }
            .pos-right.mobile-open { transform: translateX(0); }

            /* Show mobile-only elements */
            .cart-back-btn { display: flex; align-items: center; }
            .fab-cart { display: flex; }

            /* Larger touch targets */
            .qty-btn   { width: 38px; height: 38px; font-size: 18px; border-radius: 8px; }
            .qty-val   { width: 34px; font-size: 15px; }
            .ci-remove { padding: 10px; font-size: 15px; }
            .cat-tab   { padding: 7px 16px; font-size: 13px; }
            .pay-method { padding: 11px 4px; }
            .pay-method i { font-size: 20px; margin-bottom: 4px; }
            .btn-checkout { padding: 15px; font-size: 16px; }
            .cart-customer select { padding: 10px 12px; font-size: 14px; }

            /* Product grid: 2 columns minimum */
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                padding: 10px;
                gap: 8px;
                /* leave room for the FAB */
                padding-bottom: 80px;
            }

            /* Modal slides up from bottom */
            .modal-overlay { align-items: flex-end; }
            .modal {
                width: 100%;
                border-radius: 16px 16px 0 0;
                padding: 20px 20px 36px;
                max-height: 92vh;
                overflow-y: auto;
            }
            .modal-actions { grid-template-columns: 1fr 1fr; }
        }

        /* Safe-area insets for notched phones */
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            @media (max-width: 767px) {
                .cart-payment { padding-bottom: calc(14px + env(safe-area-inset-bottom)); }
                .fab-cart { bottom: calc(20px + env(safe-area-inset-bottom)); }
            }
        }
    </style>
</head>
<body>

<div class="pos-header">
    <span class="brand">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</span>
    <span class="sep">/</span>
    <span class="title">Point of Sale</span>
    <span class="clock-chip" id="liveClock">--:--:--</span>
    <div class="spacer"></div>
    <span class="cashier-info">
        <i class="fas fa-user-circle"></i>
        {{ auth()->user()->name ?? 'Cashier' }}
    </span>
    <button class="theme-btn" onclick="toggleTheme()" id="themeToggle">
        <i class="fas fa-moon" id="themeIcon"></i>
        <span id="themeLabel">Dark</span>
    </button>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary" style="font-size:12px;padding:5px 12px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="pos-body">
    <div class="pos-left">
        <div class="pos-search">
            <input type="text" id="productSearch" placeholder="Search products, brand, or scan barcode…" autofocus>
        </div>
        <div class="category-tabs brand-tabs">
            <button class="cat-tab active" data-brand="">All</button>
            @foreach($brands as $brand)
                <button class="cat-tab" data-brand="{{ $brand->id }}">{{ $brand->name }}</button>
            @endforeach
            @if($productSets->isNotEmpty())
            <button class="cat-tab" data-brand="__sets__" style="border-color:rgba(240,180,41,.35);color:var(--accent);">
                <i class="fas fa-layer-group" style="margin-right:3px;"></i>Sets
                <span style="background:var(--accent);color:#000;font-size:9px;font-weight:700;padding:0 5px;border-radius:8px;margin-left:3px;">{{ $productSets->count() }}</span>
            </button>
            @endif
        </div>
        <div class="product-grid" id="productGrid">
            @forelse($products as $product)
            @php
                $activeUnits = $product->productUnits->where('is_active', true)->values();
                $pcsUnit     = $activeUnits->firstWhere('unit_type', 'piece');
                $pcsPrice    = $pcsUnit ? (float)$pcsUnit->selling_price : (float)$product->selling_price;
            @endphp
            <div class="product-card {{ $product->stock_quantity <= 0 ? 'out-of-stock' : '' }}"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-brand-name="{{ $product->brand->name ?? $product->brand_name ?? '' }}"
                 data-brand="{{ $product->brand_id }}"
                 data-stock="{{ $product->stock_quantity }}"
                 data-tax="{{ $product->tax->rate ?? 0 }}"
                 data-units="{{ $activeUnits->map(fn($u) => ['type'=>$u->unit_type,'label'=>$u->label,'uom'=>$u->uom,'price'=>(float)$u->selling_price])->toJson() }}">

                @if($product->image)
                    <img class="p-img"
                         src="{{ asset('storage/' . $product->image) }}"
                         alt="{{ $product->name }}"
                         loading="lazy">
                @else
                    <div class="p-img-placeholder">
                        <i class="fas fa-box"></i>
                    </div>
                @endif

                <div class="p-info">
                    <div class="p-name">{{ $product->name }}</div>
                    @if($product->brand || $product->brand_name)
                        <div class="p-stock" style="color:var(--muted);font-size:11px;">
                            {{ $product->brand->name ?? $product->brand_name }}
                        </div>
                    @endif
                    <div class="p-price">${{ number_format($pcsPrice, 2) }} / pcs</div>
                    <div class="p-stock">
                        {{ $product->stock_quantity <= 0 ? 'Out of stock' : $product->stock_quantity.' in stock' }}
                    </div>
                </div>

                {{-- Per-unit add buttons --}}
                <div class="p-unit-btns" onclick="event.stopPropagation()">
                    @foreach($activeUnits as $pu)
                    <button class="p-unit-btn" type="button"
                            onclick="addToCart(this.closest('.product-card'), '{{ $pu->unit_type }}')">
                        {{ $pu->label }} ${{ number_format($pu->selling_price, 2) }}
                    </button>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="no-products" style="grid-column:1/-1;">
                <i class="fas fa-box-open" style="font-size:28px;opacity:.3;"></i>
                No active products with stock available
            </div>
            @endforelse

            {{-- ── Product Sets ── --}}
            @if($productSets->isNotEmpty())
            <div class="sets-section" id="setsSection">
                <div class="sets-section-label">
                    <i class="fas fa-layer-group"></i> Product Sets
                    <span style="background:var(--surface2);border:1px solid var(--border);font-size:10px;padding:1px 7px;border-radius:8px;font-weight:500;">{{ $productSets->count() }}</span>
                </div>
                <div class="sets-grid">
                @foreach($productSets as $pset)
                @php
                    $setAvail = $pset->availableQty();
                    $setItems = $pset->items->map(function($i) {
                        return [
                            'product_id' => $i->product_id,
                            'unit_type'  => $i->unit_type,
                            'quantity'   => $i->quantity,
                            'pack_size'  => optional($i->product->productUnits->firstWhere('unit_type', $i->unit_type))->uom ?? 1,
                            'stock'      => (int) $i->product->stock_quantity,
                        ];
                    });
                @endphp
                <div class="product-card {{ $setAvail <= 0 ? 'out-of-stock' : '' }}"
                     style="border-color:rgba(240,180,41,.35);"
                     data-set-id="{{ $pset->id }}"
                     data-set-name="{{ $pset->name }}"
                     data-name="{{ $pset->name }}"
                     data-brand=""
                     data-set-price="{{ $pset->selling_price }}"
                     data-set-avail="{{ $setAvail }}"
                     data-set-items="{{ $setItems->toJson() }}">

                    <div class="p-img-wrap">
                        @if($pset->image)
                            <img src="{{ asset('storage/'.$pset->image) }}" alt="{{ $pset->name }}" loading="lazy">
                        @else
                            <div class="p-img-placeholder" style="color:var(--accent);">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        @endif
                        <span class="p-stock-badge {{ $setAvail <= 0 ? 'out' : ($setAvail <= 5 ? 'low' : 'ok') }}">
                            {{ $setAvail <= 0 ? 'Out' : $setAvail.' sets' }}
                        </span>
                        <span class="p-set-badge">SET</span>
                    </div>

                    <div class="p-info">
                        <div class="p-name">{{ $pset->name }}</div>
                        <div class="p-brand">
                            <i class="fas fa-box" style="font-size:9px;margin-right:2px;"></i>{{ $pset->items->count() }} items in set
                        </div>
                        <div class="p-price">${{ number_format($pset->selling_price, 2) }}</div>
                        <div class="p-price-sub">per set</div>
                    </div>

                    <div class="p-unit-btns" onclick="event.stopPropagation()">
                        <button class="p-unit-btn" type="button"
                                onclick="addSetToCart(this.closest('.product-card'))"
                                style="border-color:var(--accent);background:rgba(240,180,41,.1);color:var(--accent);">
                            <i class="fas fa-plus" style="font-size:9px;"></i> Add Set
                            <span class="btn-unit-label">${{ number_format($pset->selling_price, 2) }} / set</span>
                        </button>
                    </div>
                </div>
                @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="pos-right">
        <div class="cart-header">
            <button class="cart-back-btn" onclick="switchToProducts()" aria-label="Back to products">
                <i class="fas fa-arrow-left"></i>
            </button>
            <span class="cart-title">Cart</span>
            <span class="cart-count" id="cartCount">0</span>
        </div>
        <div class="cart-customer">
            <select id="customerSelect">
                <option value="">Walk-in Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart is empty</span>
            </div>
        </div>
        <div class="cart-totals">
            <div class="discount-row">
                <input type="text" id="discountCode" placeholder="Coupon code…">
                <button type="button" onclick="applyDiscount()"><i class="fas fa-tag"></i> Apply</button>
            </div>
            <div class="total-row"><span class="label">Subtotal</span><span class="value" id="subtotal">$0.00</span></div>
            <div class="total-row"><span class="label">Discount</span><span class="value" style="color:var(--danger);" id="discountDisplay">-$0.00</span></div>
            <div class="total-row"><span class="label">Tax</span><span class="value" id="taxDisplay">$0.00</span></div>
            <div class="total-row grand"><span>Total</span><span class="value" id="grandTotal">$0.00</span></div>
        </div>
        <div class="cart-payment">
            <div class="payment-methods">
                <div class="pay-method active" data-method="cash" onclick="selectPayment(this)"><i class="fas fa-money-bill-wave"></i>Cash</div>
                <div class="pay-method" data-method="card" onclick="selectPayment(this)"><i class="fas fa-credit-card"></i>Card</div>
                <div class="pay-method" data-method="mobile" onclick="selectPayment(this)"><i class="fas fa-mobile-alt"></i>Mobile</div>
                <div class="pay-method" data-method="credit" onclick="selectPayment(this)"><i class="fas fa-file-invoice-dollar"></i>Credit</div>
            </div>
            <div class="tendered-row">
                <label>Tendered ($)</label>
                <input type="number" id="tenderedAmount" placeholder="0.00" step="0.01" min="0" oninput="calcChange()">
            </div>
            <button class="btn-checkout" id="checkoutBtn" onclick="openModal()" disabled>
                <i class="fas fa-check-circle"></i> Charge
            </button>
        </div>
    </div>
</div>

<div id="toastContainer"></div>

<button class="fab-cart" id="fabCart" onclick="switchToCart()" aria-label="View cart">
    <i class="fas fa-shopping-cart"></i>
    View Cart
    <span class="fab-badge" id="fabBadge">0</span>
</button>

{{-- Per-item discount popup --}}
<div class="disc-modal-overlay" id="discModal">
    <div class="disc-modal">
        <div class="disc-modal-title" id="discModalName">Item Discount</div>
        <div class="disc-modal-sub" id="discModalSub"></div>
        <div class="disc-type-row">
            <button class="disc-type-btn active" id="discPctBtn" onclick="setDiscType('pct')">
                <i class="fas fa-percent"></i> Percentage
            </button>
            <button class="disc-type-btn" id="discAmtBtn" onclick="setDiscType('amt')">
                <i class="fas fa-dollar-sign"></i> Amount
            </button>
        </div>
        <div class="disc-val-row">
            <input type="number" id="discValInput" min="0" step="0.01" placeholder="0">
            <span class="disc-unit" id="discUnit">%</span>
        </div>
        <div class="disc-preview" id="discPreview"></div>
        <div class="disc-modal-actions">
            <button onclick="closeDiscModal()">Cancel</button>
            <button class="primary" onclick="applyDisc()"><i class="fas fa-check"></i> Apply</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="checkoutModal">
    <div class="modal">
        <h3><i class="fas fa-receipt" style="color:var(--accent);margin-right:8px;"></i>Confirm Sale</h3>
        <div class="modal-total" id="modalTotal">$0.00</div>
        <div class="modal-change-row" id="changeRow" style="display:none;">
            <div class="modal-change-label">Change Due</div>
            <div class="modal-change-val" id="modalChange">$0.00</div>
        </div>
        <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
            @csrf
            <input type="hidden" name="customer_id"    id="fCustomer">
            <input type="hidden" name="payment_method" id="fPayment">
            <input type="hidden" name="paid_amount"    id="fPaid">
            <input type="hidden" name="discount_id"    id="fDiscountId">
            <input type="hidden" name="notes"          id="fNotes">
            <div id="fItemsContainer"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Complete Sale</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────────
// cart keyed by:
//   regular item  → "productId_unitType"  e.g. "5_piece"
//   set item      → "set_setId"           e.g. "set_3"
let cart          = {};
let discountState = { id: null, amount: 0 };
let paymentMethod = 'cash';
let cartPanelOpen = false;
const DISCOUNTS   = @json($discounts);

// ── Toast ─────────────────────────────────────────────────────────
function showToast(title, detail, type) {
    type = type || 'danger';
    const icons = { danger: 'fa-times-circle', warning: 'fa-exclamation-triangle', success: 'fa-check-circle' };
    const icon  = icons[type] || 'fa-info-circle';
    const container = document.getElementById('toastContainer');
    const el = document.createElement('div');
    el.className = 'toast ' + type;
    el.innerHTML =
        '<div class="toast-icon"><i class="fas ' + icon + '"></i></div>' +
        '<div class="toast-body">' +
            '<div class="toast-title">' + title + '</div>' +
            (detail ? '<div class="toast-detail">' + detail + '</div>' : '') +
        '</div>';
    container.appendChild(el);
    setTimeout(function() {
        el.classList.add('fade-out');
        setTimeout(function() { el.remove(); }, 300);
    }, 3000);
}

// ── Helpers ───────────────────────────────────────────────────────
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmt(n) {
    return '$' + parseFloat(n || 0).toFixed(2);
}
function itemDiscount(item) {
    const lineGross = item.price * item.qty;
    if (!item.discountVal) return 0;
    return item.discountType === 'pct'
        ? Math.min(lineGross, lineGross * item.discountVal / 100)
        : Math.min(lineGross, item.discountVal);
}
function parsePackSize(packing) {
    if (!packing) return 1;
    const s = String(packing);
    // Prefer number followed by a count unit (PCS, CTN, CAN, PACK, CASE, BOX, BTL, PKT)
    const m = s.match(/(\d+)\s*(?:pcs?|ctns?|cans?|packs?|cases?|box(?:es)?|btls?|pkts?)/i);
    if (m) return Math.max(1, parseInt(m[1], 10));
    // Fallback: first number in string
    const fb = s.match(/(\d+)/);
    return fb ? Math.max(1, parseInt(fb[1], 10)) : 1;
}
// ── Stock helpers ─────────────────────────────────────────────────
// Raw piece stock from the product card attribute (set at page load)
function rawPieceStock(productId) {
    const card = document.querySelector('.product-card[data-id="' + productId + '"]');
    return parseInt(card ? card.dataset.stock : 0) || 0;
}

// Remaining piece stock = raw stock minus pieces already committed in the cart
// (PCS items count as 1 piece each; CASE items count as packSize pieces each)
function availablePieceStock(productId) {
    const raw  = rawPieceStock(productId);
    let   used = 0;
    Object.values(cart).forEach(function(i) {
        if (i.productId === productId) used += i.qty * (parseInt(i.packSize) || 1);
    });
    return Math.max(0, raw - used);
}

// Piece stock needed to add ONE unit of the given unit type
function piecesNeeded(unitType, packSize) {
    return unitType === 'carton' ? parseInt(packSize) : 1;
}

// Shared toast for stock errors
function stockLimitToast(name, unitType, packSize, rawStock) {
    if (rawStock <= 0) {
        showToast('Out of Stock', name + ' is currently unavailable.', 'danger');
    } else if (unitType === 'carton' && rawStock < parseInt(packSize)) {
        showToast(
            'Not Enough Stock for 1 Case',
            name + ' · Needs ' + packSize + ' pcs per case, but only ' + rawStock + ' pcs left in stock.',
            'danger'
        );
    } else if (unitType === 'carton') {
        const maxCases = Math.floor(rawStock / parseInt(packSize));
        showToast(
            'Stock Limit Reached',
            name + ' · Max ' + maxCases + ' case(s)  =  ' + rawStock + ' pcs total in stock.',
            'warning'
        );
    } else {
        showToast(
            'Stock Limit Reached',
            name + ' · Only ' + rawStock + ' pcs available in stock.',
            'warning'
        );
    }
}

// ── Cart operations ───────────────────────────────────────────────
function addToCart(card, unitType) {
    const productId = card.dataset.id;
    const units     = JSON.parse(card.dataset.units || '[]');
    const unit      = units.find(u => u.type === unitType);
    if (!unit) return;

    const name     = card.dataset.name || '';
    const packSize = parseInt(unit.uom) || 1;
    const cartKey  = productId + '_' + unitType;
    const avail    = availablePieceStock(productId); // remaining after current cart
    const needed   = piecesNeeded(unitType, packSize);

    if (avail < needed) {
        stockLimitToast(name, unitType, packSize, rawPieceStock(productId));
        return;
    }

    if (!cart[cartKey]) {
        cart[cartKey] = {
            cartKey,
            productId,
            name,
            price:        unit.price,
            unitType,
            packSize,
            taxRate:      parseFloat(card.dataset.tax) || 0,
            qty:          0,
            discountVal:  0,
            discountType: 'pct',
        };
    }

    cart[cartKey].qty++;
    renderCart();
}

function removeItem(cartKey) {
    delete cart[cartKey];
    renderCart();
}

// ── Product Set cart ──────────────────────────────────────────────
function addSetToCart(card) {
    const setId    = card.dataset.setId;
    const cartKey  = 'set_' + setId;
    const maxQty   = parseInt(card.dataset.setAvail) || 0;
    const name     = card.dataset.setName;

    if (maxQty <= 0) {
        showToast('Out of Stock', name + ' — no complete sets available.', 'danger');
        return;
    }

    if (cart[cartKey] && cart[cartKey].qty >= maxQty) {
        showToast('Stock Limit Reached', name + ' · Only ' + maxQty + ' sets available.', 'warning');
        return;
    }

    if (!cart[cartKey]) {
        cart[cartKey] = {
            cartKey,
            isSet:      true,
            setId,
            name,
            price:      parseFloat(card.dataset.setPrice) || 0,
            maxQty,
            qty:        0,
            setItems:   JSON.parse(card.dataset.setItems || '[]'),
            taxRate:    0,
            discountVal:  0,
            discountType: 'pct',
        };
    }
    cart[cartKey].qty++;
    renderCart();
}

function changeQty(cartKey, delta) {
    const item = cart[cartKey];
    if (!item) return;
    const newQty = item.qty + delta;
    if (newQty <= 0) {
        delete cart[cartKey];
        renderCart();
        return;
    }
    if (delta > 0) {
        if (item.isSet) {
            // Set stock check
            if (newQty > item.maxQty) {
                showToast('Stock Limit Reached', item.name + ' · Only ' + item.maxQty + ' sets available.', 'warning');
                return;
            }
        } else {
            const avail  = availablePieceStock(item.productId);
            const needed = piecesNeeded(item.unitType, item.packSize);
            if (avail < needed) {
                stockLimitToast(item.name, item.unitType, item.packSize, rawPieceStock(item.productId));
                return;
            }
        }
    }
    item.qty = newQty;
    renderCart();
}

// ── Render cart ───────────────────────────────────────────────────
function renderCart() {
    const items    = Object.values(cart);
    const totalQty = items.reduce((s, i) => s + i.qty, 0);

    document.getElementById('cartCount').textContent = totalQty;
    document.getElementById('fabBadge').textContent  = totalQty;
    document.getElementById('fabCart').style.display = (totalQty > 0 && !cartPanelOpen) ? 'flex' : 'none';

    const container = document.getElementById('cartItems');

    if (!items.length) {
        container.innerHTML = '<div class="cart-empty"><i class="fas fa-shopping-cart"></i><span>Cart is empty</span></div>';
        document.getElementById('checkoutBtn').disabled = true;
        recalc();
        return;
    }

    container.innerHTML = items.map(item => {
        const safeKey   = escHtml(item.cartKey);
        const lineGross = item.price * item.qty;
        const lineDisc  = itemDiscount(item);
        const lineNet   = lineGross - lineDisc;

        if (item.isSet) {
            // ── Set item row ──────────────────────────────────────────
            const componentList = (item.setItems || [])
                .map(si => si.quantity * item.qty + '× ' + (si.unit_type === 'carton' ? 'CASE' : 'PCS'))
                .join(', ');
            return `
            <div class="cart-item" data-key="${safeKey}" style="background:rgba(240,180,41,.04);">
                <div class="ci-main">
                    <div class="ci-name">
                        <div class="name" style="display:flex;align-items:center;gap:5px;">
                            <span style="background:var(--accent);color:#000;font-size:8px;font-weight:700;padding:1px 5px;border-radius:10px;">SET</span>
                            ${escHtml(item.name)}
                        </div>
                        <div class="price">${fmt(item.price)}/set &nbsp;·&nbsp; ${escHtml(componentList)}</div>
                    </div>
                    <div class="ci-qty">
                        <button class="qty-btn" data-action="dec" data-key="${safeKey}">−</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn" data-action="inc" data-key="${safeKey}">+</button>
                    </div>
                    <div class="ci-total">${fmt(lineNet)}</div>
                    <span style="width:26px;"></span>
                    <span class="ci-remove" data-action="remove" data-key="${safeKey}"><i class="fas fa-times"></i></span>
                </div>
            </div>`;
        }

        // ── Regular item row ──────────────────────────────────────────
        const unitLabel  = item.unitType === 'carton' ? 'CASE' : 'PCS';
        const discActive = item.discountVal > 0;
        const discLabel  = discActive
            ? (item.discountType === 'pct' ? item.discountVal + '%' : fmt(item.discountVal))
            : '';
        return `
        <div class="cart-item" data-key="${safeKey}">
            <div class="ci-main">
                <div class="ci-name">
                    <div class="name">${escHtml(item.name)}</div>
                    <div class="price">${fmt(item.price)}/${unitLabel}${discActive ? `<span class="ci-disc-badge">&nbsp;−${discLabel}</span>` : ''}</div>
                </div>
                <div class="ci-qty">
                    <button class="qty-btn" data-action="dec" data-key="${safeKey}">−</button>
                    <span class="qty-val">${item.qty}</span>
                    <button class="qty-btn" data-action="inc" data-key="${safeKey}">+</button>
                </div>
                <div class="ci-total">${fmt(lineNet)}</div>
                <button class="ci-disc-btn ${discActive ? 'active' : ''}" data-action="disc" data-key="${safeKey}" title="Set item discount">
                    <i class="fas fa-tag"></i>
                </button>
                <span class="ci-remove" data-action="remove" data-key="${safeKey}"><i class="fas fa-times"></i></span>
            </div>
        </div>`;
    }).join('');

    document.getElementById('checkoutBtn').disabled = false;
    recalc();
}

// Event delegation — +/−/remove/disc buttons inside #cartItems
document.getElementById('cartItems').addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const key    = btn.dataset.key;
    const action = btn.dataset.action;
    if      (action === 'dec')    changeQty(key, -1);
    else if (action === 'inc')    changeQty(key,  1);
    else if (action === 'remove') removeItem(key);
    else if (action === 'disc')   openDiscModal(key);
});

// ── Item discount modal ───────────────────────────────────────────
let currentDiscKey = null;

function openDiscModal(key) {
    const item = cart[key];
    if (!item) return;
    currentDiscKey = key;
    document.getElementById('discModalName').textContent = item.name;
    document.getElementById('discModalSub').textContent  =
        fmt(item.price) + ' / ' + (item.unitType === 'carton' ? 'CASE' : 'PCS') +
        '  ×  ' + item.qty + '  =  ' + fmt(item.price * item.qty);
    setDiscType(item.discountType || 'pct');
    document.getElementById('discValInput').value = item.discountVal || '';
    updateDiscPreview();
    document.getElementById('discModal').classList.add('open');
    setTimeout(() => {
        const inp = document.getElementById('discValInput');
        inp.focus(); inp.select();
    }, 80);
}

function closeDiscModal() {
    document.getElementById('discModal').classList.remove('open');
    currentDiscKey = null;
}

function setDiscType(type) {
    document.getElementById('discPctBtn').classList.toggle('active', type === 'pct');
    document.getElementById('discAmtBtn').classList.toggle('active', type === 'amt');
    document.getElementById('discUnit').textContent = type === 'pct' ? '%' : '$';
    if (currentDiscKey && cart[currentDiscKey]) {
        cart[currentDiscKey].discountType = type;
    }
    updateDiscPreview();
}

function updateDiscPreview() {
    const key = currentDiscKey;
    if (!key || !cart[key]) return;
    const item     = cart[key];
    const val      = parseFloat(document.getElementById('discValInput').value) || 0;
    const gross    = item.price * item.qty;
    const disc     = item.discountType === 'pct'
        ? Math.min(gross, gross * val / 100)
        : Math.min(gross, val);
    const preview  = document.getElementById('discPreview');
    if (disc > 0) {
        preview.textContent = '−' + fmt(disc) + '  →  Net ' + fmt(gross - disc);
        preview.style.color = 'var(--danger)';
    } else {
        preview.textContent = 'No discount';
        preview.style.color = 'var(--muted)';
    }
}

function applyDisc() {
    const key = currentDiscKey;
    if (!key || !cart[key]) return;
    const val = Math.max(0, parseFloat(document.getElementById('discValInput').value) || 0);
    cart[key].discountVal = val;
    closeDiscModal();
    renderCart();
}

// Close discount modal on overlay click
document.getElementById('discModal').addEventListener('click', function(e) {
    if (e.target === this) closeDiscModal();
});
// Preview updates as user types
document.getElementById('discValInput').addEventListener('input', updateDiscPreview);
// Enter key applies
document.getElementById('discValInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') applyDisc();
    if (e.key === 'Escape') closeDiscModal();
});

// ── Totals ─────────────────────────────────────────────────────────
function calcTotals() {
    const items         = Object.values(cart);
    const subtotal      = items.reduce((s, i) => s + i.price * i.qty, 0);
    const itemDiscTotal = items.reduce((s, i) => s + itemDiscount(i), 0);
    const globalDisc    = discountState.amount;
    const totalDisc     = itemDiscTotal + globalDisc;
    const taxAmt        = items.reduce((s, i) => {
        const lineNet = i.price * i.qty - itemDiscount(i);
        return s + Math.max(0, lineNet) * (i.taxRate / 100);
    }, 0);
    return {
        subtotal,
        itemDiscTotal,
        globalDisc,
        totalDisc,
        taxAmt,
        grandTotal: Math.max(0, subtotal - totalDisc + taxAmt),
    };
}

function recalc() {
    const { subtotal, totalDisc, taxAmt, grandTotal } = calcTotals();
    document.getElementById('subtotal').textContent        = fmt(subtotal);
    document.getElementById('discountDisplay').textContent = totalDisc > 0 ? '-' + fmt(totalDisc) : fmt(0);
    document.getElementById('taxDisplay').textContent      = fmt(taxAmt);
    document.getElementById('grandTotal').textContent      = fmt(grandTotal);
    calcChange();
}

function calcChange() {
    const total = parseFloat((document.getElementById('grandTotal').textContent || '').replace('$','')) || 0;
    const paid  = parseFloat(document.getElementById('tenderedAmount').value) || 0;
    document.getElementById('modalChange').textContent = fmt(Math.max(0, paid - total));
}

// ── Discount ───────────────────────────────────────────────────────
function applyDiscount() {
    const code = document.getElementById('discountCode').value.trim().toUpperCase();
    if (!code) return;
    const d = DISCOUNTS.find(x => x.code && x.code.toUpperCase() === code && x.is_active);
    if (!d) {
        alert('Invalid or expired coupon code.');
        discountState = { id: null, amount: 0 };
        recalc();
        return;
    }
    const { subtotal } = calcTotals();
    if (d.min_order && subtotal < parseFloat(d.min_order)) {
        alert('Minimum order ' + fmt(d.min_order) + ' required.');
        return;
    }
    const amt = d.type === 'percentage'
        ? subtotal * parseFloat(d.value) / 100
        : parseFloat(d.value);
    discountState = { id: d.id, amount: amt };
    alert('Discount applied: ' + d.name);
    recalc();
}

// ── Payment method ─────────────────────────────────────────────────
function selectPayment(el) {
    document.querySelectorAll('.pay-method').forEach(e => e.classList.remove('active'));
    el.classList.add('active');
    paymentMethod = el.dataset.method;
}

// ── Checkout modal ─────────────────────────────────────────────────
function openModal() {
    const items = Object.values(cart);
    if (!items.length) return;

    const { subtotal, grandTotal } = calcTotals();
    const paid = parseFloat(document.getElementById('tenderedAmount').value) || grandTotal;

    document.getElementById('modalTotal').textContent  = fmt(grandTotal);
    document.getElementById('modalChange').textContent = fmt(Math.max(0, paid - grandTotal));
    document.getElementById('changeRow').style.display = paymentMethod === 'cash' ? '' : 'none';

    document.getElementById('fCustomer').value   = document.getElementById('customerSelect').value;
    document.getElementById('fPayment').value    = paymentMethod;
    document.getElementById('fPaid').value       = paid.toFixed(2);
    document.getElementById('fDiscountId').value = discountState.id || '';

    const container  = document.getElementById('fItemsContainer');
    container.innerHTML = '';

    let globalAllocated = 0;

    items.forEach(function(item, idx) {
        const lineGross  = item.price * item.qty;
        const itemDisc   = itemDiscount(item);
        const lineNet    = lineGross - itemDisc;

        const weight         = subtotal > 0 ? lineGross / subtotal : 0;
        const globalLineDisc = idx === items.length - 1
            ? parseFloat((discountState.amount - globalAllocated).toFixed(2))
            : parseFloat((discountState.amount * weight).toFixed(2));
        globalAllocated += globalLineDisc;

        const lineDisc = parseFloat((itemDisc + globalLineDisc).toFixed(2));
        const lineTax  = Math.max(0, lineNet) * ((item.taxRate || 0) / 100);

        if (item.isSet) {
            // Set item — send product_set_id + price + qty; backend expands components
            [
                ['product_set_id',  item.setId],
                ['quantity',        item.qty],
                ['unit_price',      item.price.toFixed(2)],
                ['selling_unit',    'piece'],
                ['tax_amount',      lineTax.toFixed(2)],
                ['discount_amount', lineDisc.toFixed(2)],
                ['discount_type',   ''],
                ['discount_value',  0],
            ].forEach(function(pair) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'items[' + idx + '][' + pair[0] + ']';
                inp.value = pair[1];
                container.appendChild(inp);
            });
            return;
        }

        // Regular item
        [
            ['product_id',      item.productId],
            ['quantity',        item.qty],
            ['unit_price',      item.price.toFixed(2)],
            ['selling_unit',    item.unitType],
            ['tax_amount',      lineTax.toFixed(2)],
            ['discount_amount', lineDisc.toFixed(2)],
            ['discount_type',   item.discountVal > 0 ? item.discountType : ''],
            ['discount_value',  item.discountVal > 0 ? item.discountVal  : 0],
        ].forEach(function(pair) {
            const inp  = document.createElement('input');
            inp.type   = 'hidden';
            inp.name   = 'items[' + idx + '][' + pair[0] + ']';
            inp.value  = pair[1];
            container.appendChild(inp);
        });
    });

    document.getElementById('checkoutModal').classList.add('open');
}

function closeModal() {
    document.getElementById('checkoutModal').classList.remove('open');
    if (window.innerWidth < 768) switchToCart();
}

// ── Search & category filter ───────────────────────────────────────
const setsSection = document.getElementById('setsSection');

document.querySelectorAll('.cat-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const brand = this.dataset.brand;

        if (brand === '__sets__') {
            // Show ONLY set cards
            document.querySelectorAll('.product-card[data-id]').forEach(c => c.style.display = 'none');
            if (setsSection) setsSection.style.display = '';
        } else if (!brand) {
            // All tab — show everything
            document.querySelectorAll('.product-card[data-id]').forEach(c => c.style.display = '');
            if (setsSection) setsSection.style.display = '';
        } else {
            // Specific brand — show matching products only, hide sets section
            document.querySelectorAll('.product-card[data-id]').forEach(function(c) {
                c.style.display = (c.dataset.brand == brand) ? '' : 'none';
            });
            if (setsSection) setsSection.style.display = 'none';
        }
    });
});

document.getElementById('productSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    // Filter regular product cards
    document.querySelectorAll('.product-card[data-id]').forEach(function(c) {
        const hit = (c.dataset.name || '').toLowerCase().includes(q) ||
                    (c.dataset.brandName || '').toLowerCase().includes(q);
        c.style.display = hit ? '' : 'none';
    });
    // Filter set cards inside sets grid
    if (setsSection) {
        let anySet = false;
        setsSection.querySelectorAll('.product-card[data-set-id]').forEach(function(c) {
            const hit = (c.dataset.setName || c.dataset.name || '').toLowerCase().includes(q);
            c.style.display = hit ? '' : 'none';
            if (hit) anySet = true;
        });
        // Hide entire sets section if no sets match and there is a search query
        setsSection.style.display = (!q || anySet) ? '' : 'none';
    }
});

// ── Event listeners ────────────────────────────────────────────────
document.getElementById('tenderedAmount').addEventListener('input', calcChange);
document.getElementById('checkoutModal').addEventListener('click', function(e) {
    if (e.target === e.currentTarget) closeModal();
});

// ── Mobile cart panel ──────────────────────────────────────────────
function switchToCart() {
    cartPanelOpen = true;
    document.getElementById('fabCart').style.display = 'none';
    document.querySelector('.pos-right').classList.add('mobile-open');
}
function switchToProducts() {
    cartPanelOpen = false;
    document.querySelector('.pos-right').classList.remove('mobile-open');
    const qty = Object.values(cart).reduce((s, i) => s + i.qty, 0);
    document.getElementById('fabCart').style.display = qty > 0 ? 'flex' : 'none';
}

window.addEventListener('popstate', function(e) {
    if (cartPanelOpen) { e.preventDefault(); switchToProducts(); history.pushState(null, ''); }
});
history.pushState(null, '');

// ── Theme ──────────────────────────────────────────────────────────
function toggleTheme() {
    const cur = document.documentElement.getAttribute('data-theme') || 'dark';
    setTheme(cur === 'dark' ? 'light' : 'dark');
}
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const dark = theme === 'dark';
    document.getElementById('themeIcon').className    = dark ? 'fas fa-moon' : 'fas fa-sun';
    document.getElementById('themeLabel').textContent = dark ? 'Dark'        : 'Light';
}

// ── Live clock ─────────────────────────────────────────────────────
function updateClock() {
    const d = new Date();
    document.getElementById('liveClock').textContent =
        String(d.getHours()).padStart(2, '0') + ':' +
        String(d.getMinutes()).padStart(2, '0') + ':' +
        String(d.getSeconds()).padStart(2, '0');
}
setInterval(updateClock, 1000);
updateClock();

// ── Init ───────────────────────────────────────────────────────────
(function() {
    const t = localStorage.getItem('theme') || 'dark';
    document.getElementById('themeIcon').className    = t === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    document.getElementById('themeLabel').textContent = t === 'dark' ? 'Dark'        : 'Light';
    renderCart();
})();
</script>
</body>
</html>