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
        .no-products { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px; color: var(--muted); gap: 8px; font-size: 13px; }
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
        .cart-item { padding: 10px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
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
            .pos-header .title { display: none; }

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
    <div class="spacer"></div>
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
        </div>
        <div class="product-grid" id="productGrid">
            @forelse($products as $product)
            @php
                $cartonPrice = (float) $product->selling_price;
                if ($product->packing && preg_match('/(\d+)/', $product->packing, $packMatches)) {
                    $cartonPrice = $product->selling_price * (int) $packMatches[1];
                }
            @endphp
            <div class="product-card {{ $product->stock_quantity <= 0 ? 'out-of-stock' : '' }}"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-brand-name="{{ $product->brand->name ?? $product->brand_name ?? '' }}"
                 data-brand="{{ $product->brand_id }}"
                 data-price-piece="{{ $product->selling_price }}"
                 data-price-carton="{{ $cartonPrice }}"
                 data-pack="{{ $product->packing }}"
                 data-stock="{{ $product->stock_quantity }}"
                 data-tax="{{ $product->tax->rate ?? 0 }}"
                 onclick="addToCart(this)">

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
                    <div class="p-price">${{ number_format($product->selling_price, 2) }} each</div>
                    <div class="p-stock">
                        {{ $product->stock_quantity <= 0 ? 'Out of stock' : $product->stock_quantity.' in stock' }}
                    </div>
                </div>
            </div>
            @empty
            <div class="no-products" style="grid-column:1/-1;">
                <i class="fas fa-box-open" style="font-size:28px;opacity:.3;"></i>
                No active products with stock available
            </div>
            @endforelse
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
            <div style="margin-top:10px;">
                <select id="priceModeSelect">
                    <option value="piece">PCS</option>
                    <option value="carton">CASE</option>
                </select>
            </div>
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

<button class="fab-cart" id="fabCart" onclick="switchToCart()" aria-label="View cart">
    <i class="fas fa-shopping-cart"></i>
    View Cart
    <span class="fab-badge" id="fabBadge">0</span>
</button>

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
let cart            = {};
let discountState   = { id: null, amount: 0, code: '' };
let paymentMethod   = 'cash';
let cartPanelOpen   = false;
let saleUnitType    = 'piece';
const DISCOUNTS     = @json($discounts);

function getSelectedPriceMode() {
    return document.getElementById('priceModeSelect').value;
}

function parsePackSize(packing) {
    if (!packing) return 1;
    const match = packing.match(/(\d+)/);
    return match ? parseInt(match[1], 10) : 1;
}

function syncPriceMode() {
    const customerId = document.getElementById('customerSelect').value;
    const modeInput = document.getElementById('priceModeSelect');
    if (!customerId) {
        modeInput.value = 'piece';
        modeInput.querySelector('option[value="carton"]').disabled = true;
    } else {
        modeInput.querySelector('option[value="carton"]').disabled = false;
    }
    saleUnitType = modeInput.value;
    updateProductCardPrices();
    updateCartPrices();
}

function updateCartPrices() {
    Object.values(cart).forEach(item => {
        item.price = saleUnitType === 'carton' ? item.priceCarton : item.pricePiece;
        item.unitType = saleUnitType;
    });
    renderCart();
}

function addToCart(el) {
    const id    = el.dataset.id;
    const stock = parseInt(el.dataset.stock);
    const pricePiece = parseFloat(el.dataset.pricePiece) || 0;
    let priceCarton = parseFloat(el.dataset.priceCarton);
    const packSize = parsePackSize(el.dataset.pack);
    if (Number.isNaN(priceCarton)) {
        priceCarton = pricePiece * packSize;
    }
    const unitType = getSelectedPriceMode();

    if (!cart[id]) {
        cart[id] = {
            id,
            name: el.dataset.name,
            price: unitType === 'carton' ? priceCarton : pricePiece,
            pricePiece,
            priceCarton,
            packSize,
            unitType,
            taxRate: parseFloat(el.dataset.tax) || 0,
            qty: 0,
            stock,
        };
    }
    const nextQty = cart[id].qty + 1;
    const requiredStock = cart[id].unitType === 'carton' ? nextQty * cart[id].packSize : nextQty;
    if (requiredStock > stock) { flashCard(el); return; }
    cart[id].qty = nextQty;
    renderCart();
}

function removeFromCart(id) { delete cart[id]; renderCart(); }
function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    else if (cart[id].qty > cart[id].stock) cart[id].qty = cart[id].stock;
    renderCart();
}
function flashCard(el) { el.style.borderColor = 'var(--danger)'; setTimeout(() => el.style.borderColor = '', 600); }

