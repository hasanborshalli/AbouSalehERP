<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $project ? 'Project Report — '.$project->name : 'Report by Project' }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/reportsProject.css">
    <link rel="stylesheet" href="/css/alert.css">
    <style>

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
            <div class="rpt">

                {{-- ── Back + Picker ── --}}
                <div
                    style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                    <a class="rpt-back" href="{{ route('reports.index') }}">← Reports</a>
                    <form method="GET" action="{{ route('reports.project') }}"
                        style="display:flex;align-items:center;gap:8px;">
                        <select name="id" onchange="this.form.submit()"
                            style="padding:7px 32px 7px 11px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:600;background:#fff;cursor:pointer;min-width:200px;">
                            <option value="">— Select a project —</option>
                            @foreach($allProjects as $p)
                            <option value="{{ $p->id }}" {{ $project && $project->id===$p->id ? 'selected' : '' }}>{{
                                $p->name }}{{ $p->code ? ' ('.$p->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if(!$project)
                <div
                    style="text-align:center;padding:64px;color:rgba(0,0,0,.4);font-size:15px;background:#fff;border-radius:12px;border:1.5px solid #e5e7eb;">
                    <div style="font-size:40px;margin-bottom:12px;">📊</div>
                    Select a project above to view its full report.
                </div>
                @else

                {{-- ── Hero ── --}}
                <div class="rpt-hero">
                    <div>
                        <h2 class="rpt-hero__title">{{ $project->name }}</h2>
                        <div class="rpt-hero__meta">
                            {{ $project->city }}@if($project->area), {{ $project->area }}@endif
                            @if($project->code) · Code: {{ $project->code }}@endif
                            @if($project->start_date) · Started: {{ $project->start_date }}@endif
                        </div>
                    </div>
                </div>

                {{-- ── KPIs ── --}}
                <div class="rpt-kpis">
                    <div class="rpt-kpi rpt-kpi--blue">
                        <p class="rpt-kpi__label">Total Revenue Collected</p>
                        <p class="rpt-kpi__value">${{ number_format($totalRevenue, 2) }}</p>
                        <p class="rpt-kpi__sub">Paid invoices + down payments</p>
                    </div>
                    <div class="rpt-kpi rpt-kpi--red">
                        <p class="rpt-kpi__label">Total Cost (Actual)</p>
                        <p class="rpt-kpi__value">${{ number_format($totalCost, 2) }}</p>
                        <p class="rpt-kpi__sub">Materials + additional costs</p>
                    </div>
                    <div class="rpt-kpi {{ $profit >= 0 ? 'rpt-kpi--green' : 'rpt-kpi--red' }}">
                        <p class="rpt-kpi__label">Net Profit / Loss</p>
                        <p class="rpt-kpi__value">{{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}</p>
                        <p class="rpt-kpi__sub">Revenue minus all costs</p>
                    </div>
                    <div class="rpt-kpi rpt-kpi--amber">
                        <p class="rpt-kpi__label">Potential (All Units Sold)</p>
                        <p class="rpt-kpi__value">${{ number_format($totalSellingPrice, 2) }}</p>
                        <p class="rpt-kpi__sub">{{ $stats['sold'] }} sold · {{ $stats['reserved'] }} reserved · {{
                            $stats['available'] }} available</p>
                    </div>
                </div>

                {{-- ── Project Materials ── --}}
                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">🧱</span> Project-Level Materials
                        (from Inventory)</p>
                    @if($project->inventoryUsages->isNotEmpty())
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="num">Qty Used</th>
                                <th>Unit</th>
                                <th class="num">Unit Price</th>
                                <th class="num">Line Cost</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->inventoryUsages as $u)
                            @php $linePrice = (float)($u->inventoryItem->price ?? 0) * (float)$u->quantity_needed;
                            @endphp
                            <tr>
                                <td>{{ $u->inventoryItem->name ?? '—' }}</td>
                                <td class="num">{{ number_format($u->quantity_needed, 2) }}</td>
                                <td>{{ $u->unit ?? $u->inventoryItem->unit ?? '—' }}</td>
                                <td class="num">${{ number_format((float)($u->inventoryItem->price ?? 0), 2) }}</td>
                                <td class="num bold">${{ number_format($linePrice, 2) }}</td>
                                <td>
                                    <form method="post"
                                        action="{{ route('projects.materials.destroy', [$project, $u]) }}"
                                        onsubmit="return confirm('Remove this material and restore stock?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">✕</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total project materials cost</td>
                                <td class="num">${{ number_format($projectMaterialsCost, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p class="muted" style="font-size:13px; padding:8px 0;">No project-level materials added yet.</p>
                    @endif

                    {{-- Add material form --}}
                    <div class="rpt-add-form">
                        <p class="rpt-add-form__title">＋ Add material to this project</p>
                        <form method="post" action="{{ route('projects.materials.store', $project) }}">
                            @csrf
                            <div class="rpt-add-form__grid rpt-add-form__grid--mat">
                                <div>
                                    <label>Inventory item</label>
                                    <select name="inventory_item_id" required>
                                        <option value="" disabled selected>Select item…</option>
                                        @foreach($inventoryItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ $item->quantity }}
                                            {{ $item->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label>Quantity</label>
                                    <input type="number" name="quantity_needed" min="0.01" step="0.01" placeholder="0"
                                        required>
                                </div>
                                <button type="submit" class="rpt-add-form__submit"
                                    style="align-self:flex-end">Add</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── Project Additional Costs ── --}}
                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">📋</span> Project Additional Costs</p>
                    @if($projCosts->isNotEmpty())
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="num">Expected</th>
                                <th class="num">Actual</th>
                                <th class="num">Variance</th>
                                <th>Settle / Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projCosts as $c)
                            @php
                            $variance = $c->variance();
                            $settled = $c->isSettled();
                            @endphp
                            <tr>
                                <td>{{ $c->description }}</td>
                                <td class="muted">{{ $c->category ?? '—' }}</td>
                                <td class="num">${{ number_format($c->expected_amount, 2) }}</td>
                                <td class="num">{{ $settled ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                                <td class="num">
                                    @if($settled)
                                    @if($variance > 0)
                                    <span class="badge badge--over">▲ ${{ number_format($variance, 2) }} over</span>
                                    @elseif($variance < 0) <span class="badge badge--under">▼ ${{
                                        number_format(abs($variance), 2) }} saved</span>
                                        @else
                                        <span class="badge badge--paid">On budget</span>
                                        @endif
                                        @else — @endif
                                </td>
                                <td>
                                    @if(!$settled)
                                    <form class="settle-form" method="post"
                                        action="{{ route('projects.costs.settle', [$project, $c]) }}">
                                        @csrf @method('PATCH')
                                        <input type="number" name="actual_amount" step="0.01" min="0"
                                            placeholder="Actual $" required>
                                        <button type="submit">✔ Settle</button>
                                    </form>
                                    @else
                                    <span class="badge badge--paid">Settled {{ $c->actual_entered_at?->format('Y-m-d')
                                        }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$settled)
                                    <form method="post" action="{{ route('projects.costs.destroy', [$project, $c]) }}"
                                        onsubmit="return confirm('Delete this cost entry?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">✕</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Totals</td>
                                <td class="num">${{ number_format($projCostsExpected, 2) }}</td>
                                <td class="num">${{ number_format($projCostsActual, 2) }}</td>
                                <td class="num">
                                    @php $projVariance = $projCostsActual - $projCostsExpected; @endphp
                                    @if($projVariance > 0)
                                    <span class="badge badge--over">▲ ${{ number_format($projVariance, 2) }}</span>
                                    @elseif($projVariance < 0) <span class="badge badge--under">▼ ${{
                                        number_format(abs($projVariance), 2) }}</span>
                                        @else —
                                        @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p class="muted" style="font-size:13px; padding:8px 0;">No additional costs added yet.</p>
                    @endif

                    {{-- Add cost form --}}
                    <div class="rpt-add-form">
                        <p class="rpt-add-form__title">＋ Add expected cost for this project</p>
                        <form method="post" action="{{ route('projects.costs.store', $project) }}">
                            @csrf
                            <div class="rpt-add-form__grid">
                                <div>
                                    <label>Description</label>
                                    <input type="text" name="description" placeholder="e.g. Scaffolding hire" required>
                                </div>
                                <div>
                                    <label>Category</label>
                                    <input type="text" name="category" placeholder="e.g. equipment">
                                </div>
                                <div>
                                    <label>Expected Amount ($)</label>
                                    <input type="number" name="expected_amount" min="0" step="0.01" placeholder="0.00"
                                        required>
                                </div>
                            </div>
                            <div style="margin-top:8px;">
                                <button type="submit" class="rpt-add-form__submit">Add Cost</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── Apartments Summary Grid ── --}}
                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">🏘️</span> Apartments Overview ({{
                        $stats['total_apartments'] }} units)</p>
                    <div class="rpt-apts-grid">
                        @foreach($apartments as $apt)
                        @php
                        $aptMatCost = $apt->materials->sum(fn($m) => (float)($m->inventoryItem->price ?? 0) *
                        (float)$m->quantity_needed);
                        $aptCostActual = $apt->additionalCosts->sum(fn($c) => $c->isSettled() ? (float)$c->actual_amount
                        : 0.0);
                        $aptTotalCost = $aptMatCost + $aptCostActual;
                        $aptPaid = (float)($apt->contract?->invoices->where('status','paid')->sum('amount') ?? 0)
                        + (float)($apt->contract?->down_payment ?? 0);
                        $aptProfit = $aptPaid - $aptTotalCost;
                        @endphp
                        <a class="rpt-apt-card" href="{{ route('reports.apartment', $apt) }}">
                            <p class="rpt-apt-card__unit">Unit {{ $apt->unit_number }}</p>
                            <p class="rpt-apt-card__floor">Floor {{ $apt->floor->floor_number }}</p>
                            <div class="rpt-apt-card__row">
                                <span class="rpt-apt-card__key">Status</span>
                                <span class="badge badge--status-{{ $apt->status }}">{{ ucfirst($apt->status) }}</span>
                            </div>
                            <div class="rpt-apt-card__row">
                                <span class="rpt-apt-card__key">Cost</span>
                                <span class="rpt-apt-card__val">${{ number_format($aptTotalCost, 0) }}</span>
                            </div>
                            <div class="rpt-apt-card__row">
                                <span class="rpt-apt-card__key">Collected</span>
                                <span class="rpt-apt-card__val">${{ number_format($aptPaid, 0) }}</span>
                            </div>
                            <div class="rpt-apt-card__row">
                                <span class="rpt-apt-card__key">Profit</span>
                                <span class="rpt-apt-card__val"
                                    style="color:{{ $aptProfit >= 0 ? '#15803d' : '#b91c1c' }}">
                                    {{ $aptProfit >= 0 ? '+' : '' }}${{ number_format($aptProfit, 0) }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- ── Revenue Table ── --}}
                @if($contracts->isNotEmpty())
                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">💰</span> Revenue — Paid Invoices
                        &amp; Down Payments</p>
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Unit</th>
                                <th>Client</th>
                                <th class="num">Down Payment</th>
                                <th class="num">Paid Invoices</th>
                                <th class="num">Total Collected</th>
                                <th class="num">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apartments as $apt)
                            @php
                            $c = $apt->contract;
                            if (!$c) continue;
                            $aptPaidInv = (float)$c->invoices->where('status','paid')->sum('amount');
                            $aptDown = (float)$c->down_payment;
                            $aptTotal = $aptPaidInv + $aptDown;
                            $aptRemain = max(0, (float)$c->final_price - $aptTotal);
                            @endphp
                            <tr>
                                <td class="bold">Unit {{ $apt->unit_number }}</td>
                                <td>{{ $c->client->name ?? '—' }}</td>
                                <td class="num">${{ number_format($aptDown, 2) }}</td>
                                <td class="num">${{ number_format($aptPaidInv, 2) }}</td>
                                <td class="num bold">${{ number_format($aptTotal, 2) }}</td>
                                <td class="num muted">${{ number_format($aptRemain, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Totals</td>
                                <td class="num">${{ number_format($downPaymentsTotal, 2) }}</td>
                                <td class="num">${{ number_format($paidInvoicesTotal, 2) }}</td>
                                <td class="num">${{ number_format($totalRevenue, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

                {{-- ── Cost Summary ── --}}
                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">📊</span> Full Cost Summary</p>
                    <table class="rpt-table">
                        <tbody>
                            <tr>
                                <td>Project-level materials</td>
                                <td class="num bold">${{ number_format($projectMaterialsCost, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Apartment-level materials (all units)</td>
                                <td class="num bold">${{ number_format($apartmentMaterialsCost, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Project additional costs (actual / expected)</td>
                                <td class="num bold">${{ number_format($projCostsActual, 2) }} <span class="muted">/ ${{
                                        number_format($projCostsExpected, 2) }}</span></td>
                            </tr>
                            <tr>
                                <td>Apartment additional costs (actual / expected)</td>
                                <td class="num bold">${{ number_format($aptCostsActual, 2) }} <span class="muted">/ ${{
                                        number_format($aptCostsExpected, 2) }}</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL COST</td>
                                <td class="num">${{ number_format($totalCost, 2) }}</td>
                            </tr>
                            <tr>
                                <td>TOTAL REVENUE COLLECTED</td>
                                <td class="num">${{ number_format($totalRevenue, 2) }}</td>
                            </tr>
                            <tr style="background:{{ $profit >= 0 ? 'rgba(21,128,61,0.06)' : 'rgba(185,28,28,0.06)' }}">
                                <td>NET PROFIT / LOSS</td>
                                <td class="num" style="color:{{ $profit >= 0 ? '#15803d' : '#b91c1c' }}">
                                    {{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @endif {{-- end project check --}}
            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
</body>

</html>