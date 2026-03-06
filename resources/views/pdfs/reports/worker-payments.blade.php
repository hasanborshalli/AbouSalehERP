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

        .val-green {
            color: #059669;
        }

        .val-amber {
            color: #d97706;
        }

        .val-red {
            color: #dc2626;
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
            white-space: nowrap;
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

        .chip-red {
            background: #fee2e2;
            color: #991b1b;
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
            <h1>Worker Payments Report</h1>
            <div class="sub">Abou Saleh General Trading &nbsp;•&nbsp; Generated: {{
                now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    @php
    $totalAll = $totalPaid + $totalPending;
    $countPending = $payments->where('status','pending')->count();
    $countOverdue = $payments->where('status','pending')->filter(fn($p)=>$p->due_date < now())->count();
        @endphp

        <div class="kpi-row">
            <div class="kpi">
                <div class="lbl">Total (All)</div>
                <div class="val">${{ number_format($totalAll,2) }}</div>
            </div>
            <div class="kpi">
                <div class="lbl">Paid</div>
                <div class="val val-green">${{ number_format($totalPaid,2) }}</div>
            </div>
            <div class="kpi">
                <div class="lbl">Pending</div>
                <div class="val val-amber">${{ number_format($totalPending,2) }}</div>
            </div>
            <div class="kpi">
                <div class="lbl">Overdue Installments</div>
                <div class="val val-red">{{ $countOverdue }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Category</th>
                    <th>Project</th>
                    <th>Payment #</th>
                    <th>Due Date</th>
                    <th>Paid On</th>
                    <th>Status</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $pmt)
                @php $isLate = $pmt->status==='pending' && $pmt->due_date < now(); @endphp <tr>
                    <td style="font-weight:600;">{{ $pmt->contract?->worker?->name??'—' }}</td>
                    <td>{{ $pmt->contract?->category??'—' }}</td>
                    <td>{{ $pmt->contract?->project?->name??'—' }}</td>
                    <td style="font-family:monospace;font-size:8.5px;">{{ $pmt->payment_number }}</td>
                    <td style="{{ $isLate?'color:#dc2626;font-weight:600;':'' }}">{{
                        $pmt->due_date?->format('Y-m-d')??'—' }}</td>
                    <td>{{ $pmt->paid_at?->format('Y-m-d')??'—' }}</td>
                    <td>
                        @if($pmt->status==='paid')<span class="chip chip-green">Paid</span>
                        @elseif($isLate)<span class="chip chip-red">Overdue</span>
                        @else<span class="chip chip-amber">Pending</span>@endif
                    </td>
                    <td class="num {{ $pmt->status==='paid'?'val-green':'val-amber' }}">${{
                        number_format($pmt->amount,2) }}</td>
                    </tr>
                    @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right;">Total Paid</td>
                    <td class="num val-green">${{ number_format($totalPaid,2) }}</td>
                </tr>
                <tr>
                    <td colspan="7" style="text-align:right;">Total Pending</td>
                    <td class="num val-amber">${{ number_format($totalPending,2) }}</td>
                </tr>
            </tfoot>
        </table>
</body>

</html>