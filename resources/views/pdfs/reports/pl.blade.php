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
            font-size: 10px;
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
            width: 25%;
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

        .val-amber {
            color: #d97706;
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

        .section-title {
            font-size: 10px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
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

        .chip {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="header-row">
        <div>
            <h1>Profit & Loss Report</h1>
            <div class="sub">Abou Saleh General Trading &nbsp;•&nbsp; Period: {{ $dateFrom }} → {{ $dateTo }}
                &nbsp;•&nbsp; Generated: {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Total Revenue (Credit)</div>
            <div class="val val-green">${{ number_format($totalRevenue,2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Total Expenses (Debit)</div>
            <div class="val val-red">${{ number_format($totalExpenses,2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Net {{ $netProfit>=0?'Profit':'Loss' }}</div>
            <div class="val {{ $netProfit>=0?'val-blue':'val-amber' }}">{{ $netProfit>=0?'+':'' }}${{
                number_format(abs($netProfit),2) }}</div>
        </div>
        @if($totalRevenue>0)<div class="kpi">
            <div class="lbl">Margin</div>
            <div class="val">{{ number_format(($netProfit/$totalRevenue)*100,1) }}%</div>
        </div>@endif
    </div>

    <div class="two-col">
        <div>
            <div class="section-title">💚 Revenue by Source</div>
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th class="num">Entries</th>
                        <th class="num">Amount</th>
                        <th class="num">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenueRows as $r)
                    <tr>
                        <td><span class="chip">{{ str_replace('_',' ',$r->source_type??'—') }}</span></td>
                        <td class="num">{{ $r->entries }}</td>
                        <td class="num val-green">${{ number_format($r->total,2) }}</td>
                        <td class="num" style="color:#9ca3af;">{{ $totalRevenue>0 ?
                            number_format(($r->total/$totalRevenue)*100,1).'%' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total Revenue</td>
                        <td class="num val-green">${{ number_format($totalRevenue,2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div>
            <div class="section-title">🔴 Expenses by Source</div>
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th class="num">Entries</th>
                        <th class="num">Amount</th>
                        <th class="num">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseRows as $r)
                    <tr>
                        <td><span class="chip">{{ str_replace('_',' ',$r->source_type??'—') }}</span></td>
                        <td class="num">{{ $r->entries }}</td>
                        <td class="num val-red">${{ number_format($r->total,2) }}</td>
                        <td class="num" style="color:#9ca3af;">{{ $totalExpenses>0 ?
                            number_format(($r->total/$totalExpenses)*100,1).'%' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total Expenses</td>
                        <td class="num val-red">${{ number_format($totalExpenses,2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>

</html>