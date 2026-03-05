<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operating Expenses Report</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
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
                        <h1 class="rpt-title" style="margin-top:4px;">💸 Operating Expenses</h1>
                        <div style="font-size:12px;color:rgba(0,0,0,.4);margin-top:2px;">{{ $countRows }} expense
                            records</div>
                    </div>
                    <div class="rpt-exports">
                        @php $q = http_build_query(request()->only(['category','date_from','date_to'])); @endphp
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('reports.export.excel','operating-expenses') }}?{{ $q }}">⬇ Excel</a>
                        <a class="exp-btn exp-btn--pdf"
                            href="{{ route('reports.export.pdf','operating-expenses') }}?{{ $q }}" target="_blank">⬇
                            PDF</a>
                    </div>
                </div>

                <div class="rpt-kpis">
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Total Expenses</div>
                        <div class="lkpi__val">${{ number_format($totalAmount,2) }}</div>
                    </div>
                    <div class="lkpi">
                        <div class="lkpi__label">Categories</div>
                        <div class="lkpi__val">{{ $byCategory->count() }}</div>
                    </div>
                    <div class="lkpi">
                        <div class="lkpi__label">Records</div>
                        <div class="lkpi__val">{{ $countRows }}</div>
                    </div>
                    @if($byCategory->isNotEmpty())
                    <div class="lkpi">
                        <div class="lkpi__label">Top Category</div>
                        <div class="lkpi__val" style="font-size:16px;text-transform:capitalize;">{{
                            $byCategory->keys()->first() }}</div>
                        <div style="font-size:11px;color:rgba(0,0,0,.35);margin-top:2px;">${{
                            number_format($byCategory->first(),2) }}</div>
                    </div>
                    @endif
                </div>

                <div class="rpt-filters">
                    <form method="GET">
                        <div class="lf-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="">All categories</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $category===$cat?'selected':'' }}>{{ ucfirst($cat) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lf-group"><label>From</label><input type="date" name="date_from"
                                value="{{ $dateFrom }}"></div>
                        <div class="lf-group"><label>To</label><input type="date" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('reports.operating-expenses') }}" class="lf-btn lf-btn--clear">Clear</a>
                    </form>
                </div>

                <div class="rpt-layout">
                    {{-- Category breakdown sidebar --}}
                    <div class="cat-sidebar">
                        <div class="cat-sidebar__head">By Category</div>
                        @forelse($byCategory as $cat => $amount)
                        <div class="cat-row">
                            <div>
                                <div class="cat-row__name">{{ $cat }}</div>
                                <div class="cat-bar">
                                    <div class="cat-bar__fill"
                                        style="width:{{ $totalAmount>0 ? ($amount/$totalAmount)*100 : 0 }}%;"></div>
                                </div>
                            </div>
                            <div class="cat-row__val">${{ number_format($amount,2) }}</div>
                        </div>
                        @empty
                        <div style="padding:16px;text-align:center;color:rgba(0,0,0,.35);font-size:13px;">No data</div>
                        @endforelse
                    </div>

                    {{-- Main table --}}
                    <div class="rpt-table-wrap">
                        @if($expenses->isEmpty())
                        <div class="empty-state">No expenses match the selected filters.</div>
                        @else
                        <table class="rpt-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th class="num">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $exp)
                                <tr>
                                    <td>{{ $exp->expense_date->format('Y-m-d') }}</td>
                                    <td><span class="chip">{{ $exp->category }}</span></td>
                                    <td>{{ $exp->description ?? '—' }}</td>
                                    <td class="num val-red">${{ number_format($exp->amount,2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
</body>

</html>