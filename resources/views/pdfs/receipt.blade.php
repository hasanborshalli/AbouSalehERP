<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    @php
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $signaturePath = public_path('img/abousaleh-signature.png');
    $signatureB64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

    @endphp

    <style>
        @page {
            margin: 32px 40px;
        }

        .signature-wrap {
            position: relative;
            display: inline-block;
        }

        .signature-img {
            position: relative;
            top: -8px;
            height: 40px;
            max-width: 220px;
            display: inline-block;
            vertical-align: bottom;
        }


        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0b2545;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            left: 50%;
            top: 55%;
            transform: translate(-50%, -50%);
            width: 480px;
            opacity: 0.07;
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
            background: #0d3b66;
            color: #fff;
            padding: 10px 14px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 18px;
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

        .line {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 260px;
            padding-bottom: 2px;
            margin-left: 8px;
        }

        .wide-line {
            border-bottom: 1px dotted #333;
            display: inline-block;
            width: 100%;
            padding-bottom: 2px;
            margin-top: 4px;
        }

        .payment-method {
            margin-top: 14px;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            font-size: 10px;
            line-height: 12px;
            margin: 0 6px 0 14px;
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

        .stamp-box {
            width: 90px;
            height: 90px;
            border: 1px solid #000;
            margin-top: 16px;
            display: inline-block;
        }

        .footer-line {
            margin-top: 26px;
            border-top: 3px solid #0d3b66;
        }
    </style>
</head>

<body>

    {{-- Watermark --}}
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="Watermark">
    @endif

    {{-- Top Logo --}}
    <div class="logo-top">
        @if($logoB64)
        <img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">
        @endif
    </div>

    <!-- HEADER -->
    <div class="header">
        <div class="title">ABOU SALEH GENERAL TRADING</div>
    </div>

    <div class="contact-row">
        <div class="contact-col">
            Address: ___________________________<br>
            Email: ___________________________
        </div>
        <div class="contact-col" style="text-align:right;">
            Tel: ___________________________<br>
            Email: ___________________________
        </div>
    </div>

    <!-- VOUCHER BAR -->
    <div class="voucher-bar" style="text-align: center">
        RECEIPT VOUCHER
    </div>

    <!-- RECEIPT NO -->
    <div class="row">
        <div class="col">
            <span class="label">Receipt No:</span>
            <span class="line">{{ $receiptNo }}</span>
        </div>
    </div>

    <!-- DATE -->
    <div class="row">
        <div class="col">
            <span class="label">Date:</span>
            <span class="line">{{ $date }}</span>
        </div>
    </div>

    <!-- RECEIVED FROM -->
    <div class="row">
        <div class="col">
            <span class="label">Received From:</span>
            <div class="wide-line">{{ $receivedFrom }}</div>
        </div>
    </div>

    <!-- SUM -->
    <div class="row">
        <div class="col">
            <span class="label">The Sum of:</span>
            <div class="wide-line">{{ $sumOf }}</div>
        </div>
    </div>

    <!-- AMOUNT NUMBERS -->
    <div class="row">
        <div class="col">
            <span class="label">Amount in Numbers:</span>
            <div class="wide-line">{{ $amountNumbers }}</div>
        </div>
    </div>

    <!-- FOR -->
    <div class="row">
        <div class="col">
            <span class="label">For:</span>
            <div class="wide-line">{{ $forWhat }}</div>
        </div>
    </div>

    <!-- PAYMENT METHOD -->
    <div class="payment-method">
        <span class="label">Payment Method:</span>

        <span class="checkbox">{{ $paymentMethod === 'cash' ? '✓' : '' }}</span> Cash
        <span class="checkbox">{{ $paymentMethod === 'cheque' ? '✓' : '' }}</span> Cheque
        <span class="checkbox">{{ $paymentMethod === 'bank_transfer' ? '✓' : '' }}</span> Bank Transfer
    </div>

    <!-- SIGNATURE -->
    <div class="signature-section">
        <div class="sig-col">
            <span class="label">Receiver Name:</span>
            <span class="sig-line">Abou Saleh General Trading</span><br><br>

            <span class="label">Signature:</span>
            <div class="signature-wrap">
                @if($signatureB64)
                <img class="signature-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Signature">
                @endif
            </div>
        </div>

        <div class="sig-col-right">
            <div class="stamp-box"></div>
        </div>
    </div>

    <div class="footer-line"></div>

</body>

</html>