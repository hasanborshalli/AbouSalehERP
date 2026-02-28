<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Accounting</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
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
            @if ($errors->has('void'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">✕</span>
                <span class="alert__text">{{ $errors->first('void') }}</span>
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

            {{-- CHART CARD --}}
            <section class="dashboard-card" style="padding:18px;">
                <header
                    style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                    <div>
                        <h2 style="margin:0;">Accounting (Cash Basis)</h2>
                        <p style="margin:6px 0 0; opacity:.7;">Revenue = paid invoices. Expenses = purchases + operating
                            costs.</p>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <a class="clients-index__add-btn" href="{{ route('accounting.purchases') }}">Record purchase</a>
                        <a class="clients-index__add-btn" href="{{ route('accounting.expenses') }}">Record expense</a>
                    </div>
                </header>

                <div style="margin-top:16px;">
                    <canvas id="cashChart" height="90"></canvas>
                </div>
            </section>

            {{-- TABLES --}}
            <section style="display:grid; grid-template-columns: 1fr; gap:14px; margin-top:14px;">
                {{-- Purchases table (grouped by receipt) --}}
                <section class="dashboard-card" style="padding:18px;">
                    <header
                        style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div>
                            <h3 style="margin:0;">Recent Purchase Receipts</h3>
                            <p style="margin:6px 0 0; opacity:.7;">Inventory restocks grouped by receipt. Click a row to
                                see items.</p>
                        </div>
                        <a class="clients-index__add-btn" href="{{ route('accounting.purchases') }}">+ Purchase
                            Receipt</a>
                    </header>

                    <div class="table-scroll" style="margin-top:12px;">
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr style="text-align:left; background:rgba(0,0,0,.04);">
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08); width:28px;"></th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Receipt Ref</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Date</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Vendor</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Items</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Receipt Total
                                    </th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Notes</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $idx => $receipt)
                                @php
                                $isVoided = $receipt['all_voided'];
                                $rowId = 'rcpt-' . $idx;
                                $date = is_string($receipt['purchase_date'])
                                ? $receipt['purchase_date']
                                : optional($receipt['purchase_date'])->format('Y-m-d');
                                @endphp

                                {{-- Receipt summary row --}}
                                <tr class="receipt-row" onclick="toggleItems('{{ $rowId }}')"
                                    style="cursor:pointer; {{ $isVoided ? 'opacity:.5;' : '' }} transition:background .12s;"
                                    onmouseover="this.style.background='rgba(42,127,176,0.05)'"
                                    onmouseout="this.style.background=''">

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:center;">
                                        <span class="expand-icon" id="icon-{{ $rowId }}"
                                            style="display:inline-block; transition:transform .2s; font-size:11px; opacity:.5;">▶</span>
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); font-family:monospace; font-size:12px; white-space:nowrap;">
                                        {{ $receipt['receipt_ref'] ?? '—' }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $date }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $receipt['vendor_name'] ?? '—' }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        <span style="display:inline-flex; align-items:center; justify-content:center;
                                            background:rgba(42,127,176,0.1); color:rgba(42,127,176,0.9);
                                            border-radius:999px; padding:2px 10px; font-size:12px; font-weight:700;">
                                            {{ count($receipt['items']) }}
                                        </span>
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap; font-weight:700;">
                                        ${{ number_format($receipt['total'], 2) }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        {{ $receipt['notes'] ?? '—' }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        @if($isVoided)
                                        <span
                                            style="font-size:11px; font-weight:700; color:#b91c1c; background:rgba(185,28,28,0.08); padding:3px 10px; border-radius:999px;">VOID</span>
                                        @elseif($receipt['any_voided'])
                                        <span
                                            style="font-size:11px; font-weight:700; color:#d97706; background:rgba(217,119,6,0.08); padding:3px 10px; border-radius:999px;">PARTIAL</span>
                                        @else
                                        <span
                                            style="font-size:11px; font-weight:700; color:#15803d; background:rgba(21,128,61,0.08); padding:3px 10px; border-radius:999px;">ACTIVE</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Expandable items sub-table --}}
                                <tr id="{{ $rowId }}" style="display:none;">
                                    <td colspan="8" style="padding:0; border-bottom:2px solid rgba(42,127,176,0.12);">
                                        <div style="background:rgba(42,127,176,0.03); padding:10px 20px;">
                                            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                                <thead>
                                                    <tr style="text-align:left;">
                                                        <th
                                                            style="padding:6px 10px; color:rgba(0,0,0,.45); font-weight:700;">
                                                            Item</th>
                                                        <th
                                                            style="padding:6px 10px; color:rgba(0,0,0,.45); font-weight:700;">
                                                            Qty</th>
                                                        <th
                                                            style="padding:6px 10px; color:rgba(0,0,0,.45); font-weight:700;">
                                                            Unit Cost</th>
                                                        <th
                                                            style="padding:6px 10px; color:rgba(0,0,0,.45); font-weight:700;">
                                                            Line Total</th>
                                                        <th
                                                            style="padding:6px 10px; color:rgba(0,0,0,.45); font-weight:700;">
                                                            Status / Void</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($receipt['items'] as $p)
                                                    @php $pVoided = !is_null($p->voided_at); @endphp
                                                    <tr
                                                        style="{{ $pVoided ? 'opacity:.5; text-decoration:line-through;' : '' }}">
                                                        <td style="padding:6px 10px;">{{ $p->item?->name ?? 'Item
                                                            #'.$p->inventory_item_id }}</td>
                                                        <td style="padding:6px 10px;">{{ $p->qty }}</td>
                                                        <td style="padding:6px 10px;">${{
                                                            number_format((float)$p->unit_cost, 2) }}</td>
                                                        <td style="padding:6px 10px; font-weight:600;">${{
                                                            number_format((float)$p->total_cost, 2) }}</td>
                                                        <td style="padding:6px 10px;">
                                                            @if($pVoided)
                                                            <span
                                                                style="font-size:11px; font-weight:700; color:#b91c1c;">VOID
                                                                — {{ $p->void_reason }}</span>
                                                            @else
                                                            <form method="post"
                                                                action="{{ route('accounting.purchases.void', $p) }}"
                                                                style="display:flex; gap:6px; align-items:center;"
                                                                onclick="event.stopPropagation()">
                                                                @csrf @method('PATCH')
                                                                <input name="reason" required placeholder="Void reason"
                                                                    style="padding:5px 8px; border-radius:8px; border:1px solid rgba(0,0,0,.18); font-size:12px; min-width:160px;" />
                                                                <button type="submit"
                                                                    style="padding:5px 12px; border-radius:999px; border:0; cursor:pointer; background:#b91c1c; color:white; font-size:12px;">
                                                                    Void
                                                                </button>
                                                            </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="8" style="padding:14px; opacity:.7;">No purchases yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Expenses table --}}
                <section class="dashboard-card" style="padding:18px;">
                    <header
                        style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div>
                            <h3 style="margin:0;">Recent Operating Expenses</h3>
                            <p style="margin:6px 0 0; opacity:.7;">Non-inventory costs (rent, salaries, fuel...).</p>
                        </div>
                        <a class="clients-index__add-btn" href="{{ route('accounting.expenses') }}">+ Expense</a>
                    </header>

                    <div class="table-scroll" style="margin-top:12px;">
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr style="text-align:left; background:rgba(0,0,0,.04);">
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Date</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Category</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Amount</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Description</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08); width:360px;">
                                        Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($opExpenses as $e)
                                @php
                                $isVoided = !is_null($e->voided_at);
                                @endphp

                                <tr style="{{ $isVoided ? 'opacity:.55;' : '' }}">
                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ optional($e->expense_date)->format('Y-m-d') ?? $e->expense_date }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $e->category }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap; font-weight:600;">
                                        ${{ number_format((float)$e->amount, 2) }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $e->description ?? '—' }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        @if(!$isVoided)
                                        <form method="post" action="{{ route('accounting.expenses.void', $e) }}"
                                            style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                            @csrf
                                            @method('PATCH')

                                            <input name="reason" required placeholder="Void reason"
                                                style="padding:8px 10px; border-radius:10px; border:1px solid rgba(0,0,0,.18); min-width:220px;" />

                                            <button type="submit"
                                                style="padding:8px 12px; border-radius:999px; border:0; cursor:pointer; background:#b91c1c; color:white;">
                                                Void
                                            </button>
                                        </form>
                                        @else
                                        <div style="font-size:12px;">
                                            <div><b>VOID</b> — {{ $e->void_reason ?? '—' }}</div>
                                            <div style="opacity:.8;">{{ optional($e->voided_at)->format('Y-m-d H:i') }}
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="padding:14px; opacity:.7;">No expenses yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
                {{-- Revenues table --}}
                <section class="dashboard-card" style="padding:18px;">
                    <header
                        style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div>
                            <h3 style="margin:0;">Latest Revenues (Cash In)</h3>
                            <p style="margin:6px 0 0; opacity:.7;">Paid invoices posted as cash-in revenue.</p>
                        </div>
                        <a class="clients-index__add-btn" href="{{ route('invoices.overview') }}">Go to invoices</a>
                    </header>

                    <div class="table-scroll" style="margin-top:12px;">
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr style="text-align:left; background:rgba(0,0,0,.04);">
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Date</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Invoice</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Description</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($revenuesRows as $r)
                                <tr>
                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ optional($r->posted_at)->format('Y-m-d H:i') ?? $r->posted_at }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        @if($r->source_type === 'invoice' && $r->source_id)
                                        #{{ $r->source_id }}
                                        @else
                                        —
                                        @endif
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $r->description ?? '—' }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap; font-weight:600;">
                                        ${{ number_format((float)$r->amount, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="padding:14px; opacity:.7;">No revenue entries yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = @json($labels);
        const revenues = @json($revenues);
        const expenses = @json($expenses);
        const net = @json($net);

        const ctx = document.getElementById('cashChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Revenue (Cash In)', data: revenues },
                    { label: 'Expenses (Cash Out)', data: expenses },
                    { label: 'Net', data: net },
                ]
            }
        });
    </script>
    <script src="/js/navSearch.js"></script>
    <script>
        function toggleItems(rowId) {
        const row  = document.getElementById(rowId);
        const icon = document.getElementById('icon-' + rowId);
        const open = row.style.display !== 'none' && row.style.display !== '';
        row.style.display  = open ? 'none' : 'table-row';
        icon.style.transform = open ? 'rotate(0deg)' : 'rotate(90deg)';
        icon.style.opacity   = open ? '0.5' : '1';
    }
    </script>
</body>

</html>