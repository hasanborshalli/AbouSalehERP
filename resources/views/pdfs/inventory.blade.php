<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php $logoPath=public_path("img/abosaleh-logo.png");
    $logoB64=file_exists($logoPath)?base64_encode(file_get_contents($logoPath)):null; @endphp
    <style>
        @page {
            margin: 20px 18px 40px 18px;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #111827;
            margin: 0;
        }

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
            height: 38px;
        }

        h1 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .sub {
            color: #6b7280;
            font-size: 9px;
        }

        .line {
            height: 1px;
            background: #e5e7eb;
            margin: 7px 0;
        }

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
            padding: 5px 9px;
        }

        .kpi .lbl {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
        }

        .kpi .val {
            font-size: 12px;
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

        .section-title {
            font-size: 10px;
            font-weight: 700;
            margin: 8px 0 4px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        thead th {
            background: #f1f5f9;
            padding: 5px 7px;
            text-align: left;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1.5px solid #e5e7eb;
            white-space: nowrap;
        }

        thead th.num {
            text-align: right;
        }

        tbody td {
            padding: 4px 7px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8.5px;
        }

        tbody td.num {
            text-align: right;
        }

        tfoot td {
            padding: 5px 7px;
            font-weight: 800;
            border-top: 2px solid #e5e7eb;
            background: #f8fafc;
            font-size: 9px;
        }

        tfoot td.num {
            text-align: right;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.06;
            z-index: -1;
        }
    </style>
</head>

<body>
    @if($logoB64)<img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="Watermark">@endif
    <div class="header-row">
        <div>
            <h1>Inventory Report — {{ $item->name }}</h1>
            <div class="sub">Abou Saleh General Trading &nbsp;•&nbsp; Unit: {{ $item->unit }} &nbsp;•&nbsp; Generated:
                {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    @php
    $totalPurchased = (int)$purchases->sum('qty');
    $totalPurchaseCost = (float)$purchases->sum('total_cost');
    $qtyUsedProj = (float)$projectUsages->sum('quantity_needed');
    $qtyUsedApt = (float)$apartmentUsages->sum('quantity_needed');
    $totalUsed = $qtyUsedProj + $qtyUsedApt;
    $totalUsageCost = $totalUsed * $avgCost;
    @endphp

    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Total Purchased</div>
            <div class="val">{{ number_format($totalPurchased) }} {{ $item->unit }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Cost Paid</div>
            <div class="val val-red">${{ number_format($totalPurchaseCost,2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Avg Cost</div>
            <div class="val">${{ $totalPurchased>0 ? number_format($totalPurchaseCost/$totalPurchased,2) :
                number_format($item->price,2) }}/{{ $item->unit }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Total Used</div>
            <div class="val val-amber">{{ number_format($totalUsed,1) }} {{ $item->unit }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Usage Cost</div>
            <div class="val val-amber">${{ number_format($totalUsageCost,2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">In Stock</div>
            <div class="val {{ $item->quantity>0?'val-green':'val-red' }}">{{ number_format($item->quantity) }} {{
                $item->unit }}</div>
        </div>
    </div>

    {{-- Purchase History --}}
    <div class="section-title">🛒 Purchase History</div>
    @if($purchases->isEmpty())
    <p style="color:#9ca3af;font-size:9px;">No purchases recorded.</p>
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
                <td>{{ $p->vendor_name??'—' }}</td>
                <td style="font-family:monospace;">{{ $p->receipt_ref??'—' }}</td>
                <td class="num">{{ number_format($p->qty) }}</td>
                <td class="num">${{ number_format($p->unit_cost,2) }}</td>
                <td class="num val-red">${{ number_format($p->total_cost,2) }}</td>
                <td style="color:#6b7280;">{{ $p->notes??'—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td class="num">{{ number_format($totalPurchased) }}</td>
                <td></td>
                <td class="num val-red">${{ number_format($totalPurchaseCost,2) }}</td>
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
                <td style="font-weight:600;">{{ $pu->project->name??'—' }}</td>
                <td class="num">{{ number_format($pu->quantity_needed,1) }}</td>
                <td class="num val-amber">{{ number_format($pu->quantity_needed,1) }}</td>
                <td class="num val-amber">${{ number_format($pu->quantity_needed*$avgCost,2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td></td>
                <td class="num val-amber">{{ number_format($qtyUsedProj,1) }}</td>
                <td class="num val-amber">${{ number_format($qtyUsedProj*$avgCost,2) }}</td>
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
                <td>{{ $au->apartment?->project?->name??'—' }}</td>
                <td style="font-weight:600;">Unit {{ $au->apartment?->unit_number??'?' }}</td>
                <td>Floor {{ $au->apartment?->floor?->floor_number??'?' }}</td>
                <td class="num val-amber">{{ number_format($au->quantity_needed,1) }}</td>
                <td class="num val-amber">${{ number_format($au->quantity_needed*$avgCost,2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td class="num val-amber">{{ number_format($qtyUsedApt,1) }}</td>
                <td class="num val-amber">${{ number_format($qtyUsedApt*$avgCost,2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif
</body>

</html>