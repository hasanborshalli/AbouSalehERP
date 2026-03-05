<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profit & Loss Report</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/reportsPl.css">
</head>

<body class="app-shell dashboard-page">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle">
    <div class="app-shell__sidebar">
        <x-sidebar />
    </div>
    <div class="app-shell__main">
        <x-navbar />
        <main class="app-content">
            <div class="rpt-wrap">

                <div class="rpt-head">
                    <div>
                        <a class="rpt-back" href="{{ route('reports.index') }}">← Reports</a>
                        <h1 class="rpt-title" style="margin-top:4px;">📈 Profit & Loss</h1>
                        <div style="font-size:12px;color:rgba(0,0,0,.4);margin-top:2px;">{{ $dateFrom }} → {{ $dateTo }}
                        </div>
                    </div>
                    <div class="rpt-exports">
                        @php $q = http_build_query(request()->only(['date_from','date_to'])); @endphp
                        <a class="exp-btn exp-btn--excel" href="{{ route('reports.export.excel','pl') }}?{{ $q }}">⬇
                            Excel</a>
                        <a class="exp-btn exp-btn--pdf" href="{{ route('reports.export.pdf','pl') }}?{{ $q }}"
                            target="_blank">⬇ PDF</a>
                    </div>
                </div>

                {{-- KPIs --}}
                <div class="rpt-kpis">
                    <div class="lkpi lkpi--green">
                        <div class="lkpi__label">Total Revenue (Credit)</div>
                        <div class="lkpi__val">${{ number_format($totalRevenue,2) }}</div>
                    </div>
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Total Expenses (Debit)</div>
                        <div class="lkpi__val">${{ number_format($totalExpenses,2) }}</div>
                    </div>
                    <div class="lkpi {{ $netProfit>=0 ? 'lkpi--blue' : 'lkpi--amber' }}">
                        <div class="lkpi__label">Net {{ $netProfit>=0 ? 'Profit' : 'Loss' }}</div>
                        <div class="lkpi__val">{{ $netProfit>=0?'+':'' }}${{ number_format(abs($netProfit),2) }}</div>
                        @if($totalRevenue>0)
                        <div class="lkpi__sub">{{ number_format(($netProfit/$totalRevenue)*100,1) }}% margin</div>
                        @endif
                    </div>
                </div>

                {{-- Filters --}}
                <div class="rpt-filters">
                    <form method="GET">
                        <div class="lf-group"><label>From</label><input type="date" name="date_from"
                                value="{{ $dateFrom }}"></div>
                        <div class="lf-group"><label>To</label><input type="date" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('reports.pl') }}" class="lf-btn lf-btn--clear">Clear</a>
                    </form>
                </div>

                {{-- Revenue & Expenses side by side --}}
                <div class="rpt-two-col">
                    {{-- Revenue breakdown --}}
                    <div class="rpt-table-wrap">
                        <div class="rpt-section-head">
                            <h3>💚 Revenue by Source</h3>
                            <span style="font-size:13px;font-weight:800;color:#059669;">${{
                                number_format($totalRevenue,2) }}</span>
                        </div>
                        @if($revenueRows->isEmpty())
                        <div class="empty-state">No revenue entries in this period.</div>
                        @else
                        <table class="rpt-table">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th class="num">Entries</th>
                                    <th class="num">Amount</th>
                                    <th class="num">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueRows as $row)
                                <tr>
                                    <td><span class="chip chip--blue">{{ str_replace('_',' ',$row->source_type ?? '—')
                                            }}</span></td>
                                    <td class="num">{{ $row->entries }}</td>
                                    <td class="num val-green">${{ number_format($row->total,2) }}</td>
                                    <td class="pct">{{ $totalRevenue>0 ?
                                        number_format(($row->total/$totalRevenue)*100,1).'%' : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>

                    {{-- Expense breakdown --}}
                    <div class="rpt-table-wrap">
                        <div class="rpt-section-head">
                            <h3>🔴 Expenses by Source</h3>
                            <span style="font-size:13px;font-weight:800;color:#dc2626;">${{
                                number_format($totalExpenses,2) }}</span>
                        </div>
                        @if($expenseRows->isEmpty())
                        <div class="empty-state">No expense entries in this period.</div>
                        @else
                        <table class="rpt-table">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th class="num">Entries</th>
                                    <th class="num">Amount</th>
                                    <th class="num">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenseRows as $row)
                                <tr>
                                    <td><span class="chip chip--blue">{{ str_replace('_',' ',$row->source_type ?? '—')
                                            }}</span></td>
                                    <td class="num">{{ $row->entries }}</td>
                                    <td class="num val-red">${{ number_format($row->total,2) }}</td>
                                    <td class="pct">{{ $totalExpenses>0 ?
                                        number_format(($row->total/$totalExpenses)*100,1).'%' : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

                {{-- Monthly trend --}}
                @if($monthlyData->isNotEmpty())
                <div class="rpt-table-wrap monthly-table-wrap">
                    <div class="rpt-section-head">
                        <h3>📅 Monthly Trend</h3>
                    </div>
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="num">Revenue (Credit)</th>
                                <th class="num">Expenses (Debit)</th>
                                <th class="num">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $month => $rows)
                            @php
                            $rev = (float)($rows->firstWhere('direction','in')?->total ?? 0);
                            $exp = (float)($rows->firstWhere('direction','out')?->total ?? 0);
                            $net = $rev - $exp;
                            @endphp
                            <tr>
                                <td style="font-weight:600;">{{
                                    \Carbon\Carbon::createFromFormat('Y-m',$month)->format('M Y') }}</td>
                                <td class="num val-green">${{ number_format($rev,2) }}</td>
                                <td class="num val-red">${{ number_format($exp,2) }}</td>
                                <td class="num {{ $net>=0?'val-green':'val-red' }}">{{ $net>=0?'+':'' }}${{
                                    number_format($net,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
</body>

</html>