function renderCart() {
    const container = document.getElementById('cartItems');
    const items = Object.values(cart);
    const totalQty = items.reduce((s, i) => s + i.qty, 0);
    document.getElementById('cartCount').textContent = totalQty;

    // FAB: show when cart has items and cart panel is not open (mobile)
    const fab = document.getElementById('fabCart');
    document.getElementById('fabBadge').textContent = totalQty;
    fab.style.display = (totalQty > 0 && !cartPanelOpen) ? 'flex' : 'none';

    if (!items.length) {
        container.innerHTML = '<div class="cart-empty"><i class="fas fa-shopping-cart"></i><span>Cart is empty</span></div>';
        document.getElementById('checkoutBtn').disabled = true;
        updateTotals(); return;
    }
    container.innerHTML = items.map(i => {
        const unitLabel = i.unitType === 'carton' ? '/CASE' : '/PCS';
        return `
        <div class="cart-item">
            <div class="ci-name"><div class="name">${escHtml(i.name)}</div><div class="price">$${i.price.toFixed(2)} ${unitLabel}</div></div>
            <div class="ci-qty">
                <button class="qty-btn" onclick="changeQty('${i.id}',-1)">−</button>
                <span class="qty-val">${i.qty}</span>
                <button class="qty-btn" onclick="changeQty('${i.id}',1)">+</button>
            </div>
            <div class="ci-total">$${(i.price*i.qty).toFixed(2)}</div>
            <span class="ci-remove" onclick="removeFromCart('${i.id}')"><i class="fas fa-times"></i></span>
        </div>`;
    }).join('');
    document.getElementById('checkoutBtn').disabled = false;
    updateTotals();
}

function switchToCart() {
    cartPanelOpen = true;
    document.getElementById('fabCart').style.display = 'none';
    document.querySelector('.pos-right').classList.add('mobile-open');
}

function switchToProducts() {
    cartPanelOpen = false;
    document.querySelector('.pos-right').classList.remove('mobile-open');
    const totalQty = Object.values(cart).reduce((s, i) => s + i.qty, 0);
    const fab = document.getElementById('fabCart');
    fab.style.display = totalQty > 0 ? 'flex' : 'none';
}

function calcTotals() {
    const items = Object.values(cart);
    const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
    const taxBase  = Math.max(0, subtotal - discountState.amount);
    const taxAmount = items.reduce((s, i) => {
        const w = (i.price * i.qty) / (subtotal || 1);
        return s + taxBase * w * (i.taxRate / 100);
    }, 0);
    return { subtotal, taxAmount, grandTotal: Math.max(0, subtotal - discountState.amount + taxAmount) };
}

function updateTotals() {
    const { subtotal, taxAmount, grandTotal } = calcTotals();
    document.getElementById('subtotal').textContent        = '$' + subtotal.toFixed(2);
    document.getElementById('discountDisplay').textContent = '-$' + discountState.amount.toFixed(2);
    document.getElementById('taxDisplay').textContent      = '$' + taxAmount.toFixed(2);
    document.getElementById('grandTotal').textContent      = '$' + grandTotal.toFixed(2);
    calcChange();
}

function calcChange() {
    const total = parseFloat(document.getElementById('grandTotal').textContent.replace('$','')) || 0;
    const paid  = parseFloat(document.getElementById('tenderedAmount').value) || 0;
    document.getElementById('modalChange').textContent = '$' + Math.max(0, paid - total).toFixed(2);
}

function applyDiscount() {
    const code = document.getElementById('discountCode').value.trim().toUpperCase();
    if (!code) return;
    const found = DISCOUNTS.find(d => d.code && d.code.toUpperCase() === code && d.is_active);
    if (!found) { alert('Invalid or expired coupon code.'); discountState = { id: null, amount: 0, code: '' }; updateTotals(); return; }
    const { subtotal } = calcTotals();
    if (found.min_order && subtotal < parseFloat(found.min_order)) { alert(`Minimum order $${parseFloat(found.min_order).toFixed(2)} required.`); return; }
    discountState = { id: found.id, code, amount: found.type === 'percentage' ? subtotal * parseFloat(found.value) / 100 : parseFloat(found.value) };
    alert('✓ Discount applied: ' + found.name);
    updateTotals();
}

function selectPayment(el) {
    document.querySelectorAll('.pay-method').forEach(e => e.classList.remove('active'));
    el.classList.add('active');
    paymentMethod = el.dataset.method;
}

