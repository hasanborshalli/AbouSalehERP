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
                {{-- Purchases table --}}
                <section class="dashboard-card" style="padding:18px;">
                    <header
                        style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div>
                            <h3 style="margin:0;">Recent Purchases</h3>
                            <p style="margin:6px 0 0; opacity:.7;">Inventory restocks recorded as cash-out.</p>
                        </div>
                        <a class="clients-index__add-btn" href="{{ route('accounting.purchases') }}">+ Purchase</a>
                    </header>

                    <div style="overflow:auto; margin-top:12px;">
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr style="text-align:left; background:rgba(0,0,0,.04);">
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Date</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Item</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Qty</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Unit cost</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Total</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);">Vendor</th>
                                    <th style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08); width:360px;">
                                        Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($purchases as $p)
                                @php
                                $isVoided = !is_null($p->voided_at);
                                @endphp

                                <tr style="{{ $isVoided ? 'opacity:.55;' : '' }}">
                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ optional($p->purchase_date)->format('Y-m-d') ?? $p->purchase_date }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $p->item?->name ?? 'Item #' . $p->inventory_item_id }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $p->qty }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        ${{ number_format((float)$p->unit_cost, 2) }}
                                    </td>

                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap; font-weight:600;">
                                        ${{ number_format((float)$p->total_cost, 2) }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $p->vendor_name ?? '—' }}
                                    </td>

                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        @if(!$isVoided)
                                        <form method="post" action="{{ route('accounting.purchases.void', $p) }}"
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
                                            <div><b>VOID</b> — {{ $p->void_reason ?? '—' }}</div>
                                            <div style="opacity:.8;">{{ optional($p->voided_at)->format('Y-m-d H:i') }}
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="padding:14px; opacity:.7;">No purchases yet.</td>
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

                    <div style="overflow:auto; margin-top:12px;">
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

                    <div style="overflow:auto; margin-top:12px;">
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
</body>

</html>