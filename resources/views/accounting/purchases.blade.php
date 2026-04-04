<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Record Purchase Receipt</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/addItem.css" />
    <style>
        .receipt-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
            margin-bottom: 24px;
        }

        @media (max-width: 860px) {
            .receipt-header {
                grid-template-columns: 1fr;
            }
        }

        /* ── Payment method toggle ─────────────────── */
        .pay-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }

        .pay-toggle__btn {
            padding: 9px 20px;
            border-radius: 999px;
            border: 2px solid rgba(0, 0, 0, 0.12);
            background: rgba(0, 0, 0, 0.03);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            color: rgba(0, 0, 0, 0.55);
        }

        .pay-toggle__btn--active {
            background: rgba(42, 127, 176, 0.1);
            border-color: rgba(42, 127, 176, 0.4);
            color: rgba(42, 127, 176, 0.95);
        }

        /* ── Items table ───────────────────────────── */
        .receipt-table-wrap {
            overflow-x: auto;
            border-radius: 14px;
            border: 2px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 12px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .receipt-table thead {
            background: rgba(0, 0, 0, 0.04);
        }

        .receipt-table th {
            padding: 10px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: rgba(0, 0, 0, 0.5);
            letter-spacing: .04em;
            white-space: nowrap;
            border-bottom: 2px solid rgba(0, 0, 0, 0.07);
        }

        .receipt-table td {
            padding: 8px 10px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            vertical-align: middle;
        }

        .receipt-table tbody tr:last-child td {
            border-bottom: none;
        }

        .receipt-table tbody tr:hover {
            background: rgba(42, 127, 176, 0.03);
        }

        .rt-select,
        .rt-input {
            height: 38px;
            padding: 0 10px;
            border-radius: 10px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.75);
            font-size: 13px;
            color: rgba(0, 0, 0, 0.75);
            outline: none;
            width: 100%;
            box-sizing: border-box;
        }

        .rt-select:focus,
        .rt-input:focus {
            border-color: rgba(42, 127, 176, 0.35);
        }

        .rt-input--narrow {
            width: 90px;
        }

        .rt-input--cost {
            width: 115px;
        }

        .rt-line-total {
            font-weight: 600;
            color: rgba(0, 0, 0, 0.65);
            white-space: nowrap;
            min-width: 90px;
        }

        .rt-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 2px solid rgba(220, 50, 50, 0.2);
            background: rgba(220, 50, 50, 0.07);
            color: rgba(200, 40, 40, 0.75);
            cursor: pointer;
            font-size: 16px;
            transition: background .15s;
        }

        .rt-remove:hover {
            background: rgba(220, 50, 50, 0.15);
        }

        .rt-remove:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .rt-stock {
            font-size: 11px;
            color: rgba(0, 0, 0, 0.4);
            margin-top: 3px;
        }

        .rt-stock--low {
            color: rgba(220, 80, 40, 0.85);
        }

        .receipt-add-row {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 999px;
            border: 2px dashed rgba(42, 127, 176, 0.35);
            background: rgba(42, 127, 176, 0.05);
            color: rgba(42, 127, 176, 0.85);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background .15s;
        }

        .receipt-add-row:hover {
            background: rgba(42, 127, 176, 0.1);
        }

        .receipt-total-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 14px;
            background: rgba(42, 127, 176, 0.07);
            border: 2px solid rgba(42, 127, 176, 0.14);
            margin-bottom: 20px;
        }

        .receipt-total-label {
            font-size: 14px;
            color: rgba(0, 0, 0, 0.5);
            font-weight: 600;
        }

        .receipt-total-amount {
            font-size: 22px;
            font-weight: 700;
            color: rgba(42, 127, 176, 0.9);
            min-width: 120px;
            text-align: right;
        }

        /* In-kind payment section */
        .inkind-section {
            margin-top: 4px;
            padding: 20px 0 4px;
            border-top: 2px dashed rgba(42, 127, 176, 0.2);
        }

        .inkind-section__title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(42, 127, 176, 0.8);
            margin-bottom: 14px;
        }

        .inkind-total-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: 12px 18px;
            border-radius: 14px;
            background: rgba(5, 150, 105, 0.07);
            border: 2px solid rgba(5, 150, 105, 0.18);
            margin-bottom: 20px;
        }

        .inkind-total-label {
            font-size: 14px;
            color: rgba(0, 0, 0, 0.5);
            font-weight: 600;
        }

        .inkind-total-amount {
            font-size: 20px;
            font-weight: 700;
            color: rgba(5, 150, 105, 0.9);
            min-width: 120px;
            text-align: right;
        }

        .rt-error {
            font-size: 11px;
            color: rgba(200, 40, 40, 0.85);
            margin-top: 2px;
        }

        .row-num {
            color: rgba(0, 0, 0, 0.35);
            font-weight: 600;
            font-size: 13px;
        }
    </style>
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">

            @if (session('success'))
            <div class="alert alert--success" data-alert>
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">✕</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">✕</span>
                <span class="alert__text">Please fix the errors below and try again.</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            <section class="add-item" aria-label="Record purchase receipt">
                <section class="dashboard-card add-item__card">

                    <header class="add-item__header">
                        <h2 class="add-item__title">Record Purchase Receipt</h2>
                        <a href="{{ route('accounting.overview') }}" class="add-item__back">← Back</a>
                    </header>

                    <form id="receiptForm" action="{{ route('accounting.purchases.store') }}" method="post" novalidate>
                        @csrf

                        {{-- Receipt-level header fields --}}
                        <div class="receipt-header">
                            <div class="add-item__field">
                                <label class="add-item__label" for="purchase_date">Purchase Date</label>
                                <input class="add-item__input" id="purchase_date" name="purchase_date" type="date"
                                    value="{{ old('purchase_date', now()->toDateString()) }}" required />
                                @error('purchase_date') <p class="rt-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="add-item__field">
                                <label class="add-item__label" for="vendor_name">Vendor (optional)</label>
                                <input class="add-item__input" id="vendor_name" name="vendor_name" type="text"
                                    placeholder="Supplier name" value="{{ old('vendor_name') }}" />
                                @error('vendor_name') <p class="rt-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="add-item__field">
                                <label class="add-item__label" for="notes">Notes (optional)</label>
                                <input class="add-item__input" id="notes" name="notes" type="text"
                                    placeholder="Invoice #, delivery note…" value="{{ old('notes') }}" />
                                @error('notes') <p class="rt-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Payment method toggle --}}
                        <div class="pay-toggle">
                            <button type="button" class="pay-toggle__btn pay-toggle__btn--active" id="btnCash">
                                💵 Cash Payment
                            </button>
                            <button type="button" class="pay-toggle__btn" id="btnInKind">
                                📦 In-Kind (Pay with inventory)
                            </button>
                        </div>
                        <input type="hidden" name="payment_method" id="paymentMethodInput" value="cash">

                        {{-- Items received from supplier --}}
                        <div class="receipt-table-wrap">
                            <table class="receipt-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th style="min-width:220px">Item received</th>
                                        <th>Qty</th>
                                        <th>Unit Cost ($)</th>
                                        <th>Line Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>

                        <button type="button" class="receipt-add-row" id="addRowBtn">
                            <span>＋</span> Add item
                        </button>

                        <div class="receipt-total-bar">
                            <span class="receipt-total-label">Receipt Total</span>
                            <span class="receipt-total-amount" id="grandTotal">$0.00</span>
                        </div>

                        @error('items') <p class="rt-error" style="margin-bottom:12px">{{ $message }}</p> @enderror

                        {{-- In-kind payment section (hidden unless in-kind is selected) --}}
                        <div class="inkind-section" id="inkindSection" style="display:none;">
                            <div class="inkind-section__title">📦 Items you are giving to the supplier as payment</div>

                            <div class="receipt-table-wrap">
                                <table class="receipt-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th style="min-width:220px">Item (from your stock)</th>
                                            <th>Qty to give</th>
                                            <th>Unit Value ($)</th>
                                            <th>Line Value</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="inkindBody"></tbody>
                                </table>
                            </div>

                            <button type="button" class="receipt-add-row" id="addInkindRowBtn">
                                <span>＋</span> Add payment item
                            </button>

                            <div class="inkind-total-bar">
                                <span class="inkind-total-label">In-Kind Payment Value</span>
                                <span class="inkind-total-amount" id="inkindGrandTotal">$0.00</span>
                            </div>

                            @error('inkind_items') <p class="rt-error" style="margin-bottom:12px">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="add-item__actions">
                            <button class="add-item__btn add-item__btn--primary" type="submit">Save Receipt</button>
                            <a class="add-item__btn add-item__btn--ghost"
                                href="{{ route('accounting.overview') }}">Cancel</a>
                        </div>
                    </form>

                </section>
            </section>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="/js/navSearch.js"></script>
    <script>
        (() => {
        const ITEMS    = {!! $itemsJson !!};
        const OLD_ROWS = @json(old('items', []));

        let rowIndex      = 0;
        let inkindIndex   = 0;
        let paymentMethod = 'cash';

        const tbody          = document.getElementById('itemsBody');
        const inkindBody     = document.getElementById('inkindBody');
        const grandTotalEl   = document.getElementById('grandTotal');
        const inkindTotalEl  = document.getElementById('inkindGrandTotal');
        const inkindSection  = document.getElementById('inkindSection');
        const btnCash        = document.getElementById('btnCash');
        const btnInKind      = document.getElementById('btnInKind');
        const pmInput        = document.getElementById('paymentMethodInput');

        // ── Payment method toggle ──────────────────────────────────────
        btnCash.addEventListener('click', () => setPaymentMethod('cash'));
        btnInKind.addEventListener('click', () => setPaymentMethod('in_kind'));

        function setPaymentMethod(method) {
            paymentMethod = method;
            pmInput.value = method;
            if (method === 'cash') {
                btnCash.classList.add('pay-toggle__btn--active');
                btnInKind.classList.remove('pay-toggle__btn--active');
                inkindSection.style.display = 'none';
                // Remove required from inkind inputs
                inkindBody.querySelectorAll('input, select').forEach(el => el.removeAttribute('required'));
            } else {
                btnInKind.classList.add('pay-toggle__btn--active');
                btnCash.classList.remove('pay-toggle__btn--active');
                inkindSection.style.display = '';
                // Ensure at least one row exists
                if (inkindBody.querySelectorAll('tr').length === 0) addInkindRow();
                // Restore required on inkind inputs
                inkindBody.querySelectorAll('select, input[data-required]').forEach(el => el.setAttribute('required', ''));
            }
        }

        // ── Build item <select> options ────────────────────────────────
        function buildOptions(selectedId) {
            let html = `<option value="" disabled ${!selectedId ? 'selected' : ''}>Select item</option>`;
            ITEMS.forEach(it => {
                const sel = String(it.id) === String(selectedId) ? 'selected' : '';
                html += `<option value="${it.id}" data-price="${it.price}" data-qty="${it.qty}" data-unit="${it.unit ?? ''}" ${sel}>${it.name} (Stock: ${it.qty} ${it.unit ?? ''})</option>`;
            });
            return html;
        }

        // ── Purchased items (what you receive from supplier) ───────────
        function addRow(data) {
            data = data || {};
            const idx      = rowIndex++;
            const qty      = data.qty      || 1;
            const unitCost = data.unit_cost || '';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="row-num"></td>
                <td>
                    <select class="rt-select" name="items[${idx}][inventory_item_id]" required>
                        ${buildOptions(data.inventory_item_id || '')}
                    </select>
                </td>
                <td>
                    <input class="rt-input rt-input--narrow" type="number" step="1" min="1"
                        name="items[${idx}][qty]" value="${qty}" placeholder="1" required />
                </td>
                <td>
                    <input class="rt-input rt-input--cost" type="number" step="0.01" min="0"
                        name="items[${idx}][unit_cost]" value="${unitCost}" placeholder="0.00" required />
                </td>
                <td class="rt-line-total">$0.00</td>
                <td><button type="button" class="rt-remove" title="Remove">✕</button></td>
            `;

            const qtyInput  = tr.querySelector('input[name*="[qty]"]');
            const costInput = tr.querySelector('input[name*="[unit_cost]"]');
            const lineCell  = tr.querySelector('.rt-line-total');

            function recalcLine() {
                const q = parseFloat(qtyInput.value) || 0;
                const c = parseFloat(costInput.value) || 0;
                lineCell.textContent = '$' + (q * c).toFixed(2);
                recalcGrand();
            }
            qtyInput.addEventListener('input', recalcLine);
            costInput.addEventListener('input', recalcLine);

            tr.querySelector('.rt-remove').addEventListener('click', () => {
                tr.remove(); reindexBody(tbody); recalcGrand();
            });

            tbody.appendChild(tr);
            reindexBody(tbody);
            recalcLine();
        }

        // ── In-kind payment items (what you give to supplier) ──────────
        function addInkindRow(data) {
            data = data || {};
            const idx = inkindIndex++;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="row-num"></td>
                <td>
                    <select class="rt-select" name="inkind_items[${idx}][inventory_item_id]" ${paymentMethod === 'in_kind' ? 'required' : ''}>
                        ${buildOptions(data.inventory_item_id || '')}
                    </select>
                    <div class="rt-stock" id="ik-stock-${idx}"></div>
                </td>
                <td>
                    <input class="rt-input rt-input--narrow" type="number" step="0.001" min="0.001"
                        name="inkind_items[${idx}][quantity]" value="${data.quantity || ''}"
                        placeholder="0.00" ${paymentMethod === 'in_kind' ? 'required' : ''} data-required />
                </td>
                <td>
                    <input class="rt-input rt-input--cost" type="number" step="0.01" min="0"
                        name="inkind_items[${idx}][unit_value]" value="${data.unit_value || ''}"
                        placeholder="0.00" readonly style="background:rgba(0,0,0,0.04); cursor:default;" />
                </td>
                <td class="rt-line-total">$0.00</td>
                <td><button type="button" class="rt-remove" title="Remove">✕</button></td>
            `;

            const sel       = tr.querySelector('select');
            const qtyInput  = tr.querySelector('input[name*="[quantity]"]');
            const valInput  = tr.querySelector('input[name*="[unit_value]"]');
            const lineCell  = tr.querySelector('.rt-line-total');
            const stockDiv  = tr.querySelector('.rt-stock');

            function recalcInkindLine() {
                const q = parseFloat(qtyInput.value) || 0;
                const v = parseFloat(valInput.value) || 0;
                lineCell.textContent = '$' + (q * v).toFixed(2);
                recalcInkindGrand();
            }

            sel.addEventListener('change', () => {
                const opt = sel.options[sel.selectedIndex];
                if (!opt.value) return;
                const price = parseFloat(opt.dataset.price || 0);
                const stock = parseFloat(opt.dataset.qty   || 0);
                const unit  = opt.dataset.unit || '';

                // Auto-fill unit value from item's current price
                valInput.value = price.toFixed(2);

                // Show stock info with warning if low
                stockDiv.textContent = `Stock: ${stock} ${unit}`;
                stockDiv.className   = 'rt-stock' + (stock < parseFloat(qtyInput.value || 0) ? ' rt-stock--low' : '');

                recalcInkindLine();
            });

            qtyInput.addEventListener('input', () => {
                const opt   = sel.options[sel.selectedIndex];
                const stock = parseFloat(opt?.dataset?.qty || 0);
                stockDiv.className = 'rt-stock' + (stock < parseFloat(qtyInput.value || 0) ? ' rt-stock--low' : '');
                recalcInkindLine();
            });

            tr.querySelector('.rt-remove').addEventListener('click', () => {
                tr.remove(); reindexBody(inkindBody); recalcInkindGrand();
            });

            inkindBody.appendChild(tr);
            reindexBody(inkindBody);
        }

        // ── Helpers ────────────────────────────────────────────────────
        function reindexBody(body) {
            const rows = body.querySelectorAll('tr');
            rows.forEach((tr, i) => {
                tr.querySelector('.row-num').textContent = i + 1;
                tr.querySelector('.rt-remove').disabled = rows.length === 1;
            });
        }

        function recalcGrand() {
            let total = 0;
            tbody.querySelectorAll('tr').forEach(tr => {
                const q = parseFloat(tr.querySelector('input[name*="[qty]"]')?.value) || 0;
                const c = parseFloat(tr.querySelector('input[name*="[unit_cost]"]')?.value) || 0;
                total += q * c;
            });
            grandTotalEl.textContent = '$' + total.toFixed(2);
        }

        function recalcInkindGrand() {
            let total = 0;
            inkindBody.querySelectorAll('tr').forEach(tr => {
                const q = parseFloat(tr.querySelector('input[name*="[quantity]"]')?.value) || 0;
                const v = parseFloat(tr.querySelector('input[name*="[unit_value]"]')?.value) || 0;
                total += q * v;
            });
            inkindTotalEl.textContent = '$' + total.toFixed(2);
        }

        // ── Init ───────────────────────────────────────────────────────
        if (OLD_ROWS && OLD_ROWS.length > 0) {
            OLD_ROWS.forEach(row => addRow(row));
        } else {
            addRow();
        }

        document.getElementById('addRowBtn').addEventListener('click', () => addRow());
        document.getElementById('addInkindRowBtn').addEventListener('click', () => addInkindRow());

        // ── Form validation ────────────────────────────────────────────
        document.getElementById('receiptForm').addEventListener('submit', e => {
            let valid = true;

            // Validate purchased items
            tbody.querySelectorAll('tr').forEach(tr => {
                const sel  = tr.querySelector('select');
                const qty  = tr.querySelector('input[name*="[qty]"]');
                const cost = tr.querySelector('input[name*="[unit_cost]"]');
                if (!sel.value || !qty.value || !cost.value) valid = false;
            });

            // Validate in-kind payment items
            if (paymentMethod === 'in_kind') {
                if (inkindBody.querySelectorAll('tr').length === 0) {
                    alert('Please add at least one item you are giving to the supplier as payment.');
                    e.preventDefault();
                    return;
                }
                inkindBody.querySelectorAll('tr').forEach(tr => {
                    const sel = tr.querySelector('select');
                    const qty = tr.querySelector('input[name*="[quantity]"]');
                    if (!sel.value || !qty.value || parseFloat(qty.value) <= 0) valid = false;
                });
            }

            if (!valid) {
                e.preventDefault();
                alert('Please fill in all item fields before saving.');
            }
        });
    })();
    </script>
</body>

</html>