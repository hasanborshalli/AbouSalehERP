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
            font-size: 9px;
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

        table {
            width: 100%;
            border-collapse: collapse;
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
            font-size: 9px;
            border-top: 2px solid #e5e7eb;
            background: #f8fafc;
        }

        tfoot td.num {
            text-align: right;
        }

        .chip {
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
        }

        .chip-green {
            background: #d1fae5;
            color: #065f46;
        }

        .chip-amber {
            background: #fef3c7;
            color: #92400e;
        }

        .chip-gray {
            background: #f3f4f6;
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="header-row">
        <div>
            <h1>Sales Pipeline Report</h1>
            <div class="sub">Abou Saleh General Trading &nbsp;•&nbsp; Generated: {{
                now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    @php
    $totalUnits = $apartments->count();
    $totalSold = $apartments->where('status','sold')->count();
    $totalReserved = $apartments->where('status','reserved')->count();
    $totalAvailable = $apartments->where('status','available')->count();
    $totalValue = (float)$apartments->sum('price_total');
    $totalCollected =
    $apartments->sum(fn($a)=>$a->contract?(float)$a->contract->down_payment+(float)$a->contract->invoices->where('status','paid')->sum('amount'):0);
    $totalOutstanding =
    $apartments->sum(fn($a)=>$a->contract?(float)$a->contract->invoices->whereIn('status',['pending','overdue'])->sum('amount'):0);
    @endphp

    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Total Units</div>
            <div class="val">{{ $totalUnits }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Sold</div>
            <div class="val val-green">{{ $totalSold }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Reserved</div>
            <div class="val val-amber">{{ $totalReserved }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Available</div>
            <div class="val">{{ $totalAvailable }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Portfolio Value</div>
            <div class="val">${{ number_format($totalValue,0) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Collected</div>
            <div class="val val-green">${{ number_format($totalCollected,0) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Outstanding</div>
            <div class="val val-red">${{ number_format($totalOutstanding,0) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Project</th>
                <th>Unit</th>
                <th>Beds</th>
                <th class="num">Area m²</th>
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
            $collected = $c ? (float)$c->down_payment + (float)$c->invoices->where('status','paid')->sum('amount') : 0;
            $outstanding = $c ? (float)$c->invoices->whereIn('status',['pending','overdue'])->sum('amount') : 0;
            @endphp
            <tr>
                <td style="font-weight:600;">{{ $apt->project->name??'—' }}</td>
                <td>Unit {{ $apt->unit_number??'#'.$apt->id }}</td>
                <td>{{ $apt->bedrooms??'—' }}</td>
                <td class="num">{{ $apt->area_sqm?number_format($apt->area_sqm,1):'—' }}</td>
                <td class="num">${{ $apt->price_total?number_format($apt->price_total,2):'—' }}</td>
                <td>
                    @if($apt->status==='sold')<span class="chip chip-green">Sold</span>
                    @elseif($apt->status==='reserved')<span class="chip chip-amber">Reserved</span>
                    @else<span class="chip chip-gray">Available</span>@endif
                </td>
                <td>{{ $c?->client?->name??'—' }}</td>
                <td>{{ $c?->contract_date?->format('Y-m-d')??'—' }}</td>
                <td class="num val-green">{{ $collected>0?'$'.number_format($collected,2):'—' }}</td>
                <td class="num val-red">{{ $outstanding>0?'$'.number_format($outstanding,2):'—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" style="text-align:right;">Totals</td>
                <td class="num val-green">${{ number_format($totalCollected,2) }}</td>
                <td class="num val-red">${{ number_format($totalOutstanding,2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>