<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $apartment ? 'Apartment Report — Unit '.$apartment->unit_number : 'Report by Apartment' }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/reportsApartment.css">
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

                {{-- Back + Pickers --}}
                <div style="margin-bottom:16px;">
                    <a class="rpt-back" href="{{ route('reports.index') }}">← Reports</a>
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:center;">
                    <select id="projPicker" onchange="loadApartments(this.value)"
                        style="padding:7px 11px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:600;background:#fff;min-width:180px;">
                        <option value="">— Select project —</option>
                        @foreach($allProjects as $p)
                        <option value="{{ $p->id }}" {{ ($apartment && $apartment->project_id===$p->id) ? 'selected' :
                            '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <select id="aptPicker" onchange="goToApartment(this.value)"
                        style="padding:7px 11px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:600;background:#fff;min-width:200px;">
                        <option value="">— Select apartment —</option>
                        @if($apartment)
                        @foreach($allProjects->firstWhere('id', $apartment->project_id)?->floors ?? [] as $floor)
                        @foreach($floor->apartments as $apt)
                        <option value="{{ route('reports.apartment.show', $apt->id) }}" {{ $apt->id===$apartment->id ?
                            'selected' : '' }}>
                            Unit {{ $apt->unit_number }} (Floor {{ $floor->floor_number }}) · {{ ucfirst($apt->status)
                            }}
                        </option>
                        @endforeach
                        @endforeach
                        @endif
                    </select>
                </div>

                @if(!$apartment)
                <div
                    style="text-align:center;padding:64px;color:rgba(0,0,0,.4);font-size:15px;background:#fff;border-radius:12px;border:1.5px solid #e5e7eb;">
                    <div style="font-size:40px;margin-bottom:12px;">🏠</div>
                    Select a project and apartment above to view its report.
                </div>
                @else

                {{-- Hero + Exports --}}
                <div class="rpt-hero">
                    <div>
                        <h2 class="rpt-hero__title">Unit {{ $apartment->unit_number }} — {{ $apartment->project->name }}
                        </h2>
                        <div class="rpt-hero__meta">
                            Floor {{ $apartment->floor->floor_number }}
                            @if($apartment->bedrooms) · {{ $apartment->bedrooms }} bed @endif
                            @if($apartment->bathrooms) · {{ $apartment->bathrooms }} bath @endif
                            @if($apartment->area_sqm) · {{ $apartment->area_sqm }} m² @endif
                            · Selling price: ${{ number_format($apartment->price_total, 2) }}
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <a href="{{ route('apartments.unit', $apartment->id) }}"
                            style="text-decoration:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;background:rgba(42,127,176,0.1);color:rgba(42,127,176,0.9);">✏️
                            Edit Costs & Materials</a>
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('reports.export.excel','apartment') }}?apartment_id={{ $apartment->id }}">⬇
                            Excel</a>
                        <a class="exp-btn exp-btn--pdf"
                            href="{{ route('reports.export.pdf','apartment') }}?apartment_id={{ $apartment->id }}"
                            target="_blank">⬇ PDF</a>
                    </div>
                </div>

                {{-- KPIs --}}
                <div class="rpt-kpis">
                    <div class="rpt-kpi rpt-kpi--blue">
                        <p class="rpt-kpi__label">Revenue Collected</p>
                        <p class="rpt-kpi__value">${{ number_format($totalRevenue, 2) }}</p>
                        <p class="rpt-kpi__sub">Invoices + down payment</p>
                    </div>
                    <div class="rpt-kpi rpt-kpi--red">
                        <p class="rpt-kpi__label">Total Cost</p>
                        <p class="rpt-kpi__value">${{ number_format($totalCost, 2) }}</p>
                        <p class="rpt-kpi__sub">Materials + additional costs</p>
                    </div>
                    <div class="rpt-kpi {{ $profit >= 0 ? 'rpt-kpi--green' : 'rpt-kpi--red' }}">
                        <p class="rpt-kpi__label">Net Profit / Loss</p>
                        <p class="rpt-kpi__value">{{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}</p>
                        <p class="rpt-kpi__sub">Revenue minus all costs</p>
                    </div>
                </div>

                {{-- Client & Contract (read-only) --}}
                @if($contract)
                <div class="rpt-section">
                    <p class="rpt-section__title">🧾 Contract &amp; Client</p>
                    <div class="rpt-info-grid">
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Client</p>
                            <p class="rpt-info-item__val">{{ $contract->client->name ?? '—' }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Phone</p>
                            <p class="rpt-info-item__val">{{ $contract->client->phone ?? '—' }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Contract Date</p>
                            <p class="rpt-info-item__val">{{ $contract->contract_date ?? '—' }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Final Price</p>
                            <p class="rpt-info-item__val">${{ number_format($contract->final_price, 2) }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Down Payment</p>
                            <p class="rpt-info-item__val">${{ number_format($downPayment, 2) }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Installments</p>
                            <p class="rpt-info-item__val">{{ $contract->installment_months }} × ${{
                                number_format($contract->installment_amount, 2) }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Paid Invoices</p>
                            <p class="rpt-info-item__val">{{ $paidInvoices->count() }} / {{ $invoices->count() }}</p>
                        </div>
                        <div class="rpt-info-item">
                            <p class="rpt-info-item__key">Remaining Balance</p>
                            <p class="rpt-info-item__val">${{ number_format(max(0,(float)$contract->final_price -
                                $totalRevenue), 2) }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Invoices (read-only) --}}
                @if($invoices->isNotEmpty())
                <div class="rpt-section">
                    <p class="rpt-section__title">💰 Invoices ({{ $invoices->count() }} total)</p>
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th class="num">Amount</th>
                                <th class="num">Late Fee</th>
                                <th>Status</th>
                                <th>Paid At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices->sortBy('issue_date') as $inv)
                            <tr>
                                <td class="muted">{{ $inv->invoice_number ?? $inv->id }}</td>
                                <td>{{ $inv->issue_date }}</td>
                                <td>{{ $inv->due_date }}</td>
                                <td class="num bold">${{ number_format($inv->amount, 2) }}</td>
                                <td class="num muted">@if($inv->late_fee_amount > 0)+${{
                                    number_format($inv->late_fee_amount, 2) }}@else —@endif</td>
                                <td>
                                    @if($inv->status === 'paid') <span class="badge badge--paid">Paid</span>
                                    @elseif($inv->status === 'overdue') <span
                                        class="badge badge--overdue">Overdue</span>
                                    @else <span class="badge badge--pending">Pending</span>
                                    @endif
                                </td>
                                <td class="muted">{{ $inv->paid_at ?
                                    \Carbon\Carbon::parse($inv->paid_at)->format('Y-m-d') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total paid</td>
                                <td class="num">${{ number_format($paidAmount, 2) }}</td>
                                <td class="num">${{ number_format($paidInvoices->sum('late_fee_amount'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

                {{-- Materials (read-only) --}}
                <div class="rpt-section">
                    <p class="rpt-section__title">🧱 Materials Used for This Unit</p>
                    @if($apartment->materials->isNotEmpty())
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="num">Qty</th>
                                <th>Unit</th>
                                <th class="num">Unit Price</th>
                                <th class="num">Line Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apartment->materials as $m)
                            @php $lp = (float)($m->inventoryItem->price ?? 0) * (float)$m->quantity_needed; @endphp
                            <tr>
                                <td>{{ $m->inventoryItem->name ?? '—' }}</td>
                                <td class="num">{{ number_format($m->quantity_needed, 2) }}</td>
                                <td>{{ $m->unit ?? '—' }}</td>
                                <td class="num">${{ number_format((float)($m->inventoryItem->price ?? 0), 2) }}</td>
                                <td class="num bold">${{ number_format($lp, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total materials cost</td>
                                <td class="num">${{ number_format($materialsCost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p style="opacity:.5;font-size:13px;margin:0;">No materials recorded for this unit yet.</p>
                    @endif
                </div>

                {{-- Additional Costs (read-only) --}}
                <div class="rpt-section">
                    <p class="rpt-section__title">📋 Additional Costs for This Unit</p>
                    @if($apartment->additionalCosts->isNotEmpty())
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="num">Expected</th>
                                <th class="num">Actual</th>
                                <th class="num">Variance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apartment->additionalCosts as $c)
                            @php $v = $c->variance(); @endphp
                            <tr>
                                <td>{{ $c->description }}</td>
                                <td class="muted">{{ $c->category ?? '—' }}</td>
                                <td class="num">${{ number_format($c->expected_amount, 2) }}</td>
                                <td class="num">{{ $c->isSettled() ? '$'.number_format($c->actual_amount, 2) : '—' }}
                                </td>
                                <td class="num">
                                    @if($c->isSettled())
                                    @if($v > 0) <span class="badge badge--over">▲ ${{ number_format($v, 2) }}</span>
                                    @elseif($v < 0) <span class="badge badge--under">▼ ${{ number_format(abs($v), 2)
                                        }}</span>
                                        @else <span class="badge badge--paid">On budget</span>
                                        @endif
                                        @else —
                                        @endif
                                </td>
                                <td>
                                    @if($c->isSettled()) <span class="badge badge--paid">Settled {{
                                        $c->actual_entered_at?->format('Y-m-d') }}</span>
                                    @else <span class="badge badge--pending">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Totals</td>
                                <td class="num">${{ number_format($costsExpected, 2) }}</td>
                                <td class="num">${{ number_format($costsActual, 2) }}</td>
                                <td class="num">
                                    @php $totalV = $costsActual - $costsExpected; @endphp
                                    @if($totalV > 0) <span class="badge badge--over">▲ ${{ number_format($totalV,2)
                                        }}</span>
                                    @elseif($totalV < 0) <span class="badge badge--under">▼ ${{
                                        number_format(abs($totalV),2) }}</span>
                                        @else — @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p style="opacity:.5;font-size:13px;margin:0;">No additional costs recorded yet.</p>
                    @endif
                </div>

                {{-- Cost Summary --}}
                <div class="rpt-section">
                    <p class="rpt-section__title">📊 Cost &amp; Profit Summary</p>
                    <table class="rpt-table">
                        <tbody>
                            <tr>
                                <td>Down payment received</td>
                                <td class="num bold">${{ number_format($downPayment, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Paid invoices (cash received)</td>
                                <td class="num bold">${{ number_format($paidAmount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Materials cost</td>
                                <td class="num bold">– ${{ number_format($materialsCost, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Additional costs (actual / expected)</td>
                                <td class="num bold">– ${{ number_format($costsActual, 2) }} <span class="muted">/ ${{
                                        number_format($costsExpected, 2) }}</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL REVENUE COLLECTED</td>
                                <td class="num">${{ number_format($totalRevenue, 2) }}</td>
                            </tr>
                            <tr>
                                <td>TOTAL COST</td>
                                <td class="num">${{ number_format($totalCost, 2) }}</td>
                            </tr>
                            <tr style="background:{{ $profit >= 0 ? 'rgba(21,128,61,0.06)' : 'rgba(185,28,28,0.06)' }}">
                                <td>NET PROFIT / LOSS</td>
                                <td class="num"
                                    style="color:{{ $profit >= 0 ? '#15803d' : '#b91c1c' }};font-size:18px;">
                                    {{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @endif {{-- end apartment check --}}
            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
    <script>
        const APT_ROUTES = {!! $aptRoutesJson !!};
    function loadApartments(projId) {
        const sel = document.getElementById('aptPicker');
        sel.innerHTML = '<option value="">— Select apartment —</option>';
        (APT_ROUTES[projId] || []).forEach(a => {
            const o = document.createElement('option');
            o.value = a.url; o.textContent = a.label;
            sel.appendChild(o);
        });
    }
    function goToApartment(url) { if(url) window.location.href = url; }
    document.addEventListener('DOMContentLoaded', function(){
        const projSel = document.getElementById('projPicker');
        if(projSel.value) loadApartments(projSel.value);
    });
    </script>
</body>

</html>