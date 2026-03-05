<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Outstanding Invoices Report</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/reportsOutstanding.css">
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
                        <h1 class="rpt-title" style="margin-top:4px;">🧾 Outstanding Invoices</h1>
                        <div style="font-size:12px;color:rgba(0,0,0,.4);margin-top:2px;">Unpaid & overdue invoices as of
                            today</div>
                    </div>
                    <div class="rpt-exports">
                        @php $q = http_build_query(request()->only(['project_id','overdue','date_from','date_to']));
                        @endphp
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('reports.export.excel','outstanding-invoices') }}?{{ $q }}">⬇ Excel</a>
                        <a class="exp-btn exp-btn--pdf"
                            href="{{ route('reports.export.pdf','outstanding-invoices') }}?{{ $q }}" target="_blank">⬇
                            PDF</a>
                    </div>
                </div>

                <div class="rpt-kpis">
                    <div class="lkpi">
                        <div class="lkpi__label">Total Outstanding</div>
                        <div class="lkpi__val">{{ $totalCount }} invoices</div>
                    </div>
                    <div class="lkpi lkpi--amber">
                        <div class="lkpi__label">Outstanding Amount</div>
                        <div class="lkpi__val">${{ number_format($totalAmount,2) }}</div>
                    </div>
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Overdue Count</div>
                        <div class="lkpi__val">{{ $overdueCount }}</div>
                    </div>
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Overdue Amount</div>
                        <div class="lkpi__val">${{ number_format($overdueAmount,2) }}</div>
                    </div>
                </div>

                <div class="rpt-filters">
                    <form method="GET">
                        <div class="lf-group">
                            <label>Project</label>
                            <select name="project_id">
                                <option value="">All projects</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $projectId==$p->id?'selected':'' }}>{{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lf-group">
                            <label>Status</label>
                            <select name="overdue">
                                <option value="">Pending & Overdue</option>
                                <option value="1" {{ $overdue==='1' ?'selected':'' }}>Overdue only</option>
                            </select>
                        </div>
                        <div class="lf-group"><label>Due From</label><input type="date" name="date_from"
                                value="{{ $dateFrom }}"></div>
                        <div class="lf-group"><label>Due To</label><input type="date" name="date_to"
                                value="{{ $dateTo }}"></div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('reports.outstanding-invoices') }}" class="lf-btn lf-btn--clear">Clear</a>
                    </form>
                </div>

                <div class="rpt-table-wrap">
                    @if($invoices->isEmpty())
                    <div class="empty-state">No outstanding invoices found.</div>
                    @else
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Project</th>
                                <th>Unit</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="num">Amount</th>
                                <th class="num">Days Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                            @php
                            $daysOverdue = $inv->status==='overdue' ? now()->diffInDays($inv->due_date, false) * -1 :
                            null;
                            $apt = $inv->contract?->apartment;
                            @endphp
                            <tr>
                                <td style="font-weight:600;font-family:monospace;">{{ $inv->invoice_number }}</td>
                                <td>{{ $inv->contract?->client?->name ?? '—' }}</td>
                                <td>{{ $apt?->project?->name ?? '—' }}</td>
                                <td>{{ $apt ? 'Unit '.($apt->unit_number ?? '#'.$apt->id) : '—' }}</td>
                                <td>{{ $inv->issue_date ?? '—' }}</td>
                                <td style="{{ $inv->status==='overdue'?'color:#dc2626;font-weight:600;':'' }}">{{
                                    $inv->due_date ?? '—' }}</td>
                                <td>
                                    @if($inv->status==='overdue') <span class="chip chip--red">Overdue</span>
                                    @else <span class="chip chip--amber">Pending</span>
                                    @endif
                                </td>
                                <td class="num val-amber">${{ number_format($inv->amount,2) }}</td>
                                <td class="num">
                                    @if($daysOverdue !== null)
                                    <span class="overdue-days">{{ $daysOverdue }} days</span>
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
</body>

</html>