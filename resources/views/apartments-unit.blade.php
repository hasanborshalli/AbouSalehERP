<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Unit {{ $apartment->unit_number }} — {{ $apartment->project->name }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/reportsApartment.css">
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

            @if(session('success'))
            <div class="alert alert--success" data-alert>
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">✕</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            <div class="rpt">

                {{-- Header --}}
                <div
                    style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                    <a class="rpt-back" href="{{ route('apartments.project', $apartment->project_id) }}">← Back to {{
                        $apartment->project->name }}</a>
                    <a href="{{ route('reports.apartment.show', $apartment->id) }}"
                        style="text-decoration:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;background:rgba(42,127,176,0.1);color:rgba(42,127,176,0.9);">📊
                        View Report</a>
                </div>

                {{-- Hero --}}
                <div class="rpt-hero">
                    <div>
                        <h2 class="rpt-hero__title">Unit {{ $apartment->unit_number }} — {{ $apartment->project->name }}
                        </h2>
                        <div class="rpt-hero__meta">
                            Floor {{ $apartment->floor->floor_number }}
                            @if($apartment->bedrooms) · {{ $apartment->bedrooms }} bed @endif
                            @if($apartment->bathrooms) · {{ $apartment->bathrooms }} bath @endif
                            @if($apartment->area_sqm) · {{ $apartment->area_sqm }} m² @endif
                            · <strong>{{ ucfirst($apartment->status) }}</strong>
                            · Selling price: ${{ number_format($apartment->price_total, 2) }}
                        </div>
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

                {{-- ── Materials ── --}}
                <div class="rpt-section">
                    <p class="rpt-section__title">🧱 Materials Used for This Unit</p>

                    @if($apartment->materials->isNotEmpty())
                    <table class="rpt-table" style="margin-bottom:14px;">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="num">Qty</th>
                                <th>Unit</th>
                                <th class="num">Unit Price</th>
                                <th class="num">Line Cost</th>
                                <th></th>
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
                                <td>
                                    <form method="post"
                                        action="{{ route('apartments.materials.destroy', [$apartment, $m]) }}"
                                        onsubmit="return confirm('Remove this material and restore stock?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">✕ Remove</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total materials cost</td>
                                <td class="num">${{ number_format($materialsCost, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p style="opacity:.5;font-size:13px;margin:0 0 12px;">No materials recorded for this unit yet.</p>
                    @endif

                    <div class="rpt-add-form">
                        <p class="rpt-add-form__title">＋ Add material to this unit</p>
                        <form method="post" action="{{ route('apartments.materials.store', $apartment) }}">
                            @csrf
                            <div class="rpt-add-form__grid rpt-add-form__grid--mat">
                                <div>
                                    <label>Inventory Item</label>
                                    <select name="inventory_item_id" required>
                                        <option value="">Select item</option>
                                        @foreach($inventoryItems as $it)
                                        <option value="{{ $it->id }}">{{ $it->name }} (Stock: {{ $it->quantity }} {{
                                            $it->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label>Quantity</label>
                                    <input type="number" name="quantity_needed" step="0.01" min="0.01"
                                        placeholder="0.00" required>
                                </div>
                                <button type="submit" class="rpt-add-form__submit"
                                    style="align-self:flex-end;">Add</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── Additional Costs ── --}}
                <div class="rpt-section">
                    <p class="rpt-section__title">📋 Additional Costs for This Unit</p>

                    @if($apartment->additionalCosts->isNotEmpty())
                    <table class="rpt-table" style="margin-bottom:14px;">
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
                            @foreach($apartment->additionalCosts as $c)
                            @php $v = $c->variance(); $settled = $c->isSettled(); @endphp
                            <tr>
                                <td>{{ $c->description }}</td>
                                <td class="muted">{{ $c->category ?? '—' }}</td>
                                <td class="num">${{ number_format($c->expected_amount, 2) }}</td>
                                <td class="num">{{ $settled ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                                <td class="num">
                                    @if($settled)
                                    @if($v > 0) <span class="badge badge--over">▲ ${{ number_format($v, 2) }}</span>
                                    @elseif($v < 0) <span class="badge badge--under">▼ ${{ number_format(abs($v), 2)
                                        }}</span>
                                        @else <span class="badge badge--paid">On budget</span>
                                        @endif
                                        @else —
                                        @endif
                                </td>
                                <td>
                                    @if(!$settled)
                                    <form class="settle-form" method="post"
                                        action="{{ route('apartments.costs.settle', [$apartment, $c]) }}">
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
                                    <form method="post"
                                        action="{{ route('apartments.costs.destroy', [$apartment, $c]) }}"
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
                                <td class="num">${{ number_format($costsExpected, 2) }}</td>
                                <td class="num">${{ number_format($costsActual, 2) }}</td>
                                <td class="num">
                                    @php $totalV = $costsActual - $costsExpected; @endphp
                                    @if($totalV > 0) <span class="badge badge--over">▲ ${{ number_format($totalV, 2)
                                        }}</span>
                                    @elseif($totalV < 0) <span class="badge badge--under">▼ ${{
                                        number_format(abs($totalV), 2) }}</span>
                                        @else — @endif
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p style="opacity:.5;font-size:13px;margin:0 0 12px;">No additional costs recorded yet.</p>
                    @endif

                    <div class="rpt-add-form">
                        <p class="rpt-add-form__title">＋ Add expected cost for this unit</p>
                        <form method="post" action="{{ route('apartments.costs.store', $apartment) }}">
                            @csrf
                            <div class="rpt-add-form__grid">
                                <div>
                                    <label>Description</label>
                                    <input type="text" name="description" placeholder="e.g. Painting" required>
                                </div>
                                <div>
                                    <label>Category (optional)</label>
                                    <input type="text" name="category" placeholder="e.g. finishing">
                                </div>
                                <div>
                                    <label>Expected Amount ($)</label>
                                    <input type="number" name="expected_amount" step="0.01" min="0" placeholder="0.00"
                                        required>
                                </div>
                            </div>
                            <button type="submit" class="rpt-add-form__submit" style="margin-top:8px;">Add Cost</button>
                        </form>
                    </div>
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
</body>

</html>