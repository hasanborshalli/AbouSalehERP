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
            margin: 20px 18px 45px 18px;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
        }

        .watermark {
            position: fixed;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 480px;
            opacity: 0.05;
            z-index: -1;
        }

        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-row>div {
            display: table-cell;
            vertical-align: middle;
        }

        .header-row>div:last-child {
            text-align: right;
        }

        .logo {
            height: 40px;
        }

        h1 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .muted {
            color: #6b7280;
            font-size: 9px;
        }

        .line {
            height: 1px;
            background: #e5e7eb;
            margin: 8px 0;
        }

        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .kpi-cell {
            display: table-cell;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px 10px;
            width: 25%;
        }

        .kpi-cell .lbl {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
        }

        .kpi-cell .val {
            font-size: 14px;
            font-weight: 800;
            margin-top: 1px;
        }

        .val-credit {
            color: #059669;
        }

        .val-debit {
            color: #dc2626;
        }

        .val-net {
            color: #2563eb;
        }

        .filters-box {
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            margin-bottom: 10px;
            font-size: 9px;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f1f5f9;
            padding: 6px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #374151;
            border-bottom: 1.5px solid #e5e7eb;
        }

        thead th.num {
            text-align: right;
        }

        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 9.5px;
        }

        td.num {
            text-align: right;
        }

        .dir-in {
            color: #059669;
            font-weight: 700;
        }

        .dir-out {
            color: #dc2626;
            font-weight: 700;
        }

        .chip {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
        }

        tfoot td {
            padding: 6px 8px;
            font-weight: 800;
            font-size: 10px;
            border-top: 2px solid #e5e7eb;
            background: #f8fafc;
        }

        tfoot .net-pos {
            color: #2563eb;
        }

        tfoot .net-neg {
            color: #d97706;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">
    @endif

    <div class="header-row">
        <div>
            <h1>Ledger Report</h1>
            <div class="muted">Abou Saleh General Trading &nbsp;•&nbsp; Generated: {{
                now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>
        </div>
        <div>
            @if($logoB64)
            <img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">
            @endif
        </div>
    </div>

    <div class="line"></div>

    @php
    $activeFilters = array_filter([
    $filters['direction'] ? 'Direction: ' . ($filters['direction'] === 'in' ? 'Credit' : 'Debit') : null,
    $filters['date_from'] ? 'From: ' . $filters['date_from'] : null,
    $filters['date_to'] ? 'To: ' . $filters['date_to'] : null,
    $filters['source_type'] ? 'Source: ' . str_replace('_', ' ', $filters['source_type']) : null,
    $filters['search'] ? 'Search: "' . $filters['search'] . '"' : null,
    ]);
    @endphp

    @if($activeFilters)
    <div class="filters-box">
        <b>Filters applied:</b> {{ implode(' &nbsp;|&nbsp; ', $activeFilters) }}
    </div>
    @endif

    {{-- KPIs --}}
    <div class="kpi-row">
        <div class="kpi-cell">
            <div class="lbl">Total Credit (دين)</div>
            <div class="val val-credit">${{ number_format($totalCredit, 2) }}</div>
        </div>
        <div class="kpi-cell">
            <div class="lbl">Total Debit (مدين)</div>
            <div class="val val-debit">${{ number_format($totalDebit, 2) }}</div>
        </div>
        <div class="kpi-cell">
            @php $net = $totalCredit - $totalDebit; @endphp
            <div class="lbl">Net Balance</div>
            <div class="val val-net">{{ $net >= 0 ? '+' : '' }}${{ number_format($net, 2) }}</div>
        </div>
        <div class="kpi-cell">
            <div class="lbl">Entries</div>
            <div class="val">{{ number_format($entries->count()) }}</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Direction</th>
                <th>Account</th>
                <th>Source</th>
                <th>Description</th>
                <th class="num">Amount (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $e)
            <tr>
                <td style="color:#9ca3af;">{{ $e->id }}</td>
                <td>{{ $e->posted_at->format('Y-m-d') }}</td>
                <td class="{{ $e->direction === 'in' ? 'dir-in' : 'dir-out' }}">
                    {{ $e->direction === 'in' ? '↑ Credit' : '↓ Debit' }}
                </td>
                <td>{{ $e->account->name ?? ($e->account->code ?? '—') }}</td>
                <td>
                    @if($e->source_type)<span class="chip">{{ str_replace('_', ' ', $e->source_type) }}</span>@else
                    —@endif
                </td>
                <td>{{ $e->description ?? '—' }}</td>
                <td class="num {{ $e->direction === 'in' ? 'dir-in' : 'dir-out' }}">
                    {{ $e->direction === 'in' ? '+' : '−' }}${{ number_format((float)$e->amount, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right;">Total Credit (دين)</td>
                <td class="num dir-in">+${{ number_format($totalCredit, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:right;">Total Debit (مدين)</td>
                <td class="num dir-out">−${{ number_format($totalDebit, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:right;">Net Balance</td>
                <td class="num {{ $net >= 0 ? 'net-pos' : 'net-neg' }}">
                    {{ $net >= 0 ? '+' : '' }}${{ number_format($net, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $left = "Abou Saleh General Trading • Ledger Report";
            $pdf->page_text(18, 570, $left, null, 8, array(0.42,0.45,0.50));
            $pdf->page_text(750, 570, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 8, array(0.42,0.45,0.50));
        }
    </script>
</body>

</html>