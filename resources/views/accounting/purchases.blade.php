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

                        {{-- Receipt-level fields --}}
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

                        {{-- Items table --}}
                        <div class="receipt-table-wrap">
                            <table class="receipt-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th style="min-width:220px">Item</th>
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

                        {{-- Grand total --}}
                        <div class="receipt-total-bar">
                            <span class="receipt-total-label">Receipt Total</span>
                            <span class="receipt-total-amount" id="grandTotal">$0.00</span>
                        </div>

                        @error('items') <p class="rt-error" style="margin-bottom:12px">{{ $message }}</p> @enderror

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
        const ITEMS   = {!! $itemsJson !!};
        const OLD_ROWS = @json(old('items', []));

        let rowIndex = 0;
        const tbody       = document.getElementById('itemsBody');
        const grandTotalEl = document.getElementById('grandTotal');

        function buildOptions(selectedId) {
            let html = `<option value="" disabled ${!selectedId ? 'selected' : ''}>Select item</option>`;
            ITEMS.forEach(it => {
                const sel = String(it.id) === String(selectedId) ? 'selected' : '';
                html += `<option value="${it.id}" ${sel}>${it.name} (Stock: ${it.qty} ${it.unit ?? ''})</option>`;
            });
            return html;
        }

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
                tr.remove();
                reindex();
                recalcGrand();
            });

            tbody.appendChild(tr);
            reindex();
            recalcLine(); // init line total
        }

        function reindex() {
            const rows = tbody.querySelectorAll('tr');
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

        // Init
        if (OLD_ROWS && OLD_ROWS.length > 0) {
            OLD_ROWS.forEach(row => addRow(row));
        } else {
            addRow();
        }

        document.getElementById('addRowBtn').addEventListener('click', () => addRow());

        document.getElementById('receiptForm').addEventListener('submit', e => {
            let valid = true;
            tbody.querySelectorAll('tr').forEach(tr => {
                const sel  = tr.querySelector('select');
                const qty  = tr.querySelector('input[name*="[qty]"]');
                const cost = tr.querySelector('input[name*="[unit_cost]"]');
                if (!sel.value || !qty.value || !cost.value) valid = false;
            });
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all item fields before saving.');
            }
        });
    })();
    </script>
</body>

</html>