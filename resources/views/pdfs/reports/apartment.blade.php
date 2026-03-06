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

        .badge--overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge--over {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge--under {
            background: #d1fae5;
            color: #065f46;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 3px 8px 3px 0;
            width: 25%;
            vertical-align: top;
        }

        .info-cell .k {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
        }

        .info-cell .v {
            font-weight: 600;
            font-size: 9px;
            margin-top: 1px;
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
            <h1>Apartment Report — Unit {{ $apartment->unit_number }} · {{ $apartment->project->name }}</h1>
            <div class="sub">
                Abou Saleh General Trading &nbsp;•&nbsp;
                Floor {{ $apartment->floor->floor_number }}
                @if($apartment->bedrooms) &nbsp;•&nbsp; {{ $apartment->bedrooms }} bed @endif
                @if($apartment->area_sqm) &nbsp;•&nbsp; {{ $apartment->area_sqm }} m² @endif
                &nbsp;•&nbsp; Selling price: ${{ number_format($apartment->price_total, 2) }}
                &nbsp;•&nbsp; Generated: {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}
            </div>
        </div>
        <div>@if($logoB64)<img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif</div>
    </div>
    <div class="line"></div>

    <div class="kpi-row">
        <div class="kpi">
            <div class="lbl">Revenue Collected</div>
            <div class="val val-blue">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Total Cost</div>
            <div class="val val-red">${{ number_format($totalCost, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Net Profit / Loss</div>
            <div class="val {{ $profit >= 0 ? 'val-green' : 'val-red' }}">{{ $profit >= 0 ? '+' : '' }}${{
                number_format($profit, 2) }}</div>
        </div>
        @if($contract)
        <div class="kpi">
            <div class="lbl">Remaining Balance</div>
            <div class="val">${{ number_format(max(0, (float)$contract->final_price - $totalRevenue), 2) }}</div>
        </div>
        @endif
    </div>

    @if($contract)
    <div class="section-title">🧾 Contract &amp; Client</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <div class="k">Client</div>
                <div class="v">{{ $contract->client->name ?? '—' }}</div>
            </div>
            <div class="info-cell">
                <div class="k">Phone</div>
                <div class="v">{{ $contract->client->phone ?? '—' }}</div>
            </div>
            <div class="info-cell">
                <div class="k">Contract Date</div>
                <div class="v">{{ $contract->contract_date ?? '—' }}</div>
            </div>
            <div class="info-cell">
                <div class="k">Final Price</div>
                <div class="v">${{ number_format($contract->final_price, 2) }}</div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="k">Down Payment</div>
                <div class="v">${{ number_format($downPayment, 2) }}</div>
            </div>
            <div class="info-cell">
                <div class="k">Installments</div>
                <div class="v">{{ $contract->installment_months }} × ${{ number_format($contract->installment_amount, 2)
                    }}</div>
            </div>
            <div class="info-cell">
                <div class="k">Paid Invoices</div>
                <div class="v">{{ $paidInvoices->count() }} / {{ $invoices->count() }}</div>
            </div>
            <div class="info-cell">
                <div class="k">Status</div>
                <div class="v">{{ ucfirst($apartment->status) }}</div>
            </div>
        </div>
    </div>
    @endif

    <div class="two-col">
        <div>
            <div class="section-title">🧱 Materials</div>
            @if($apartment->materials->isNotEmpty())
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
                    @foreach($apartment->materials as $m)
                    @php $lp = (float)$m->quantity_needed * (float)($m->inventoryItem->price ?? 0); @endphp
                    <tr>
                        <td>{{ $m->inventoryItem->name ?? '—' }}</td>
                        <td class="num">{{ number_format($m->quantity_needed, 2) }}</td>
                        <td class="num">${{ number_format($m->inventoryItem->price ?? 0, 2) }}</td>
                        <td class="num">${{ number_format($lp, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="num">${{ number_format($materialsCost, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @else
            <p style="color:#9ca3af;font-size:9px;margin:0;">No materials recorded.</p>
            @endif
        </div>
        <div>
            <div class="section-title">📋 Additional Costs</div>
            @if($apartment->additionalCosts->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="num">Expected</th>
                        <th class="num">Actual</th>
                        <th>Variance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apartment->additionalCosts as $c)
                    @php $v = $c->variance(); @endphp
                    <tr>
                        <td>{{ $c->description }}</td>
                        <td style="color:#6b7280;">{{ $c->category ?? '—' }}</td>
                        <td class="num">${{ number_format($c->expected_amount, 2) }}</td>
                        <td class="num">{{ $c->isSettled() ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                        <td>
                            @if($c->isSettled())
                            @if($v > 0)<span class="badge badge--over">▲ ${{ number_format($v, 2) }}</span>
                            @elseif($v < 0)<span class="badge badge--under">▼ ${{ number_format(abs($v), 2) }}</span>
                                @else<span class="badge badge--paid">On budget</span>@endif
                                @else —
                                @endif
                        </td>
                        <td><span class="badge {{ $c->isSettled() ? 'badge--paid' : 'badge--pending' }}">{{
                                $c->isSettled() ? 'Settled' : 'Pending' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Totals</td>
                        <td class="num">${{ number_format($costsExpected, 2) }}</td>
                        <td class="num">${{ number_format($costsActual, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
            @else
            <p style="color:#9ca3af;font-size:9px;margin:0;">No additional costs recorded.</p>
            @endif
        </div>
    </div>

    @if($invoices->isNotEmpty())
    <div class="section-title">💰 Invoices</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th class="num">Amount</th>
                <th class="num">Late Fee</th>
                <th>Status</th>
                <th>Paid At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices->sortBy('issue_date') as $inv)
            <tr>
                <td>{{ $inv->invoice_number ?? $inv->id }}</td>
                <td>{{ $inv->issue_date }}</td>
                <td>{{ $inv->due_date }}</td>
                <td class="num">${{ number_format($inv->amount, 2) }}</td>
                <td class="num">{{ $inv->late_fee_amount > 0 ? '+$'.number_format($inv->late_fee_amount, 2) : '—' }}
                </td>
                <td><span class="badge badge--{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                <td>{{ $inv->paid_at ? \Carbon\Carbon::parse($inv->paid_at)->format('Y-m-d') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Paid</td>
                <td class="num">${{ number_format($paidAmount, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
    @endif

</body>

</html>