function openModal() {
    if (!Object.keys(cart).length) return;
    const { subtotal, grandTotal } = calcTotals();
    const paid = parseFloat(document.getElementById('tenderedAmount').value) || grandTotal;
    document.getElementById('modalTotal').textContent  = '$' + grandTotal.toFixed(2);
    document.getElementById('modalChange').textContent = '$' + Math.max(0, paid - grandTotal).toFixed(2);
    document.getElementById('changeRow').style.display = paymentMethod === 'cash' ? '' : 'none';
    document.getElementById('fCustomer').value   = document.getElementById('customerSelect').value;
    document.getElementById('fPayment').value    = paymentMethod;
    document.getElementById('fPaid').value       = paid.toFixed(2);
    document.getElementById('fDiscountId').value = discountState.id || '';

    const c = document.getElementById('fItemsContainer');
    c.innerHTML = '';

    const items      = Object.values(cart);
    const totalDisc  = discountState.amount;
    let   allocated  = 0;

    items.forEach((item, idx) => {
        const lineGross = item.price * item.qty;
        const weight    = subtotal > 0 ? lineGross / subtotal : 0;
        // Last item absorbs any cent-level rounding residual
        const lineDisc  = idx === items.length - 1
            ? parseFloat((totalDisc - allocated).toFixed(2))
            : parseFloat((totalDisc * weight).toFixed(2));
        allocated += lineDisc;

        const lineTax = lineGross * (item.taxRate / 100);
        [
            ['product_id',      item.id],
            ['quantity',        item.qty],
            ['unit_price',      item.price.toFixed(2)],
            ['selling_unit',    item.unitType],
            ['tax_amount',      lineTax.toFixed(2)],
            ['discount_amount', lineDisc.toFixed(2)],
        ].forEach(([k, v]) => addHidden(c, `items[${idx}][${k}]`, v));
    });

    document.getElementById('checkoutModal').classList.add('open');
}

function closeModal() {
    document.getElementById('checkoutModal').classList.remove('open');
    // On mobile, return to cart view after closing modal
    if (window.innerWidth < 768) switchToCart();
}
function addHidden(c, name, value) { const i = document.createElement('input'); i.type='hidden'; i.name=name; i.value=value; c.appendChild(i); }
function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function updateProductCardPrices() {
    const mode = getSelectedPriceMode();
    document.querySelectorAll('.product-card').forEach(card => {
        const price = mode === 'carton'
            ? parseFloat(card.dataset.priceCarton)
            : parseFloat(card.dataset.pricePiece);
        const label = mode === 'carton' ? '/ctn' : 'each';
        const priceEl = card.querySelector('.p-price');
        if (priceEl) {
            priceEl.textContent = '$' + price.toFixed(2) + ' ' + label;
        }
    });
}

document.querySelectorAll('.cat-tab').forEach(tab => tab.addEventListener('click', function() {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    const brand = this.dataset.brand;
    document.querySelectorAll('.product-card').forEach(c => c.style.display = (!brand || c.dataset.brand == brand) ? '' : 'none');
}));

document.getElementById('productSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => {
        const matchesName = c.dataset.name.toLowerCase().includes(q);
        const matchesBrand = (c.dataset.brandName || '').toLowerCase().includes(q);
        c.style.display = (matchesName || matchesBrand) ? '' : 'none';
    });
});

document.getElementById('customerSelect').addEventListener('change', function() {
    syncPriceMode();
});

document.getElementById('priceModeSelect').addEventListener('change', function() {
    const newMode = getSelectedPriceMode();
    if (Object.keys(cart).length && newMode !== saleUnitType) {
        if (!confirm('Changing sale unit will clear the current cart. Continue?')) {
            this.value = saleUnitType;
            return;
        }
        cart = {};
        renderCart();
    }
    saleUnitType = newMode;
    updateCartPrices();
    updateProductCardPrices();
});

syncPriceMode();
updateProductCardPrices();

document.getElementById('checkoutModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });

// Handle Android/browser back-button when cart is open on mobile
window.addEventListener('popstate', function(e) {
    if (cartPanelOpen) { e.preventDefault(); switchToProducts(); history.pushState(null, ''); }
});
history.pushState(null, '');

function toggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') || 'dark';
    setTheme(current === 'dark' ? 'light' : 'dark');
}
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    var isDark = theme === 'dark';
    document.getElementById('themeIcon').className  = isDark ? 'fas fa-moon' : 'fas fa-sun';
    document.getElementById('themeLabel').textContent = isDark ? 'Dark' : 'Light';
}
// Sync icon to current theme on load
(function() {
    var t = localStorage.getItem('theme') || 'dark';
    document.getElementById('themeIcon').className   = t === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    document.getElementById('themeLabel').textContent = t === 'dark' ? 'Dark' : 'Light';
})();
</script>
</body>
</html>