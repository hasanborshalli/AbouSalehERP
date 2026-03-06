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
            font-size: 13px;
            font-weight: 800;
            margin-top: 1px;
        }

        .val-red {
            color: #dc2626;
        }

        .two-col {
            display: table;
            width: 100%;
            border-spacing: 10px 0;
            border-collapse: separate;
        }

        .two-col>div {
            display: table-cell;
            vertical-align: top;
        }

        .two-col>div:first-child {
            width: 220px;
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
            border-top: 2px solid #e5e7eb;
            background: #f8fafc;
        }

        tfoot td.num {
            text-align: right;
        }

        .cat-row {
            display: table;
            width: 100%;
            padding: 4px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .cat-row>span {
            display: table-cell;
            font-size: 9px;
        }

        .cat-row>span:last-child {
            text-align: right;
            font-weight: 700;
            color: #dc2626;
        }

        .bar-wrap {
            height: 3px;
            background: #fee2e2;
            border-radius: 2px;
            margin-top: 2px;
        }

        .bar-fill {
            height: 100%;
            background: #dc2626;
            border-radius: 2px;
        }

        .chip {
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            background: #f3f4f6;
            color: #374151;
            text-transform: capitalize;
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
            <h1>Operating Expenses Report</h1>
            <div class="sub">Abou Saleh General Trading &nbsp;•&nbsp; {{ $expenses->count() }} records &nbsp;•&nbsp;
                Generated: {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Total Expenses</div>
            <div class="val val-red">${{ number_format($totalAmount,2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Categories</div>
            <div class="val">{{ $byCategory->count() }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Records</div>
            <div class="val">{{ $expenses->count() }}</div>
        </div>
        @if($byCategory->isNotEmpty())
        <div class="kpi">
            <div class="lbl">Top Category</div>
            <div class="val" style="font-size:11px;text-transform:capitalize;">{{ $byCategory->keys()->first() }}</div>
        </div>
        @endif
    </div>

    <div class="two-col">
        <div>
            <div class="section-title">By Category</div>
            @foreach($byCategory as $cat => $amt)
            <div class="cat-row">
                <span style="text-transform:capitalize;">{{ $cat }}</span>
                <span>${{ number_format($amt,2) }}</span>
            </div>
            <div class="bar-wrap">
                <div class="bar-fill" style="width:{{ $totalAmount>0?($amt/$totalAmount)*100:0 }}%;"></div>
            </div>
            @endforeach
        </div>
        <div>
            <div class="section-title">All Expenses</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $exp)
                    <tr>
                        <td>{{ $exp->expense_date->format('Y-m-d') }}</td>
                        <td><span class="chip">{{ $exp->category }}</span></td>
                        <td>{{ $exp->description??'—' }}</td>
                        <td class="num val-red">${{ number_format($exp->amount,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;">Total</td>
                        <td class="num val-red">${{ number_format($totalAmount,2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>

</html>