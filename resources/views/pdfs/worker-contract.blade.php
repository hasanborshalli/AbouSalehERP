<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $signaturePath = public_path('img/abousaleh-signature.png');
    $signatureB64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;
    @endphp
    <style>
        @page {
            margin: 26px 22px 55px 22px;
        }

        .sig-signature-img {
            height: 42px;
            max-width: 220px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 8px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .watermark {
            position: fixed;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.06;
            z-index: -1;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 10.5px;
        }

        .h1 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .h2 {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .line {
            height: 1px;
            background: #e5e7eb;
            margin: 12px 0;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 6px 8px;
            border-bottom: 1px solid #eef2f7;
        }

        .info tr:last-child td {
            border-bottom: 0;
        }

        .label {
            width: 34%;
            font-weight: 700;
        }

        .value {
            width: 66%;
        }

        .money th,
        .money td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
        }

        .money th {
            width: 55%;
            text-align: left;
            background: #f8fafc;
            font-weight: 700;
        }

        .money td {
            text-align: right;
        }

        .money .final th,
        .money .final td {
            background: #f1f5f9;
            font-weight: 800;
        }

        .schedule th,
        .schedule td {
            border: 1px solid #e5e7eb;
            padding: 7px 10px;
            font-size: 11px;
        }

        .schedule th {
            background: #f8fafc;
            font-weight: 700;
            text-align: left;
        }

        .notes {
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            width: 110px;
        }

        .sig-wrap {
            page-break-inside: avoid;
        }

        .sig-box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            height: 130px;
            page-break-inside: avoid;
        }

        .sig-title {
            font-weight: 800;
            margin-bottom: 8px;
        }

        .k {
            font-weight: 700;
            display: inline-block;
            width: 72px;
        }

        .sp-10 {
            height: 10px;
        }

        .sp-12 {
            height: 12px;
        }

        .terms {
            page-break-inside: avoid;
        }

        .terms li {
            margin: 0 0 6px;
        }

        .badge-cat {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">
    @endif

    <!-- HEADER -->
    <table class="header" style="margin-bottom:10px;">
        <tr>
            <td style="padding-right:10px;">
                <div class="h1">Service / Work Contract</div>
                <div class="muted small" style="margin-top:2px;">
                    Contract ID: <b>#{{ $contract->id }}</b> &nbsp;•&nbsp;
                    Date: <b>{{ $contract->contract_date->format('Y-m-d') }}</b><br>
                    Generated: <b>{{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</b>
                </div>
            </td>
            <td style="text-align:right;">
                @if($logoB64)
                <img src="data:image/png;base64,{{ $logoB64 }}" class="logo" alt="Logo">
                @endif
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- PARTIES -->
    <div class="card">
        <div class="h2">Contracting Parties</div>
        <table class="info">
            <tr>
                <td class="label">Company (Employer)</td>
                <td class="value">Abou Saleh General Trading</td>
            </tr>
            <tr>
                <td class="label">Contractor / Worker</td>
                <td class="value"><b>{{ $contract->worker->name }}</b></td>
            </tr>
            <tr>
                <td class="label">Phone</td>
                <td class="value">{{ $contract->worker->phone ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="value">{{ $contract->worker->email ?? '—' }}</td>
            </tr>
            @if($contract->project)
            <tr>
                <td class="label">Project</td>
                <td class="value">{{ $contract->project->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="sp-10"></div>

    <!-- SCOPE -->
    <div class="card">
        <div class="h2">Scope of Work</div>
        <table class="info">
            <tr>
                <td class="label">Description</td>
                <td class="value"><b>{{ $contract->scope_of_work }}</b></td>
            </tr>
            @if($contract->category)
            <tr>
                <td class="label">Category</td>
                <td class="value"><span class="badge-cat">{{ strtoupper($contract->category) }}</span></td>
            </tr>
            @endif
            @if($contract->start_date)
            <tr>
                <td class="label">Start Date</td>
                <td class="value">{{ $contract->start_date->format('Y-m-d') }}</td>
            </tr>
            @endif
            @if($contract->expected_end_date)
            <tr>
                <td class="label">Expected Completion</td>
                <td class="value">{{ $contract->expected_end_date->format('Y-m-d') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="sp-10"></div>

    <!-- PAYMENT -->
    <div class="card">
        <div class="h2">Payment Terms</div>
        <table class="money">
            <tr>
                <th>Total Contract Amount</th>
                <td>USD ${{ number_format($contract->total_amount,2) }}</td>
            </tr>
            <tr class="final">
                <th>Monthly Payment</th>
                <td>USD ${{ number_format($contract->monthly_amount,2) }}</td>
            </tr>
            <tr>
                <th>Number of Payments</th>
                <td>{{ $contract->payment_months }} months</td>
            </tr>
            <tr>
                <th>First Payment Date</th>
                <td>{{ $contract->first_payment_date->format('Y-m-d') }}</td>
            </tr>
        </table>

        @if($contract->notes)
        <div class="notes">
            <div style="font-weight:800;margin-bottom:6px;">Notes</div>
            <div>{{ $contract->notes }}</div>
        </div>
        @endif
    </div>

    <div class="sp-12"></div>

    <!-- PAYMENT SCHEDULE TABLE -->
    <div class="card">
        <div class="h2">Payment Schedule</div>
        <table class="schedule">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Due Date</th>
                    <th>Amount (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contract->payments->sortBy('installment_index') as $p)
                <tr>
                    <td>{{ $p->installment_index }}</td>
                    <td>{{ $p->due_date->format('Y-m-d') }}</td>
                    <td style="text-align:right;">${{ number_format($p->amount,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="sp-12"></div>

    <!-- SIGNATURES -->
    <div class="sig-wrap">
        <div class="h2">Signatures</div>
        <table style="border:0;">
            <tr>
                <td style="width:50%;padding-right:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Contractor / Worker</div>
                        <div><b>Name:</b> {{ $contract->worker->name }}</div>
                        <div style="margin-top:26px;"><span class="k">Signature:</span> ____________________________
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>
                <td style="width:50%;padding-left:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Employer / Company</div>
                        <div><b>Company:</b> Abou Saleh General Trading</div>
                        <div><b>Representative:</b> ____________________________</div>
                        <div style="margin-top:10px;">
                            <span class="k">Signature:</span>
                            <span
                                style="display:inline-block;width:240px;border-bottom:1px solid #111;height:18px;vertical-align:bottom;">
                                @if($signatureB64)
                                <img class="sig-signature-img" style="position:relative;top:-18px;left:10px;"
                                    src="data:image/png;base64,{{ $signatureB64 }}" alt="Signature">
                                @endif
                            </span>
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="sp-12"></div>

    <!-- TERMS -->
    <div class="card terms">
        <div class="h2">Terms & Conditions</div>
        <ol class="small">
            <li>The contractor agrees to complete the described scope of work to professional standards.</li>
            <li>Payments will be made monthly as per the schedule above, provided work is progressing satisfactorily.
            </li>
            <li>The employer reserves the right to withhold payment if work quality does not meet agreed standards.</li>
            <li>The contractor is responsible for their own tools, equipment, and safety at the work site.</li>
            <li>Any changes to scope or payment terms must be agreed upon in writing by both parties.</li>
            <li>This contract is governed by applicable local laws and regulations.</li>
            <li>Both parties acknowledge they have read and accepted all terms herein.</li>
        </ol>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $left = "Abou Saleh General Trading • Work Contract #{{ $contract->id }}";
            $pdf->page_text(22, 825, $left, null, 9, array(0.42,0.45,0.50));
            $pdf->page_text(500, 825, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 9, array(0.42,0.45,0.50));
        }
    </script>
</body>

</html>