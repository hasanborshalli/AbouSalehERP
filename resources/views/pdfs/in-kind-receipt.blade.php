<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $signaturePath = public_path('img/abousaleh-signature.png');
    $signatureB64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;
    $contract = $payment->contract;
    $client = $contract->client;
    @endphp
    <style>
        @page {
            margin: 32px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0b2545;
        }

        .watermark {
            position: fixed;
            left: 50%;
            top: 55%;
            transform: translate(-50%, -50%);
            width: 480px;
            opacity: 0.06;
            z-index: -1;
        }

        .logo-top {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo-top img {
            width: 110px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header .title {
            margin: 0;
            font-size: 14px;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .contact-row {
            display: table;
            width: 100%;
            margin: 8px 0 14px;
        }

        .contact-col {
            display: table-cell;
            width: 50%;
            font-size: 11px;
            vertical-align: top;
        }

        .voucher-bar {
            background: #1e3a5f;
            color: #fff;
            padding: 10px 14px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 18px;
            text-align: center;
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .col {
            display: table-cell;
            width: 100%;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
        }

        .wide-line {
            border-bottom: 1px dotted #333;
            display: inline-block;
            width: 100%;
            padding-bottom: 2px;
            margin-top: 4px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }

        .items-table th {
            background: #1e3a5f;
            color: #fff;
            padding: 7px 10px;
            font-size: 11px;
            text-align: left;
        }

        .items-table td {
            padding: 6px 10px;
            font-size: 11px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tr:last-child td {
            border-bottom: 2px solid #1e3a5f;
        }

        .total-row td {
            font-weight: bold;
            background: #f0f4f8;
        }

        .badge-inkind {
            display: inline-block;
            background: #1e3a5f;
            color: #fff;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .sig-col {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }

        .sig-col-right {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            text-align: right;
        }

        .signature-img {
            position: relative;
            top: -8px;
            height: 40px;
            max-width: 220px;
            display: inline-block;
            vertical-align: bottom;
        }

        .stamp-box {
            width: 90px;
            height: 90px;
            border: 1px solid #000;
            margin-top: 16px;
            display: inline-block;
        }

        .info-note {
            font-size: 10px;
            color: #6b7280;
            margin-top: 12px;
            border-top: 1px dashed #e5e7eb;
            padding-top: 8px;
        }

        .footer-line {
            margin-top: 26px;
            border-top: 3px solid #1e3a5f;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">
    @endif

    <div class="logo-top">
        @if($logoB64)
        <img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">
        @endif
    </div>

    <div class="header">
        <div class="title">ABOU SALEH GENERAL TRADING</div>
    </div>

    <div class="contact-row">
        <div class="contact-col">Address: ___________________________<br>Email: info@abousaleh.me</div>
        <div class="contact-col" style="text-align:right;">Tel: +961 71 999 219<br>www.abousaleh.me</div>
    </div>

    <div class="voucher-bar">IN-KIND PAYMENT RECEIPT</div>

    <div class="row">
        <div class="col">
            <span class="label">Receipt No:</span>
            <span style="margin-left:8px;">{{ $receiptNo }}</span>
            &nbsp;&nbsp;&nbsp;
            <span class="badge-inkind">IN-KIND</span>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <span class="label">Date:</span>
            <span style="margin-left:8px;">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <span class="label">Received From:</span>
            <div class="wide-line">{{ $client->name ?? '—' }} &nbsp;|&nbsp; Phone: {{ $client->phone ?? '—' }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <span class="label">For:</span>
            <div class="wide-line">
                @if($payment->invoice)
                Payment of Invoice #{{ $payment->invoice->invoice_number }}
                — Apartment {{ $contract->apartment->unit_number ?? '' }}
                — {{ $contract->project->name ?? '' }}
                @else
                Full apartment purchase (in-kind)
                — Apartment {{ $contract->apartment->unit_number ?? '' }}
                — {{ $contract->project->name ?? '' }}
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <span class="label">Payment Method:</span>
            <span style="margin-left:8px;">In-Kind (Inventory Items received from client)</span>
        </div>
    </div>

    {{-- Items table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Value</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->items as $i => $line)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $line->inventoryItem->name ?? '—' }}</td>
                <td>{{ $line->quantity }} {{ $line->inventoryItem->unit ?? '' }}</td>
                <td>${{ number_format($line->unit_price_snapshot, 2) }}</td>
                <td>${{ number_format($line->total_value, 2) }}</td>
                <td>{{ $line->notes ?? '—' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">Estimated Total Value:</td>
                <td colspan="2">${{ number_format($payment->total_estimated_value, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($payment->notes)
    <div class="row">
        <div class="col">
            <span class="label">Notes:</span>
            <div class="wide-line">{{ $payment->notes }}</div>
        </div>
    </div>
    @endif

    <div class="signature-section">
        <div class="sig-col">
            <span class="label">Received by (Company):</span>
            <span style="margin-left:8px;">Abou Saleh General Trading</span><br><br>
            <span class="label">Authorised Signature:</span>
            <div style="display:inline-block;">
                @if($signatureB64)
                <img class="signature-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Signature">
                @endif
            </div>
        </div>
        <div class="sig-col-right">
            <div class="stamp-box"></div>
        </div>
    </div>

    <div class="info-note">
        This receipt confirms that the above inventory items have been received from the client as payment.
        All items are valued at market price at the time of receipt. Please retain this document for your records.
    </div>

    <div class="footer-line"></div>
</body>

</html>