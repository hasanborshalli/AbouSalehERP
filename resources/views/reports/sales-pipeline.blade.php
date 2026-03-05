<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Pipeline Report</title>
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
                        <h1 class="rpt-title" style="margin-top:4px;">🏗️ Sales Pipeline</h1>
                        <div style="font-size:12px;color:rgba(0,0,0,.4);margin-top:2px;">All units across all projects
                        </div>
                    </div>
                    <div class="rpt-exports">
                        @php $q = http_build_query(request()->only(['project_id','status'])); @endphp
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('reports.export.excel','sales-pipeline') }}?{{ $q }}">⬇ Excel</a>
                        <a class="exp-btn exp-btn--pdf"
                            href="{{ route('reports.export.pdf','sales-pipeline') }}?{{ $q }}" target="_blank">⬇ PDF</a>
                    </div>
                </div>

                {{-- KPIs --}}
                <div class="rpt-kpis">
                    <div class="lkpi">
                        <div class="lkpi__label">Total Units</div>
                        <div class="lkpi__val">{{ $totalUnits }}</div>
                    </div>
                    <div class="lkpi lkpi--green">
                        <div class="lkpi__label">Sold</div>
                        <div class="lkpi__val">{{ $totalSold }}</div>
                    </div>
                    <div class="lkpi lkpi--amber">
                        <div class="lkpi__label">Reserved</div>
                        <div class="lkpi__val">{{ $totalReserved }}</div>
                    </div>
                    <div class="lkpi lkpi--blue">
                        <div class="lkpi__label">Available</div>
                        <div class="lkpi__val">{{ $totalAvailable }}</div>
                    </div>
                    <div class="lkpi">
                        <div class="lkpi__label">Portfolio Value</div>
                        <div class="lkpi__val">${{ number_format($totalValue,0) }}</div>
                    </div>
                    <div class="lkpi lkpi--green">
                        <div class="lkpi__label">Collected</div>
                        <div class="lkpi__val">${{ number_format($totalCollected,0) }}</div>
                    </div>
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Outstanding</div>
                        <div class="lkpi__val">${{ number_format($totalOutstanding,0) }}</div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="rpt-filters">
                    <form method="GET">
                        <div class="lf-group">
                            <label>Project</label>
                            <select name="project_id">
                                <option value="">All projects</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $projectId==$p->id ? 'selected' : '' }}>{{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lf-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="available" {{ $status==='available' ?'selected':'' }}>Available</option>
                                <option value="reserved" {{ $status==='reserved' ?'selected':'' }}>Reserved</option>
                                <option value="sold" {{ $status==='sold' ?'selected':'' }}>Sold</option>
                            </select>
                        </div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('reports.sales-pipeline') }}" class="lf-btn lf-btn--clear">Clear</a>
                    </form>
                </div>

                {{-- Table --}}
                <div class="rpt-table-wrap">
                    @if($apartments->isEmpty())
                    <div class="empty-state">No apartments match the selected filters.</div>
                    @else
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Unit</th>
                                <th>Beds</th>
                                <th class="num">Area (m²)</th>
                                <th class="num">List Price</th>
                                <th>Status</th>
                                <th>Client</th>
                                <th>Contract Date</th>
                                <th class="num">Collected</th>
                                <th class="num">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apartments as $apt)
                            @php
                            $c = $apt->contract;
                            $collected = $c ? (float)$c->down_payment +
                            (float)$c->invoices->where('status','paid')->sum('amount') : 0;
                            $outstanding = $c ?
                            (float)$c->invoices->whereIn('status',['pending','overdue'])->sum('amount') : 0;
                            @endphp
                            <tr>
                                <td style="font-weight:600;">{{ $apt->project->name ?? '—' }}</td>
                                <td>Unit {{ $apt->unit_number ?? '#'.$apt->id }}</td>
                                <td>{{ $apt->bedrooms ?? '—' }}</td>
                                <td class="num">{{ $apt->area_sqm ? number_format($apt->area_sqm,1) : '—' }}</td>
                                <td class="num">${{ $apt->price_total ? number_format($apt->price_total,2) : '—' }}</td>
                                <td>
                                    @if($apt->status==='sold') <span class="chip chip--green">Sold</span>
                                    @elseif($apt->status==='reserved') <span class="chip chip--amber">Reserved</span>
                                    @else <span class="chip chip--gray">Available</span>
                                    @endif
                                </td>
                                <td>{{ $c?->client?->name ?? '—' }}</td>
                                <td>{{ $c?->contract_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="num val-green">{{ $collected>0 ? '$'.number_format($collected,2) : '—' }}
                                </td>
                                <td class="num val-red">{{ $outstanding>0 ? '$'.number_format($outstanding,2) : '—' }}
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