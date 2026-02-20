<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
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
</body>

</html>