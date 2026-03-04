<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ledger — {{ $direction === 'in' ? 'Credit' : ($direction === 'out' ? 'Debit' : 'All Entries') }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <style>
        /* ── Page layout ── */
        .ledger-wrap {
            padding: 24px 28px;
            max-width: 1400px;
        }

        .ledger-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .ledger-title {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            margin: 0;
        }

        .ledger-back {
            font-size: 13px;
            color: rgba(42, 127, 176, .9);
            text-decoration: none;
            font-weight: 600;
        }

        .ledger-back:hover {
            text-decoration: underline;
        }

        /* ── KPI strip ── */
        .ledger-kpis {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .lkpi {
            flex: 1;
            min-width: 160px;
            padding: 14px 18px;
            border-radius: 12px;
            background: #fff;
            border: 1.5px solid #e5e7eb;
        }

        .lkpi__label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(0, 0, 0, .45);
            margin-bottom: 4px;
        }

        .lkpi__val {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
        }

        .lkpi--credit .lkpi__val {
            color: #059669;
        }

        .lkpi--debit .lkpi__val {
            color: #dc2626;
        }

        .lkpi--net .lkpi__val {
            color: #2563eb;
        }

        /* ── Filter bar ── */
        .ledger-filters {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 18px;
        }

        .ledger-filters form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .lf-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .lf-group label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(0, 0, 0, .45);
        }

        .lf-group input,
        .lf-group select {
            padding: 7px 11px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            background: #fafafa;
            min-width: 130px;
        }

        .lf-group input:focus,
        .lf-group select:focus {
            outline: none;
            border-color: rgba(42, 127, 176, .5);
        }

        .lf-btn {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
        }

        .lf-btn--apply {
            background: rgba(42, 127, 176, .9);
            color: #fff;
        }

        .lf-btn--clear {
            background: #f3f4f6;
            color: #374151;
        }

        .lf-btn:hover {
            opacity: .88;
        }

        /* ── Export buttons ── */
        .ledger-exports {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 16px;
        }

        .exp-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .15s;
        }

        .exp-btn:hover {
            opacity: .85;
        }

        .exp-btn--excel {
            background: #16a34a;
            color: #fff;
        }

        .exp-btn--pdf {
            background: #dc2626;
            color: #fff;
        }

        .exp-count {
            font-size: 12px;
            color: rgba(0, 0, 0, .4);
            margin-left: auto;
        }

        /* ── Table ── */
        .ledger-table-wrap {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .ledger-table th {
            background: #f8fafc;
            padding: 10px 14px;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(0, 0, 0, .5);
            border-bottom: 1.5px solid #e5e7eb;
            white-space: nowrap;
        }

        .ledger-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .ledger-table tr:last-child td {
            border-bottom: 0;
        }

        .ledger-table tr:hover td {
            background: #fafbfc;
        }

        .dir-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .dir-badge--in {
            background: #d1fae5;
            color: #065f46;
        }

        .dir-badge--out {
            background: #fee2e2;
            color: #991b1b;
        }

        .amount-in {
            color: #059669;
            font-weight: 700;
        }

        .amount-out {
            color: #dc2626;
            font-weight: 700;
        }

        .source-chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 700;
        }

        td.num {
            text-align: right;
        }

        th.num {
            text-align: right;
        }

        /* ── Pagination ── */
        .ledger-pagination {
            padding: 16px 14px;
            border-top: 1px solid #f1f5f9;
        }

        .pg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 11px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid #e5e7eb;
            color: #374151;
            background: #fff;
            transition: background .12s, border-color .12s;
        }

        a.pg-btn:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .pg-active {
            background: rgba(42, 127, 176, .9) !important;
            color: #fff !important;
            border-color: rgba(42, 127, 176, .9) !important;
        }

        .pg-disabled {
            color: #d1d5db;
            background: #fafafa;
            cursor: default;
        }

        .empty-state {
            text-align: center;
            padding: 48px;
            color: rgba(0, 0, 0, .4);
            font-size: 14px;
        }
    </style>
</head>

