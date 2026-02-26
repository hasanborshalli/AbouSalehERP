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

        /* Typography */
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

        /* Separators */
        .line {
            height: 1px;
            background: #e5e7eb;
            margin: 12px 0;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            page-break-inside: avoid;
        }

        /* Tables */
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

        .notes {
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        /* Header */
        .header td {
            vertical-align: top;
        }

        .logo {
            width: 110px;
        }

        /* Signatures */
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

        .sig-img {
            height: 34px;
            max-width: 200px;
            display: block;
            margin-top: 6px;
        }

        /* Terms */
        .terms {
            page-break-inside: avoid;
        }

        .terms ol {
            margin: 0;
            padding-left: 18px;
        }

        .terms li {
            margin: 0 0 6px;
        }

        /* Spacing */
        .sp-10 {
            height: 10px;
        }

        .sp-12 {
            height: 12px;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="Watermark">
    @endif
    <!-- HEADER -->
    <table class="header" style="margin-bottom:10px;">
        <tr>
            <td style="padding-right:10px;">
                <div class="h1">Sales Contract</div>
                <div class="muted small" style="margin-top:2px;">
                    Contract ID: <b>#{{ $contract->id }}</b> &nbsp; • &nbsp;
                    Date: <b>{{ $contract->contract_date }}</b>
                    <br>
                    Generated at: <b>{{ ($generatedAt ?? now())->timezone('Asia/Beirut')->format('Y-m-d H:i:s') }}</b>

                </div>
            </td>
            <td style="text-align:right;">
                <img src="{{ public_path('img/abosaleh-logo.png') }}" class="logo" alt="Abou Saleh Logo">
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- CLIENT / UNIT -->
    <div class="card">
        <div class="h2">Client & Unit Details</div>
        <table class="info">
            <tr>
                <td class="label">Client</td>
                <td class="value">{{ $contract->client->name }}</td>
            </tr>
            <tr>
                <td class="label">Phone</td>
                <td class="value">{{ $contract->client->phone ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Project</td>
                <td class="value">{{ $contract->project->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Apartment</td>
                <td class="value">{{ $contract->apartment->unit_number ?? $contract->apartment->unit_code ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="sp-10"></div>

    <!-- PAYMENT SUMMARY -->
    <div class="card">
        <div class="h2">Payment Summary</div>

        <table class="money">
            <tr>
                <th>Total price</th>
                <td>USD ${{ number_format($contract->total_price, 2) }}</td>
            </tr>
            <tr>
                <th>Discount</th>
                <td>USD ${{ number_format($contract->discount, 2) }}</td>
            </tr>
            <tr class="final">
                <th>Final price</th>
                <td>USD ${{ number_format($contract->final_price, 2) }}</td>
            </tr>
            <tr>
                <th>Down payment</th>
                <td>USD ${{ number_format($contract->down_payment, 2) }}</td>
            </tr>
            <tr>
                <th>Installment period (months)</th>
                <td>{{ $contract->installment_months }}</td>
            </tr>
            <tr>
                <th>Monthly installment</th>
                <td>USD ${{ number_format($contract->installment_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Payment start date</th>
                <td>{{ $contract->payment_start_date }}</td>
            </tr>
            <tr>
                <th>Late fee (per delay)</th>
                <td>
                    @if($contract->late_fee > 0)
                    USD ${{ number_format($contract->late_fee, 2) }}
                    @else
                    No late fee
                    @endif
                </td>
            </tr>
        </table>

        @if($contract->notes)
        <div class="notes">
            <div style="font-weight:800; margin-bottom:6px;">Notes</div>
            <div>{{ $contract->notes }}</div>
        </div>
        @endif
    </div>

    <div class="sp-12"></div>

    <!-- SIGNATURES -->
    <div class="sig-wrap">
        <div class="h2">Signatures</div>

        <table style="border:0;">
            <tr>
                <td style="width:50%; padding-right:10px; border:0; vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Client</div>
                        <div><b>Name:</b> {{ $contract->client->name }}</div>

                        <div style="margin-top:26px;"><span class="k">Signature:</span> ____________________________
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>

                <td style="width:50%; padding-left:10px; border:0; vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Seller / Company</div>
                        <div><b>Company:</b> Abou Saleh Real Estate</div>
                        <div><b>Representative:</b> ____________________________</div>

                        <div style="margin-top:10px;">
                            <span class="k">Signature:</span>
                            <span
                                style="display:inline-block; width:240px; border-bottom:1px solid #111; height:18px; vertical-align:bottom;">
                                @if($signatureB64)
                                <img class="sig-signature-img" style="position:relative; top:-18px; left:10px;"
                                    src="data:image/png;base64,{{ $signatureB64 }}" alt="Company Signature">
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
            <li>All payments must be made according to the agreed schedule stated in this contract.</li>
            <li>Down payment is non-refundable unless otherwise stated in writing.</li>
            <li>Late payments are subject to the late fee mentioned above (if applicable).</li>
            <li>Any amendment to this contract must be made in writing and signed by both parties.</li>
            <li>This contract is governed by applicable local laws and regulations.</li>
            <li>Both parties acknowledge they have read and accepted all terms herein.</li>
        </ol>
    </div>

    <!-- IMPORTANT: DOMPDF FOOTER (ONLY ONE FOOTER SYSTEM) -->
    <script type="text/php">
        if (isset($pdf)) {
            // A4 portrait is 595x842 points.
            // Left margin ~22. Bottom text y ~ 825 keeps it inside margin.
            $left = "Abou Saleh Real Estate • +961 XX XXX XXX • info@abousaleh.me";
            $pdf->page_text(22, 825, $left, null, 9, array(0.42,0.45,0.50));
            $pdf->page_text(500, 825, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 9, array(0.42,0.45,0.50));
        }
    </script>

</body>

</html>