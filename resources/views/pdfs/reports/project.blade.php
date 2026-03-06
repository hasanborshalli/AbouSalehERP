<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    <style>
        @page {
            margin: 20px 18px 40px 18px;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
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
            opacity: 0.06;
            z-index: -1;
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
            font-size: 13px;
            font-weight: 800;
            margin-top: 1px;
        }

        .val-green {
            color: #059669;
        }

        .val-red {
            color: #dc2626;
        }

        .val-blue {
            color: #2563eb;
        }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            margin: 10px 0 4px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        thead th {
            background: #f1f5f9;
            padding: 5px 7px;
            text-align: left;
            font-weight: 700;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #374151;
            border-bottom: 1.5px solid #e5e7eb;
        }

        thead th.num {
            text-align: right;
        }

        tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 9px;
        }

        tbody td.num {
            text-align: right;
        }

        tfoot td {
            padding: 5px 7px;
            font-weight: 800;
            font-size: 9.5px;
            border-top: 2px solid #e5e7eb;
            background: #f8fafc;
        }

        tfoot td.num {
            text-align: right;
        }

        .badge {
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
        }

        .badge--paid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge--pending {
            background: #fef3c7;
            color: #92400e;
        }

        .two-col {
            display: table;
            width: 100%;
            border-spacing: 8px 0;
            border-collapse: separate;
        }

        .two-col>div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>

<body>

    @if($logoB64)<img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="Watermark">@endif

    <div class="header-row">
        <div>
            <h1>Project Report — {{ $project->name }}</h1>
            <div class="sub">
                Abou Saleh General Trading &nbsp;•&nbsp;
                {{ $project->city }}@if($project->area), {{ $project->area }}@endif
                @if($project->code) &nbsp;•&nbsp; Code: {{ $project->code }}@endif
                &nbsp;•&nbsp; Generated: {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}
            </div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Total Revenue Collected</div>
            <div class="val val-blue">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Total Cost (Actual)</div>
            <div class="val val-red">${{ number_format($totalCost, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Net Profit / Loss</div>
            <div class="val {{ $profit >= 0 ? 'val-green' : 'val-red' }}">{{ $profit >= 0 ? '+' : '' }}${{
                number_format($profit, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Units</div>
            <div class="val">{{ $apartments->count() }}</div>
        </div>
    </div>

    <div class="two-col">
        <div>
            <div class="section-title">🧱 Project Materials</div>
            @if($project->inventoryUsages->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="num">Qty</th>
                        <th class="num">Unit Price</th>
                        <th class="num">Line Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->inventoryUsages as $u)
                    @php $lp = (float)$u->quantity_needed * (float)($u->inventoryItem->price ?? 0); @endphp
                    <tr>
                        <td>{{ $u->inventoryItem->name ?? '—' }}</td>
                        <td class="num">{{ number_format($u->quantity_needed, 2) }}</td>
                        <td class="num">${{ number_format($u->inventoryItem->price ?? 0, 2) }}</td>
                        <td class="num">${{ number_format($lp, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="num">${{ number_format($projectMaterialsCost, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @else
            <p style="color:#9ca3af;font-size:9px;margin:0;">No project-level materials.</p>
            @endif
        </div>
        <div>
            <div class="section-title">📋 Project Additional Costs</div>
            @if($projCosts->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="num">Expected</th>
                        <th class="num">Actual</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projCosts as $c)
                    <tr>
                        <td>{{ $c->description }}</td>
                        <td style="color:#6b7280;">{{ $c->category ?? '—' }}</td>
                        <td class="num">${{ number_format($c->expected_amount, 2) }}</td>
                        <td class="num">{{ $c->isSettled() ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                        <td><span class="badge {{ $c->isSettled() ? 'badge--paid' : 'badge--pending' }}">{{
                                $c->isSettled() ? 'Settled' : 'Pending' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Totals</td>
                        <td class="num">${{ number_format($projCostsExpected, 2) }}</td>
                        <td class="num">${{ number_format($projCostsActual, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            @else
            <p style="color:#9ca3af;font-size:9px;margin:0;">No additional costs.</p>
            @endif
        </div>
    </div>

    <div class="section-title">🏘️ Apartments Summary</div>
    <table>
        <thead>
            <tr>
                <th>Unit</th>
                <th>Floor</th>
                <th>Status</th>
                <th class="num">Total Cost</th>
                <th class="num">Collected</th>
                <th class="num">Profit / Loss</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apartments as $apt)
            @php
            $mat = $apt->materials->sum(fn($m) => (float)$m->quantity_needed * (float)($m->inventoryItem->price ?? 0));
            $cost = $mat + $apt->additionalCosts->sum(fn($c) => $c->isSettled() ? (float)$c->actual_amount : 0.0);
            $coll = (float)($apt->contract?->invoices->where('status','paid')->sum('amount') ?? 0)
            + (float)($apt->contract?->down_payment ?? 0);
            $ap = $coll - $cost;
            @endphp
            <tr>
                <td>Unit {{ $apt->unit_number }}</td>
                <td>Floor {{ $apt->floor->floor_number }}</td>
                <td>{{ ucfirst($apt->status) }}</td>
                <td class="num">${{ number_format($cost, 2) }}</td>
                <td class="num">${{ number_format($coll, 2) }}</td>
                <td class="num" style="color:{{ $ap >= 0 ? '#059669' : '#dc2626' }};font-weight:700;">{{ $ap >= 0 ? '+'
                    : '' }}${{ number_format($ap, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totals</td>
                <td class="num">${{ number_format($totalCost, 2) }}</td>
                <td class="num">${{ number_format($totalRevenue, 2) }}</td>
                <td class="num" style="color:{{ $profit >= 0 ? '#059669' : '#dc2626' }};">{{ $profit >= 0 ? '+' : ''
                    }}${{ number_format($profit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>