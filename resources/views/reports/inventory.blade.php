<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory Report{{ $item ? ' — '.$item->name : '' }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
    <link rel="stylesheet" href="/css/reportsInventory.css">

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

                {{-- Header --}}
                <div class="rpt-head">
                    <div>
                        <a class="rpt-back" href="{{ route('reports.index') }}">← Reports</a>
                        <h1 class="rpt-title" style="margin-top:4px;">📦 Inventory Report</h1>
                        <div class="rpt-subtitle">
                            {{ $item ? $item->name.($item->unit ? ' ('.$item->unit.')' : '') : 'Select an item for full
                            details' }}
                        </div>
                    </div>
                    @if($item)
                    <div class="rpt-exports">
                        @php $q = http_build_query(request()->only(['item_id','date_from','date_to'])); @endphp
                        <a class="exp-btn exp-btn--excel"
                            href="{{ route('reports.export.excel','inventory') }}?{{ $q }}">⬇ Excel</a>
                        <a class="exp-btn exp-btn--pdf" href="{{ route('reports.export.pdf','inventory') }}?{{ $q }}"
                            target="_blank">⬇ PDF</a>
                    </div>
                    @endif
                </div>

                {{-- Filters (dates — only shown when item selected) --}}
                @if($item)
                <div class="rpt-filters">
                    <form method="GET">
                        <input type="hidden" name="item_id" value="{{ $itemId }}">
                        <div class="lf-group"><label>Purchase From</label><input type="date" name="date_from"
                                value="{{ $dateFrom }}"></div>
                        <div class="lf-group"><label>Purchase To</label><input type="date" name="date_to"
                                value="{{ $dateTo }}"></div>
                        <button type="submit" class="lf-btn lf-btn--apply">Apply</button>
                        <a href="{{ route('reports.inventory') }}?item_id={{ $itemId }}"
                            class="lf-btn lf-btn--clear">Clear dates</a>
                    </form>
                </div>

                {{-- KPIs --}}
                <div class="rpt-kpis rpt-kpis--inventory">
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Total Purchased</div>
                        <div class="lkpi__val">{{ number_format($totalPurchased) }} {{ $item->unit }}</div>
                        <div class="lkpi__sub">{{ $purchases->count() }} purchase orders</div>
                    </div>
                    <div class="lkpi lkpi--red">
                        <div class="lkpi__label">Total Cost Paid</div>
                        <div class="lkpi__val">${{ number_format($totalPurchaseCost,2) }}</div>
                        @if($totalPurchased>0)<div class="lkpi__sub">avg ${{
                            number_format($totalPurchaseCost/$totalPurchased,2) }}/{{ $item->unit }}</div>@endif
                    </div>
                    <div class="lkpi lkpi--amber">
                        <div class="lkpi__label">Total Used</div>
                        <div class="lkpi__val">{{ number_format($totalQuantityUsed,1) }} {{ $item->unit }}</div>
                        <div class="lkpi__sub">across projects & apartments</div>
                    </div>
                    <div class="lkpi lkpi--amber">
                        <div class="lkpi__label">Usage Cost</div>
                        <div class="lkpi__val">${{ number_format($totalUsageCost,2) }}</div>
                        <div class="lkpi__sub">at avg purchase cost</div>
                    </div>
                    <div class="lkpi {{ $item->quantity > 0 ? 'lkpi--green' : 'lkpi--red' }}">
                        <div class="lkpi__label">In Stock</div>
                        <div class="lkpi__val">{{ number_format($item->quantity) }} {{ $item->unit }}</div>
                        <div class="lkpi__sub">current inventory</div>
                    </div>
                    <div class="lkpi lkpi--blue">
                        <div class="lkpi__label">Received (In-Kind)</div>
                        <div class="lkpi__val">{{ number_format($totalInKindQty, 1) }} {{ $item->unit }}</div>
                        <div class="lkpi__sub">${{ number_format($totalInKindValue, 2) }} est. value · {{
                            $inKindReceipts->count() }} receipt(s)</div>
                    </div>
                    <div class="lkpi">
                        <div class="lkpi__label">Current Price</div>
                        <div class="lkpi__val">${{ number_format($item->price,2) }}</div>
                        <div class="lkpi__sub">per {{ $item->unit }}</div>
                    </div>
                </div>
                @endif

                {{-- Main layout: sidebar + detail --}}
                <div class="inv-layout">

                    {{-- Item list sidebar --}}
                    <div class="inv-sidebar">
                        <div class="inv-sidebar__head">
                            <span>All Items ({{ $summary->count() }})</span>
                        </div>
                        <div style="max-height:600px;overflow-y:auto;">
                            @foreach($summary as $row)
                            <a class="inv-item-row {{ $itemId==$row->id ? 'active' : '' }}"
                                href="{{ route('reports.inventory') }}?item_id={{ $row->id }}">
                                <div style="flex:1;min-width:0;">
                                    <div class="inv-item-row__name">{{ $row->name }}</div>
                                    <div class="inv-item-row__meta">
                                        Stock: {{ $row->qty_in_stock }} {{ $row->unit }}
                                        @if($row->deleted_at) · <span style="color:#dc2626;">Deleted</span>@endif
                                    </div>
                                    <div class="stock-bar-wrap">
                                        @php $pct = $row->qty_bought>0 ? min(100,
                                        ($row->qty_in_stock/$row->qty_bought)*100) : 0; @endphp
                                        <div class="stock-bar-fill"
                                            style="width:{{ $pct }}%;background:{{ $pct<20?'#dc2626':($pct<50?'#d97706':'#059669') }};">
                                        </div>
                                    </div>
                                </div>
                                <div class="inv-item-row__cost">${{ number_format($row->total_cost,0) }}</div>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Detail panel --}}
                    <div class="inv-detail">
                        @if(!$item)
                        <div class="inv-select-prompt">
                            <div style="font-size:40px;margin-bottom:12px;">📦</div>
                            Click an item on the left to see full purchase history, usage and project breakdown.
                        </div>

                        {{-- Summary table of all items --}}
                        <div class="section-card">
                            <div class="section-card__head">
                                <h3>📋 All Items Summary</h3>
                            </div>
                            <div class="section-card__table-wrap">
                                <table class="rpt-table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="num">Qty Bought</th>
                                            <th class="num">Cost Paid</th>
                                            <th class="num">Qty Used</th>
                                            <th class="num">Usage Cost</th>
                                            <th class="num" style="color:#2563eb;">Received (In-Kind)</th>
                                            <th class="num">In Stock</th>
                                            <th class="num">Unit Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary as $row)
                                        <tr>
                                            <td>
                                                <a href="{{ route('reports.inventory') }}?item_id={{ $row->id }}"
                                                    style="font-weight:600;color:rgba(42,127,176,.9);text-decoration:none;">{{
                                                    $row->name }}</a>
                                                @if($row->deleted_at) <span
                                                    class="chip chip--red chip--square">Deleted</span>@endif
                                            </td>
                                            <td class="num">{{ number_format($row->qty_bought) }} {{ $row->unit }}</td>
                                            <td class="num val-red">${{ number_format($row->total_cost,2) }}</td>
                                            <td class="num val-amber">{{ number_format($row->qty_used,1) }} {{
                                                $row->unit }}
                                            </td>
                                            <td class="num val-amber">${{ number_format($row->usage_cost,2) }}</td>
                                            <td class="num" style="color:#2563eb;">
                                                @if(($row->qty_in_kind ?? 0) > 0)
                                                {{ number_format($row->qty_in_kind,1) }} {{ $row->unit }}<br><small>${{
                                                    number_format($row->val_in_kind,0) }}</small>
                                                @else
                                                —
                                                @endif
                                            </td>
                                            <td class="num {{ $row->qty_in_stock>0?'val-green':'val-red' }}">{{
                                                number_format($row->qty_in_stock) }}</td>
                                            <td class="num">${{ number_format($row->current_price,2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="border-top:2px solid #e5e7eb;background:#f8fafc;font-weight:800;">
                                            <td>Total ({{ $summary->count() }} items)</td>
                                            <td class="num">—</td>
                                            <td class="num val-red">${{ number_format($grandTotalCost,2) }}</td>
                                            <td class="num">—</td>
                                            <td class="num val-amber">${{ number_format($grandUsageCost,2) }}</td>
                                            <td class="num" style="color:#2563eb;">${{
                                                number_format($summary->sum('val_in_kind'),2) }}</td>
                                            <td class="num">—</td>
                                            <td class="num">—</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        @else

                        {{-- Purchase History --}}
                        <div class="section-card">
                            <div class="section-card__head">
                                <h3>🛒 Purchase History</h3>
                                <span style="font-size:13px;font-weight:800;color:#dc2626;">${{
                                    number_format($totalPurchaseCost,2) }}</span>
                            </div>
                            @if($purchases->isEmpty())
                            <div class="empty-state">No purchases recorded for this item{{ $dateFrom||$dateTo ? ' in
                                this date range' : '' }}.</div>
                            @else
                            <div class="section-card__table-wrap">
                                <table class="rpt-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Vendor</th>
                                            <th>Receipt Ref</th>
                                            <th class="num">Qty</th>
                                            <th class="num">Unit Cost</th>
                                            <th class="num">Total Cost</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchases as $pur)
                                        <tr>
                                            <td>{{ $pur->purchase_date->format('Y-m-d') }}</td>
                                            <td>{{ $pur->vendor_name ?? '—' }}</td>
                                            <td style="font-family:monospace;font-size:12px;">{{ $pur->receipt_ref ??
                                                '—' }}
                                            </td>
                                            <td class="num">{{ number_format($pur->qty) }} {{ $item->unit }}</td>
                                            <td class="num">${{ number_format($pur->unit_cost,2) }}</td>
                                            <td class="num val-red">${{ number_format($pur->total_cost,2) }}</td>
                                            <td style="color:rgba(0,0,0,.5);font-size:12px;">{{ $pur->notes ?? '—' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="border-top:2px solid #e5e7eb;background:#f8fafc;font-weight:800;">
                                            <td colspan="3">Total</td>
                                            <td class="num">{{ number_format($totalPurchased) }} {{ $item->unit }}</td>
                                            <td class="num">—</td>
                                            <td class="num val-red">${{ number_format($totalPurchaseCost,2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @endif
                        </div>

                        {{-- Project Usage --}}
                        @if($projectUsages->isNotEmpty())
                        <div class="section-card">
                            <div class="section-card__head">
                                <h3>🏗️ Usage by Project</h3>
                                <span style="font-size:13px;font-weight:800;color:#d97706;">{{
                                    number_format($projectUsages->sum('quantity_needed'),1) }} {{ $item->unit }}</span>
                            </div>
                            <div class="section-card__table-wrap">
                                <table class="rpt-table">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th class="num">Qty Assigned</th>
                                            <th class="num">Est. Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($projectUsages as $pu)
                                        @php
                                        $avgCost = $totalPurchased>0 ? $totalPurchaseCost/$totalPurchased :
                                        (float)$item->price;
                                        $estCost = (float)$pu->quantity_needed * $avgCost;
                                        @endphp
                                        <tr>
                                            <td style="font-weight:600;">{{ $pu->project->name ?? '—' }}</td>
                                            <td class="num val-amber">{{ number_format($pu->quantity_needed,1) }} {{
                                                $pu->unit }}</td>
                                            <td class="num val-amber">${{ number_format($estCost,2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- Apartment Usage --}}
                        @if($apartmentUsages->isNotEmpty())
                        <div class="section-card">
                            <div class="section-card__head">
                                <h3>🏠 Usage by Apartment</h3>
                                <span style="font-size:13px;font-weight:800;color:#d97706;">{{
                                    number_format($apartmentUsages->sum('quantity_needed'),1) }} {{ $item->unit
                                    }}</span>
                            </div>
                            <div class="section-card__table-wrap">
                                <table class="rpt-table">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th>Apartment</th>
                                            <th>Floor</th>
                                            <th class="num">Qty Needed</th>
                                            <th class="num">Est. Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($apartmentUsages as $au)
                                        @php
                                        $avgCost = $totalPurchased>0 ? $totalPurchaseCost/$totalPurchased :
                                        (float)$item->price;
                                        $estCost = (float)$au->quantity_needed * $avgCost;
                                        @endphp
                                        <tr>
                                            <td>{{ $au->apartment?->project?->name ?? '—' }}</td>
                                            <td style="font-weight:600;">Unit {{ $au->apartment?->unit_number ?? '—' }}
                                            </td>
                                            <td>Floor {{ $au->apartment?->floor?->floor_number ?? '—' }}</td>
                                            <td class="num val-amber">{{ number_format($au->quantity_needed,1) }} {{
                                                $au->unit }}</td>
                                            <td class="num val-amber">${{ number_format($estCost,2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- In-Kind Receipts --}}
                        @if($inKindReceipts->isNotEmpty())
                        <div class="section-card">
                            <div class="section-card__head">
                                <h3>📦 Received from Clients (In-Kind Payments)</h3>
                                <span style="font-size:13px;font-weight:800;color:#2563eb;">{{
                                    number_format($totalInKindQty,1) }} {{ $item->unit }} · ${{
                                    number_format($totalInKindValue,2) }}</span>
                            </div>
                            <div class="section-card__table-wrap">
                                <table class="rpt-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Apartment</th>
                                            <th>Invoice / Contract</th>
                                            <th class="num">Qty Received</th>
                                            <th class="num">Unit Price</th>
                                            <th class="num">Total Value</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($inKindReceipts as $ik)
                                        @php
                                        $ikContract = $ik->payment?->contract;
                                        $ikClient = $ikContract?->client;
                                        $ikApt = $ikContract?->apartment;
                                        $ikInvoice = $ik->payment?->invoice;
                                        @endphp
                                        <tr>
                                            <td>{{ $ik->payment?->payment_date?->format('Y-m-d') ?? '—' }}</td>
                                            <td style="font-weight:600;">{{ $ikClient?->name ?? '—' }}</td>
                                            <td>{{ $ikApt ? 'Unit '.$ikApt->unit_number : '—' }}</td>
                                            <td style="font-family:monospace;font-size:12px;">
                                                {{ $ikInvoice ? '#'.$ikInvoice->invoice_number : 'Full contract
                                                #'.($ikContract?->id ?? '—') }}
                                            </td>
                                            <td class="num val-green">{{ number_format($ik->quantity, 3) }} {{
                                                $item->unit }}</td>
                                            <td class="num">${{ number_format($ik->unit_price_snapshot, 2) }}</td>
                                            <td class="num" style="font-weight:700;color:#2563eb;">${{
                                                number_format($ik->total_value, 2) }}</td>
                                            <td style="color:rgba(0,0,0,.5);font-size:12px;">{{ $ik->notes ?? '—' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="border-top:2px solid #e5e7eb;background:#f8fafc;font-weight:800;">
                                            <td colspan="4">Total</td>
                                            <td class="num val-green">{{ number_format($totalInKindQty, 1) }} {{
                                                $item->unit }}</td>
                                            <td class="num">—</td>
                                            <td class="num" style="color:#2563eb;">${{ number_format($totalInKindValue,
                                                2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($projectUsages->isEmpty() && $apartmentUsages->isEmpty())
                        <div class="section-card">
                            <div class="empty-state">This item has not been assigned to any project or apartment yet.
                            </div>
                        </div>
                        @endif

                        @endif {{-- end $item check --}}
                    </div>{{-- end inv-detail --}}
                </div>{{-- end inv-layout --}}

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
</body>

</html>