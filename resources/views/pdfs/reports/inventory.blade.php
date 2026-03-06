<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $totalPurchased = (int) $purchases->sum('qty');
    $totalPurchaseCost = (float) $purchases->sum('total_cost');
    $qtyUsedProj = (float) $projectUsages->sum('quantity_needed');
    $qtyUsedApt = (float) $apartmentUsages->sum('quantity_needed');
    $totalUsed = $qtyUsedProj + $qtyUsedApt;
    $totalUsageCost = $totalUsed * $avgCost;
    $inKindReceipts = $inKindReceipts ?? collect();
    $totalInKindQty = $totalInKindQty ?? 0;
    $totalInKindValue = $totalInKindValue ?? 0;
    @endphp
    <style>
        @page {
            margin: 20px 18px 40px 18px;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
            margin: 0;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.05;
            z-index: -1;
        }

        /* Header */
        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .header-row>div {
            display: table-cell;
            vertical-align: middle;
        }

        .header-row>div:last-child {
            text-align: right;
        }

        .logo {
            height: 36px;
        }

        h1 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .sub {
            color: #6b7280;
            font-size: 8.5px;
        }

        .line {
            height: 1px;
            background: #e5e7eb;
            margin: 7px 0;
        }

        /* KPIs */
        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: separate;
            border-spacing: 5px 0;
        }

        .kpi {
            display: table-cell;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 5px 8px;
        }

        .kpi .lbl {
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .03em;
        }

        .kpi .val {
            font-size: 11px;
            font-weight: 800;
            margin-top: 1px;
        }

        .val-green {
            color: #059669;
        }

        .val-red {
            color: #dc2626;
        }

        .val-amber {
            color: #d97706;
        }

        .val-blue {
            color: #2563eb;
        }

        /* Sections */
        .section-title {
            font-size: 9px;
            font-weight: 700;
            margin: 10px 0 4px;
            color: #1e3a5f;
            border-bottom: 1.5px solid #1e3a5f;
            padding-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .section-title.inkind {
            color: #2563eb;
            border-color: #2563eb;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        thead th {
            background: #f1f5f9;
            padding: 4px 6px;
            text-align: left;
            font-weight: 700;
            font-size: 7.5px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1.5px solid #d1d5db;
            white-space: nowrap;
        }

        thead th.num {
            text-align: right;
        }

        tbody td {
            padding: 3px 6px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8px;
        }

        tbody td.num {
            text-align: right;
        }

        tfoot td {
            padding: 4px 6px;
            font-weight: 800;
            border-top: 2px solid #e5e7eb;
            background: #f8fafc;
            font-size: 8.5px;
        }

        tfoot td.num {
            text-align: right;
        }

        /* In-kind table accent */
        .inkind-thead th {
            background: #eff6ff;
            color: #1e40af;
            border-bottom-color: #bfdbfe;
        }

        .inkind-tfoot td {
            background: #eff6ff;
            border-top-color: #bfdbfe;
        }

        /* Empty */
        .empty {
            color: #9ca3af;
            font-size: 8.5px;
            font-style: italic;
            margin: 4px 0 10px 4px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 14px;
            left: 18px;
            right: 18px;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
            display: table;
            width: calc(100% - 36px);
        }

        .footer-l {
            display: table-cell;
        }

        .footer-r {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>

<body>
    @if($logoB64)<img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif

    {{-- Header --}}
    <div class="header-row">
        <div>
            <h1>Inventory Report — {{ $item->name }}</h1>
            <div class="sub">
                Abou Saleh General Trading &nbsp;•&nbsp;
                Unit: {{ $item->unit }} &nbsp;•&nbsp;
                Generated: {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}
            </div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    {{-- KPIs --}}
    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Total Purchased</div>
            <div class="val">{{ number_format($totalPurchased) }} {{ $item->unit }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Cost Paid</div>
            <div class="val val-red">${{ number_format($totalPurchaseCost, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Avg Cost / {{ $item->unit }}</div>
            <div class="val">${{ $totalPurchased > 0 ? number_format($totalPurchaseCost/$totalPurchased, 2) :
                number_format($item->price, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Total Used</div>
            <div class="val val-amber">{{ number_format($totalUsed, 1) }} {{ $item->unit }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Usage Cost</div>
            <div class="val val-amber">${{ number_format($totalUsageCost, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">In Stock</div>
            <div class="val {{ $item->quantity > 0 ? 'val-green' : 'val-red' }}">{{ number_format($item->quantity) }} {{
                $item->unit }}</div>
        </div>
        @if($totalInKindQty > 0)
        <div class="kpi">
            <div class="lbl">Received (In-Kind)</div>
            <div class="val val-blue">{{ number_format($totalInKindQty, 1) }} {{ $item->unit }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">In-Kind Value</div>
            <div class="val val-blue">${{ number_format($totalInKindValue, 2) }}</div>
        </div>
        @endif
    </div>

    {{-- Purchase History --}}
    <div class="section-title">🛒 Purchase History</div>
    @if($purchases->isEmpty())
    <div class="empty">No purchases recorded.</div>
    @else
    <table>
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
            @foreach($purchases as $p)
            <tr>
                <td>{{ $p->purchase_date->format('Y-m-d') }}</td>
                <td>{{ $p->vendor_name ?? '—' }}</td>
                <td style="font-family:monospace;">{{ $p->receipt_ref ?? '—' }}</td>
                <td class="num">{{ number_format($p->qty) }}</td>
                <td class="num">${{ number_format($p->unit_cost, 2) }}</td>
                <td class="num val-red">${{ number_format($p->total_cost, 2) }}</td>
                <td style="color:#6b7280;">{{ $p->notes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td class="num">{{ number_format($totalPurchased) }}</td>
                <td></td>
                <td class="num val-red">${{ number_format($totalPurchaseCost, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Project Usage --}}
    @if($projectUsages->isNotEmpty())
    <div class="section-title">🏗️ Usage by Project</div>
    <table>
        <thead>
            <tr>
                <th>Project</th>
                <th class="num">Qty Assigned</th>
                <th class="num">Est. Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projectUsages as $pu)
            <tr>
                <td style="font-weight:600;">{{ $pu->project->name ?? '—' }}</td>
                <td class="num val-amber">{{ number_format($pu->quantity_needed, 1) }} {{ $item->unit }}</td>
                <td class="num val-amber">${{ number_format($pu->quantity_needed * $avgCost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="num val-amber">{{ number_format($qtyUsedProj, 1) }} {{ $item->unit }}</td>
                <td class="num val-amber">${{ number_format($qtyUsedProj * $avgCost, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Apartment Usage --}}
    @if($apartmentUsages->isNotEmpty())
    <div class="section-title">🏠 Usage by Apartment</div>
    <table>
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
            <tr>
                <td>{{ $au->apartment?->project?->name ?? '—' }}</td>
                <td style="font-weight:600;">Unit {{ $au->apartment?->unit_number ?? '?' }}</td>
                <td>Floor {{ $au->apartment?->floor?->floor_number ?? '?' }}</td>
                <td class="num val-amber">{{ number_format($au->quantity_needed, 1) }} {{ $item->unit }}</td>
                <td class="num val-amber">${{ number_format($au->quantity_needed * $avgCost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td class="num val-amber">{{ number_format($qtyUsedApt, 1) }} {{ $item->unit }}</td>
                <td class="num val-amber">${{ number_format($qtyUsedApt * $avgCost, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- In-Kind Receipts --}}
    @if($inKindReceipts->isNotEmpty())
    <div class="section-title inkind">📦 Received from Clients (In-Kind Payments)</div>
    <table>
        <thead class="inkind-thead">
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
            $ikInvoice = $ik->payment?->invoice;
            @endphp
            <tr>
                <td>{{ $ik->payment?->payment_date?->format('Y-m-d') ?? '—' }}</td>
                <td style="font-weight:600;">{{ $ikContract?->client?->name ?? '—' }}</td>
                <td>{{ $ikContract?->apartment ? 'Unit '.$ikContract->apartment->unit_number : '—' }}</td>
                <td style="font-family:monospace;font-size:7.5px;">
                    {{ $ikInvoice ? '#'.$ikInvoice->invoice_number : 'Contract #'.($ikContract?->id ?? '—') }}
                </td>
                <td class="num val-blue">{{ number_format($ik->quantity, 3) }} {{ $item->unit }}</td>
                <td class="num">${{ number_format($ik->unit_price_snapshot, 2) }}</td>
                <td class="num val-blue" style="font-weight:700;">${{ number_format($ik->total_value, 2) }}</td>
                <td style="color:#6b7280;">{{ $ik->notes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="inkind-tfoot">
            <tr>
                <td colspan="4">Total Received</td>
                <td class="num val-blue">{{ number_format($totalInKindQty, 1) }} {{ $item->unit }}</td>
                <td></td>
                <td class="num val-blue">${{ number_format($totalInKindValue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-l">Abou Saleh General Trading &nbsp;•&nbsp; Confidential</div>
        <div class="footer-r">{{ $item->name }} Inventory Report &nbsp;•&nbsp; {{ now()->format('Y-m-d') }}</div>
    </div>
</body>

</html>