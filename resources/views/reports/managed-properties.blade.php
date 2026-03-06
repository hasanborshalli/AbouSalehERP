<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managed Properties Report</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/managed.css">
    <link rel="stylesheet" href="/css/managed-properties.css">

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
                        <h1 class="rpt-title">🏠 Managed Properties Report</h1>
                        <div style="font-size:12px;color:rgba(0,0,0,.4);margin-top:2px;">
                            {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} → {{
                            \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                        </div>
                    </div>
                </div>

                {{-- Date filter --}}
                <form method="GET" action="{{ route('reports.managed-properties') }}" class="filter-bar">
                    <label>From
                        <input type="date" name="date_from" value="{{ $dateFrom }}">
                    </label>
                    <label>To
                        <input type="date" name="date_to" value="{{ $dateTo }}">
                    </label>
                    <button type="submit">Apply</button>
                </form>

                {{-- ── OVERVIEW KPIS ──────────────────────────────────────── --}}
                <div class="sub-label">Overview — All Properties</div>
                <div class="rpt-kpi-grid">
                    <div class="rpt-kpi">
                        <p class="rpt-kpi__label">Total Properties</p>
                        <p class="rpt-kpi__val">{{ $properties->count() }}</p>
                    </div>
                    <div class="rpt-kpi blue">
                        <p class="rpt-kpi__label">Flip Properties</p>
                        <p class="rpt-kpi__val">{{ $flipProps->count() }}</p>
                    </div>
                    <div class="rpt-kpi" style="border-color:rgba(124,58,237,.2);background:rgba(124,58,237,.04);">
                        <p class="rpt-kpi__label">Rental Properties</p>
                        <p class="rpt-kpi__val" style="color:#7c3aed;">{{ $rentalProps->count() }}</p>
                    </div>
                    <div class="rpt-kpi amber">
                        <p class="rpt-kpi__label">Pending Payouts</p>
                        <p class="rpt-kpi__val">{{ $pendingOwnerPayouts->count() }}</p>
                    </div>
                    <div class="rpt-kpi amber">
                        <p class="rpt-kpi__label">Overdue Rent Pmts</p>
                        <p class="rpt-kpi__val">{{ $pendingRentalPayments->count() }}</p>
                    </div>
                </div>

                {{-- ── FLIP SECTION ─────────────────────────────────────── --}}
                <div class="sub-label">🔨 Flip Properties (Buy, Renovate, Sell)</div>
                <div class="rpt-kpi-grid">
                    <div class="rpt-kpi green">
                        <p class="rpt-kpi__label">Total Sale Income</p>
                        <p class="rpt-kpi__val">${{ number_format($flipTotalSaleIncome, 0) }}</p>
                    </div>
                    <div class="rpt-kpi red">
                        <p class="rpt-kpi__label">Owner Payouts</p>
                        <p class="rpt-kpi__val">${{ number_format($flipTotalOwnerPayout, 0) }}</p>
                    </div>
                    <div class="rpt-kpi red">
                        <p class="rpt-kpi__label">Renovation Costs</p>
                        <p class="rpt-kpi__val">${{ number_format($flipTotalExpenses, 0) }}</p>
                    </div>
                    @php $isFlipLoss = $flipTotalProfit < 0; @endphp <div
                        class="rpt-kpi {{ $isFlipLoss ? 'red' : 'green' }}">
                        <p class="rpt-kpi__label">Net {{ $isFlipLoss ? 'Loss' : 'Profit' }}</p>
                        <p class="rpt-kpi__val">{{ $isFlipLoss ? '-' : '' }}${{ number_format(abs($flipTotalProfit), 0)
                            }}</p>
                </div>
            </div>

            <div class="rpt-section">
                <div class="rpt-section__head">
                    <h3 class="rpt-section__title">Flip Properties Detail</h3>
                </div>
                <div class="rpt-section__body">
                    <table class="rpt-tbl">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th class="r">Owner Asking</th>
                                <th class="r">Renovation Cost</th>
                                <th class="r">Sale Price</th>
                                <th class="r">Owner Payout</th>
                                <th class="r">Net Profit</th>
                                <th>Owner Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($flipProps as $p)
                            @php
                            $exp = $p->totalExpenses();
                            $profit = $p->sale
                            ? (float)$p->sale->sale_price - (float)$p->sale->owner_payout_amount - $exp
                            : null;
                            $badge = $p->statusBadge();
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('managed.show', $p) }}"
                                        style="font-weight:700;color:#2563eb;text-decoration:none;">
                                        {{ $p->address }}
                                    </a>
                                    @if($p->city)<br><span style="font-size:11px;color:#9ca3af;">{{ $p->city
                                        }}</span>@endif
                                </td>
                                <td>{{ $p->owner_name }}<br><span style="font-size:11px;color:#9ca3af;">{{
                                        $p->owner_phone }}</span></td>
                                <td><span class="badge badge-{{ $p->status }}" style="color:{{ $badge['color'] }};">{{
                                        $badge['label'] }}</span></td>
                                <td class="r">${{ number_format($p->owner_asking_price, 2) }}</td>
                                <td class="r" style="color:#dc2626;">${{ number_format($exp, 2) }}</td>
                                <td class="r" style="color:#059669;">
                                    {{ $p->sale ? '$'.number_format($p->sale->sale_price, 2) : '—' }}
                                </td>
                                <td class="r" style="color:#dc2626;">
                                    {{ $p->sale ? '$'.number_format($p->sale->owner_payout_amount, 2) : '—' }}
                                </td>
                                <td class="r"
                                    style="color:{{ $profit === null ? '#9ca3af' : ($profit >= 0 ? '#059669' : '#dc2626') }}; font-weight:800;">
                                    @if($profit !== null)
                                    {{ $profit < 0 ? '-' : '' }}${{ number_format(abs($profit), 2) }} @else Not sold
                                        @endif </td>
                                <td>
                                    @if($p->sale)
                                    @if($p->sale->owner_paid_at)
                                    <span style="color:#059669;font-weight:600;">✓ {{ $p->sale->owner_paid_at->format('d
                                        M Y') }}</span>
                                    @else
                                    <span style="color:#d97706;font-weight:600;">⏳ Pending</span>
                                    @endif
                                    @else
                                    <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" style="text-align:center;padding:24px;color:#9ca3af;">No flip properties
                                    yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($flipProps->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="3">Totals</td>
                                <td class="r">${{ number_format($flipProps->sum('owner_asking_price'), 2) }}</td>
                                <td class="r" style="color:#dc2626;">${{ number_format($flipTotalExpenses, 2) }}</td>
                                <td class="r" style="color:#059669;">${{ number_format($flipTotalSaleIncome, 2) }}</td>
                                <td class="r" style="color:#dc2626;">${{ number_format($flipTotalOwnerPayout, 2) }}</td>
                                <td class="r"
                                    style="color:{{ $flipTotalProfit >= 0 ? '#059669' : '#dc2626' }}; font-weight:800;">
                                    {{ $flipTotalProfit < 0 ? '-' : '' }}${{ number_format(abs($flipTotalProfit), 2) }}
                                        </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Pending owner payouts alert --}}
            @if($pendingOwnerPayouts->isNotEmpty())
            <div class="alert-box warn">
                <p class="alert-title">⚠️ {{ $pendingOwnerPayouts->count() }} Owner Payout(s) Still Pending</p>
                @foreach($pendingOwnerPayouts as $s)
                <p style="font-size:12px;margin:4px 0;color:#78350f;">
                    {{ $s->property->address ?? '—' }} — Owner: {{ $s->property->owner_name ?? '—' }} —
                    Amount: <strong>${{ number_format($s->owner_payout_amount, 2) }}</strong>
                    <a href="{{ route('managed.show', $s->managed_property_id) }}"
                        style="color:#92400e;margin-left:8px;">Pay now →</a>
                </p>
                @endforeach
            </div>
            @endif

            {{-- ── RENTAL SECTION ───────────────────────────────────── --}}
            <div class="sub-label" style="margin-top:8px;">🔑 Rental Properties (Management)</div>
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi green">
                    <p class="rpt-kpi__label">Total Rent Collected</p>
                    <p class="rpt-kpi__val">${{ number_format($rentalTotalCollected, 0) }}</p>
                </div>
                <div class="rpt-kpi red">
                    <p class="rpt-kpi__label">Paid to Owners</p>
                    <p class="rpt-kpi__val">${{ number_format($rentalTotalOwnerPaid, 0) }}</p>
                </div>
                <div class="rpt-kpi blue">
                    <p class="rpt-kpi__label">Company Commission</p>
                    <p class="rpt-kpi__val">${{ number_format($rentalTotalCommission, 0) }}</p>
                </div>
                <div class="rpt-kpi red">
                    <p class="rpt-kpi__label">Prep Expenses</p>
                    <p class="rpt-kpi__val">${{ number_format($rentalTotalExpenses, 0) }}</p>
                </div>
                <div class="rpt-kpi green">
                    <p class="rpt-kpi__label">Net Income</p>
                    <p class="rpt-kpi__val">${{ number_format($rentalTotalCommission - $rentalTotalExpenses, 0) }}</p>
                </div>
            </div>

            {{-- Monthly commission chart --}}
            @if($months->isNotEmpty())
            <div class="chart-card">
                <h3>Monthly Commission Income</h3>
                <canvas id="commChart" height="70"></canvas>
            </div>
            @endif

            <div class="rpt-section">
                <div class="rpt-section__head">
                    <h3 class="rpt-section__title">Rental Properties Detail</h3>
                </div>
                <div class="rpt-section__body">
                    <table class="rpt-tbl">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Active Tenant</th>
                                <th class="r">Monthly Rent</th>
                                <th class="r">Commission %</th>
                                <th class="r">Total Collected</th>
                                <th class="r">Commission Earned</th>
                                <th class="r">Prep Expenses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rentalProps as $p)
                            @php
                            $activeRental = $p->rentals->where('status','active')->first();
                            $allPmts = $p->rentals->flatMap->payments;
                            $collected = $allPmts->whereNotNull('collected_at')->sum('amount_collected');
                            $commission = $allPmts->where('status','owner_paid')->sum('company_commission');
                            $badge = $p->statusBadge();
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('managed.show', $p) }}"
                                        style="font-weight:700;color:#7c3aed;text-decoration:none;">
                                        {{ $p->address }}
                                    </a>
                                    @if($p->city)<br><span style="font-size:11px;color:#9ca3af;">{{ $p->city
                                        }}</span>@endif
                                </td>
                                <td>{{ $p->owner_name }}</td>
                                <td><span class="badge badge-{{ $p->status }}" style="color:{{ $badge['color'] }};">{{
                                        $badge['label'] }}</span></td>
                                <td style="font-size:12px;">
                                    @if($activeRental)
                                    {{ $activeRental->tenant_name }}<br>
                                    <span style="color:#9ca3af;">Until {{ $activeRental->end_date->format('M Y')
                                        }}</span>
                                    @else
                                    <span style="color:#9ca3af;">Vacant</span>
                                    @endif
                                </td>
                                <td class="r">${{ number_format($activeRental->monthly_rent ?? $p->agreed_rent_price ??
                                    0, 2) }}</td>
                                <td class="r">{{ $p->company_commission_pct ?? '—' }}%</td>
                                <td class="r" style="color:#059669;">${{ number_format($collected, 2) }}</td>
                                <td class="r" style="color:#2563eb;">${{ number_format($commission, 2) }}</td>
                                <td class="r" style="color:#dc2626;">${{ number_format($p->totalExpenses(), 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" style="text-align:center;padding:24px;color:#9ca3af;">No rental
                                    properties yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($rentalProps->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="6">Totals</td>
                                <td class="r" style="color:#059669;">${{ number_format($rentalTotalCollected, 2) }}</td>
                                <td class="r" style="color:#2563eb;">${{ number_format($rentalTotalCommission, 2) }}
                                </td>
                                <td class="r" style="color:#dc2626;">${{ number_format($rentalTotalExpenses, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Overdue rent payments --}}
            @if($pendingRentalPayments->isNotEmpty())
            <div class="alert-box warn">
                <p class="alert-title">⚠️ {{ $pendingRentalPayments->count() }} Overdue Rent Payment(s)</p>
                @foreach($pendingRentalPayments as $pmt)
                <p style="font-size:12px;margin:4px 0;color:#78350f;">
                    {{ $pmt->rental->property->address ?? '—' }} —
                    Tenant: {{ $pmt->rental->tenant_name ?? '—' }} —
                    Due: <strong>{{ $pmt->due_date->format('d M Y') }}</strong> —
                    Amount: <strong>${{ number_format($pmt->amount_due, 2) }}</strong>
                    <a href="{{ route('managed.show', $pmt->rental->managed_property_id) }}"
                        style="color:#92400e;margin-left:8px;">Collect →</a>
                </p>
                @endforeach
            </div>
            @endif

            {{-- ── EXPENSE BREAKDOWN ───────────────────────────────── --}}
            <div class="rpt-section">
                <div class="rpt-section__head">
                    <h3 class="rpt-section__title">All Renovation & Preparation Expenses</h3>
                </div>
                <div class="rpt-section__body">
                    <table class="rpt-tbl">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Vendor</th>
                                <th class="r">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $allExpenses = $properties->flatMap->expenses->sortByDesc('expense_date');
                            @endphp
                            @forelse($allExpenses as $exp)
                            <tr style="{{ $exp->isVoided() ? 'opacity:.45;' : '' }}">
                                <td style="white-space:nowrap;font-size:12px;">{{ $exp->expense_date->format('d M Y') }}
                                </td>
                                <td style="font-size:12px;">{{ $exp->property->address ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $exp->property->type ?? 'flip' }}">
                                        {{ ucfirst($exp->property->type ?? '—') }}
                                    </span>
                                </td>
                                <td>{{ $exp->description }}</td>
                                <td>{{ $exp->category ? ucfirst($exp->category) : '—' }}</td>
                                <td style="font-size:12px;color:#6b7280;">{{ $exp->vendor_name ?? '—' }}</td>
                                <td class="r" style="color:{{ $exp->isVoided() ? '#9ca3af' : '#dc2626' }};">
                                    ${{ number_format($exp->amount, 2) }}
                                </td>
                                <td>
                                    @if($exp->isVoided())
                                    <span class="badge badge-terminated">Voided</span>
                                    @else
                                    <span class="badge badge-active">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align:center;padding:24px;color:#9ca3af;">No expenses
                                    recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($allExpenses->where('voided_at', null)->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="6">Total Active Expenses</td>
                                <td class="r" style="color:#dc2626;">
                                    ${{ number_format($allExpenses->whereNull('voided_at')->sum('amount'), 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

    </div>{{-- end rpt-wrap --}}
    </main>
    <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        @if($months->isNotEmpty())
const commCtx = document.getElementById('commChart').getContext('2d');
new Chart(commCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($months->pluck('label')) !!},
        datasets: [{
            label: 'Commission ($)',
            data: {!! json_encode($months->pluck('commission')) !!},
            backgroundColor: 'rgba(37,99,235,.7)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } }
        }
    }
});
@endif
    </script>
</body>

</html>