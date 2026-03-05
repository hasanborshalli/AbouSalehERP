<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Worker Payments Report</title>
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
                        <h1 class="rpt-title" style="margin-top:4px;">👷 Worker Payments</h1>
                        <div style="font-size:12px;color:rgba(0,0,0,.4);margin-top:2px;">All worker payment installments
                        </div>
                    </div>
                    <div class="rpt-exports">
                        @php $q = http_build_query(request()->only(['status','date_from','date_to','worker_id']));
                        @endphp
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('reports.export.excel','worker-payments') }}?{{ $q }}">⬇ Excel</a>
                        <a class="exp-btn exp-btn--pdf"
                            href="{{ route('reports.export.pdf','worker-payments') }}?{{ $q }}" target="_blank">⬇
                            PDF</a>
                    </div>
                </div>

                <div class="rpt-kpis">
                    <div class="lkpi">
                        <div class="lkpi__label">Total (All)</div>
                        <div class="lkpi__val">${{ number_format($totalAll,2) }}</div>
                    </div>
                    <div class="lkpi lkpi--green">
                        <div class="lkpi__label">Paid</div>
                        <div class="lkpi__val">${{ number_format($totalPaid,2) }}</div>
                    </div>
                    <div class="lkpi lkpi--amber">
                        <div class="lkpi__label">Pending</div>
                        <div class="lkpi__val">${{ number_format($totalPending,2) }}</div>
                        <div style="font-size:11px;color:rgba(0,0,0,.35);margin-top:2px;">{{ $countPending }}
                            installments</div>
                    </div>
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Overdue</div>
                        <div class="lkpi__val">{{ $countOverdue }}</div>
                        <div style="font-size:11px;color:rgba(0,0,0,.35);margin-top:2px;">past due date</div>
                    </div>
                </div>

                <div class="rpt-filters">
                    <form method="GET">
                        <div class="lf-group">
                            <label>Worker</label>
                            <select name="worker_id">
                                <option value="">All workers</option>
                                @foreach($workers as $w)
                                <option value="{{ $w->id }}" {{ $workerId==$w->id?'selected':'' }}>{{ $w->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lf-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="">All</option>
                                <option value="paid" {{ $status==='paid' ?'selected':'' }}>Paid</option>
                                <option value="pending" {{ $status==='pending' ?'selected':'' }}>Pending</option>
                            </select>
                        </div>
                        <div class="lf-group"><label>Due From</label><input type="date" name="date_from"
                                value="{{ $dateFrom }}"></div>
                        <div class="lf-group"><label>Due To</label><input type="date" name="date_to"
                                value="{{ $dateTo }}"></div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('reports.worker-payments') }}" class="lf-btn lf-btn--clear">Clear</a>
                    </form>
                </div>

                <div class="rpt-table-wrap">
                    @if($payments->isEmpty())
                    <div class="empty-state">No worker payments match the selected filters.</div>
                    @else
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Worker</th>
                                <th>Category</th>
                                <th>Project</th>
                                <th>Payment #</th>
                                <th>Due Date</th>
                                <th>Paid On</th>
                                <th>Status</th>
                                <th class="num">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $pmt)
                            @php $isLate = $pmt->status==='pending' && $pmt->due_date < now(); @endphp <tr>
                                <td style="font-weight:600;">{{ $pmt->contract?->worker?->name ?? '—' }}</td>
                                <td>{{ $pmt->contract?->category ?? '—' }}</td>
                                <td>{{ $pmt->contract?->project?->name ?? '—' }}</td>
                                <td style="font-family:monospace;">{{ $pmt->payment_number }}</td>
                                <td style="{{ $isLate?'color:#dc2626;font-weight:600;':'' }}">{{
                                    $pmt->due_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $pmt->paid_at?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    @if($pmt->status==='paid') <span class="chip chip--green">Paid</span>
                                    @elseif($isLate) <span class="chip chip--red">Overdue</span>
                                    @else <span class="chip chip--amber">Pending</span>
                                    @endif
                                </td>
                                <td class="num {{ $pmt->status==='paid'?'val-green':'val-amber' }}">${{
                                    number_format($pmt->amount,2) }}</td>
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