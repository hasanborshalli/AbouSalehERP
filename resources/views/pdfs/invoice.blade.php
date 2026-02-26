<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $signaturePath = public_path('img/abousaleh-signature.png');
    $signatureB64 = file_exists($signaturePath)
    ? base64_encode(file_get_contents($signaturePath))
    : null;
    @endphp
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .signature-section {
            margin-top: 40px;
            text-align: right;
        }

        .signature-label {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .signature-img {
            height: 45px;
            max-width: 220px;
            display: block;
            margin-top: 6px;
        }

        .signature-line {
            width: 240px;
            border-bottom: 1px solid #111;
            margin-top: 6px;
        }

        .header {
            display: flex;
            justify-content: space-between;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.06;
            /* adjust between 0.04 - 0.08 */
            z-index: -1;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .box {
            border: 1px solid #eee;
            padding: 10px;
            margin-top: 10px;
        }

        .logo {
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 10.5px;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="Watermark">
    @endif
    {{-- Logo --}}
    <img src="{{ public_path('img/abosaleh-logo.png') }}" class="logo" alt="Company logo">
    <div class="header">
        <div>
            <div class="title">Invoice</div>
            <div class="muted small" style="margin-top:2px;">
                Generated at: <b>{{ ($generatedAt ?? now())->timezone('Asia/Beirut')->format('Y-m-d H:i:s') }}</b>
            </div>

            <div><strong>No:</strong> {{ $invoice->invoice_number }}</div>
            <div><strong>Status:</strong> {{ strtoupper($invoice->status) }}</div>
        </div>
        <div>
            <div><strong>Issue:</strong> {{ $invoice->issue_date }}</div>
            <div><strong>Due:</strong> {{ $invoice->due_date }}</div>
        </div>
    </div>

    <div class="box">
        <div><strong>Client:</strong> {{ $contract->client->name }}</div>
        <div><strong>Project:</strong> {{ $contract->project->name ?? '-' }}</div>
        <div><strong>Apartment:</strong> {{ $contract->apartment->unit_number ?? $contract->apartment->unit_code ?? '-'
            }}</div>
        <div><strong>Contract ID:</strong> {{ $contract->id }}</div>
    </div>
    @php
    $baseAmount = (float) $invoice->amount;
    $lateFee = (float) ($invoice->late_fee_amount ?? 0);
    $totalDue = $baseAmount + $lateFee;
    @endphp

    <div class="box">
        <div><strong>Base Amount:</strong> ${{ number_format($baseAmount, 2) }}</div>

        @if(strtolower($invoice->status) === 'overdue' && $lateFee > 0)
        <div><strong>Late Fee:</strong> ${{ number_format($lateFee, 2) }}</div>
        <div style="margin-top:6px;"><strong>Total Due:</strong> ${{ number_format($totalDue, 2) }}</div>
        <div class="muted small" style="margin-top:6px;">
            Late fee applied as per contract.
        </div>
        @else
        <div style="margin-top:6px;"><strong>Total Due:</strong> ${{ number_format($totalDue, 2) }}</div>
        @endif

        <div style="margin-top:8px;"><strong>Description:</strong> Monthly installment</div>
    </div>
    <div class="signature-section">
        <div class="signature-label">Authorized Signature</div>

        @if($signatureB64)
        <img class="signature-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Company Signature">
        @else
        <div class="signature-line"></div>
        @endif

        <div style="margin-top:4px; font-weight:600;">
            Abou Saleh Real Estate
        </div>
    </div>
</body>

</html>