<body class="app-shell dashboard-page">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle">

    <div class="app-shell__sidebar">
        <x-sidebar />
    </div>

    <div class="app-shell__main">
        <x-navbar />

        <main class="app-content">
            <div class="ledger-wrap">

                {{-- Header --}}
                <div class="ledger-head">
                    <div>
                        <a class="ledger-back" href="{{ route('dashboard') }}">← Dashboard</a>
                        <h1 class="ledger-title" style="margin-top:4px;">
                            @if($direction === 'in') 💚 Credit Entries (دين)
                            @elseif($direction === 'out') 🔴 Debit Entries (مدين)
                            @else 📒 Full Ledger
                            @endif
                        </h1>
                    </div>
                    <div class="ledger-exports">
                        @php $exportQuery =
                        http_build_query(array_filter(request()->only(['direction','date_from','date_to','source_type','search'])));
                        @endphp
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('accounting.ledger.export.excel') }}?{{ $exportQuery }}">
                            ⬇ Export Excel
                        </a>
                        <a class="exp-btn exp-btn--pdf"
                            href="{{ route('accounting.ledger.export.pdf') }}?{{ $exportQuery }}" target="_blank">
                            ⬇ Export PDF
                        </a>
                    </div>
                </div>

                {{-- KPIs --}}
                <div class="ledger-kpis">
                    <div class="lkpi lkpi--credit">
                        <div class="lkpi__label">Credit (دين)</div>
                        <div class="lkpi__val">${{ number_format($filteredCredit, 2) }}</div>
                    </div>
                    <div class="lkpi lkpi--debit">
                        <div class="lkpi__label">Debit (مدين)</div>
                        <div class="lkpi__val">${{ number_format($filteredDebit, 2) }}</div>
                    </div>
                    <div class="lkpi lkpi--net">
                        <div class="lkpi__label">Net (filtered)</div>
                        @php $filteredNet = $filteredCredit - $filteredDebit; @endphp
                        <div class="lkpi__val" style="color:{{ $filteredNet >= 0 ? '#2563eb' : '#d97706' }}">
                            {{ $filteredNet >= 0 ? '+' : '' }}${{ number_format($filteredNet, 2) }}
                        </div>
                    </div>
                    <div class="lkpi">
                        <div class="lkpi__label">Rows shown</div>
                        <div class="lkpi__val">{{ number_format($entries->total()) }}</div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="ledger-filters">
                    <form method="GET" action="{{ route('accounting.ledger') }}">
                        <div class="lf-group">
                            <label>Direction</label>
                            <select name="direction">
                                <option value="">All</option>
                                <option value="in" {{ $direction==='in' ? 'selected' : '' }}>Credit (دين)</option>
                                <option value="out" {{ $direction==='out' ? 'selected' : '' }}>Debit (مدين)</option>
                            </select>
                        </div>
                        <div class="lf-group">
                            <label>From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}">
                        </div>
                        <div class="lf-group">
                            <label>To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="lf-group">
                            <label>Source Type</label>
                            <select name="source_type">
                                <option value="">All sources</option>
                                @foreach($sourceTypes as $st)
                                <option value="{{ $st }}" {{ $sourceType===$st ? 'selected' : '' }}>{{ str_replace('_',
                                    ' ', $st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lf-group">
                            <label>Search description</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="keyword…">
                        </div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('accounting.ledger') }}" class="lf-btn lf-btn--clear"
                            style="display:inline-flex;align-items:center;text-decoration:none;">Clear</a>
                    </form>
                </div>

                {{-- Table --}}
                <div class="ledger-table-wrap">
                    @if($entries->isEmpty())
                    <div class="empty-state">No ledger entries match the selected filters.</div>
                    @else
                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Direction</th>
                                <th>Account</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th class="num">Amount (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                            <tr>
                                <td style="color:rgba(0,0,0,.35); font-size:11px;">{{ $entry->id }}</td>
                                <td style="white-space:nowrap;">{{ $entry->posted_at->format('Y-m-d') }}</td>
                                <td>
                                    <span class="dir-badge dir-badge--{{ $entry->direction }}">
                                        {{ $entry->direction === 'in' ? '↑ Credit' : '↓ Debit' }}
                                    </span>
                                </td>
                                <td>{{ $entry->account->name ?? ($entry->account->code ?? '—') }}</td>
                                <td>
                                    @if($entry->source_type)
                                    <span class="source-chip">{{ str_replace('_', ' ', $entry->source_type) }}</span>
                                    @else —
                                    @endif
                                </td>
                                <td>{{ $entry->description ?? '—' }}</td>
                                <td class="num {{ $entry->direction === 'in' ? 'amount-in' : 'amount-out' }}">
                                    {{ $entry->direction === 'in' ? '+' : '−' }}${{ number_format((float)$entry->amount,
                                    2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    @if($entries->hasPages())
                    <div class="ledger-pagination">
                        @php
                        $prev = $entries->previousPageUrl();
                        $next = $entries->nextPageUrl();
                        $cur = $entries->currentPage();
                        $last = $entries->lastPage();
                        $from = max(1, $cur - 2);
                        $to = min($last, $cur + 2);
                        @endphp
                        <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;justify-content:center;">
                            {{-- Prev --}}
                            @if($prev)
                            <a href="{{ $prev }}" class="pg-btn">‹ Prev</a>
                            @else
                            <span class="pg-btn pg-disabled">‹ Prev</span>
                            @endif

                            {{-- First page if far away --}}
                            @if($from > 1)
                            <a href="{{ $entries->url(1) }}" class="pg-btn">1</a>
                            @if($from > 2)<span class="pg-btn pg-disabled">…</span>@endif
                            @endif

                            {{-- Page window --}}
                            @for($p = $from; $p <= $to; $p++) @if($p===$cur) <span class="pg-btn pg-active">{{ $p
                                }}</span>
                                @else
                                <a href="{{ $entries->url($p) }}" class="pg-btn">{{ $p }}</a>
                                @endif
                                @endfor

                                {{-- Last page if far away --}}
                                @if($to < $last) @if($to < $last - 1)<span class="pg-btn pg-disabled">…</span>@endif
                                    <a href="{{ $entries->url($last) }}" class="pg-btn">{{ $last }}</a>
                                    @endif

                                    {{-- Next --}}
                                    @if($next)
                                    <a href="{{ $next }}" class="pg-btn">Next ›</a>
                                    @else
                                    <span class="pg-btn pg-disabled">Next ›</span>
                                    @endif
                        </div>
                    </div>
                    @endif
                    @endif
                </div>

            </div>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
</body>

</html>