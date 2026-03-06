<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 36px 44px; }

        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0b2545; }

        .watermark {
            position: fixed; left: 50%; top: 52%;
            transform: translate(-50%, -50%);
            width: 480px; opacity: 0.06; z-index: -1;
        }

        .logo-top { text-align: center; margin-bottom: 6px; }
        .logo-top img { width: 110px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header .title { font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 0; }
        .contact-row { display: table; width: 100%; margin: 6px 0 12px; }
        .contact-col { display: table-cell; width: 50%; font-size: 11px; vertical-align: top; }
        .voucher-bar {
            background: #1e3a5f; color: #fff; padding: 10px 14px;
            font-weight: bold; font-size: 14px; margin-bottom: 18px; text-align: center;
        }
        .section-title {
            font-size: 11px; font-weight: bold; text-transform: uppercase;
            color: #1e3a5f; border-bottom: 2px solid #1e3a5f;
            padding-bottom: 4px; margin: 16px 0 10px;
        }
        .row { display: table; width: 100%; margin-bottom: 8px; }
        .col-label { display: table-cell; width: 40%; font-weight: bold; font-size: 11px; }
        .col-value { display: table-cell; border-bottom: 1px dotted #333; font-size: 11px; padding-bottom: 2px; }

        .highlight-box {
            background: #f0f4ff; border: 1px solid #bfdbfe;
            border-radius: 6px; padding: 14px 18px; margin: 14px 0;
        }
        .hl-row { display: table; width: 100%; margin-bottom: 6px; }
        .hl-label { display: table-cell; font-size: 11px; font-weight: bold; color: #1e40af; }
        .hl-value { display: table-cell; text-align: right; font-size: 13px; font-weight: 800; color: #1e3a5f; }

        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        thead th {
            background: #f1f5f9; padding: 6px 8px; text-align: left;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            color: #374151; border-bottom: 1.5px solid #e5e7eb;
        }
        thead th.num { text-align: right; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        tbody td.num { text-align: right; }

        .info-box {
            background: #fffbeb; border: 1px solid #fde68a;
            border-radius: 6px; padding: 12px; font-size: 11px;
            color: #374151; line-height: 1.7; margin: 14px 0;
        }
        .signature-section { display: table; width: 100%; margin-top: 36px; }
        .sig-col { display: table-cell; width: 50%; vertical-align: top; }
        .sig-label { font-size: 11px; font-weight: bold; margin-bottom: 28px; }
        .sig-line { border-top: 1px solid #333; width: 80%; margin-top: 4px; }
        .signature-img {
            position: relative; top: -8px; height: 40px;
            max-width: 220px; display: inline-block; vertical-align: bottom;
        }
        .stamp-box { width: 80px; height: 80px; border: 1px solid #000; display: inline-block; margin-top: 10px; }
        .footer-line { margin-top: 24px; border-top: 3px solid #1e3a5f; }
        .info-note { font-size: 10px; color: #6b7280; margin-top: 10px; padding-top: 6px; border-top: 1px dashed #e5e7eb; }
    </style>
</head>
<body>
    @if($logoB64)<img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif

    <div class="logo-top">
        @if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif
    </div>
    <div class="header">
        <div class="title">ABOU SALEH GENERAL TRADING</div>
    </div>
    <div class="contact-row">
        <div class="contact-col">Address: ___________________________<br>Email: info@abousaleh.me</div>
        <div class="contact-col" style="text-align:right;">Tel: +961 71 999 219<br>www.abousaleh.me</div>
    </div>

    <div class="voucher-bar">RENTAL AGREEMENT</div>

    <div class="section-title">Contract Details</div>
    <div class="row">
        <div class="col-label">Contract No:</div>
        <div class="col-value">RC-{{ str_pad($rental->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>
    <div class="row">
        <div class="col-label">Issue Date:</div>
        <div class="col-value">{{ now()->format('d F Y') }}</div>
    </div>
    <div class="row">
        <div class="col-label">Rental Period:</div>
        <div class="col-value">{{ $rental->start_date->format('d M Y') }} → {{ $rental->end_date->format('d M Y') }}</div>
    </div>

    <div class="section-title">Property</div>
    <div class="row">
        <div class="col-label">Address:</div>
        <div class="col-value">{{ $property->address }}</div>
    </div>
    @if($property->city || $property->area)
    <div class="row">
        <div class="col-label">City / Area:</div>
        <div class="col-value">{{ $property->city }}{{ $property->area ? ' — '.$property->area : '' }}</div>
    </div>
    @endif
    @if($property->bedrooms || $property->bathrooms)
    <div class="row">
        <div class="col-label">Specifications:</div>
        <div class="col-value">
            @if($property->bedrooms) {{ $property->bedrooms }} Bedroom(s) @endif
            @if($property->bathrooms) · {{ $property->bathrooms }} Bathroom(s) @endif
            @if($property->area_sqm) · {{ number_format($property->area_sqm,0) }} m² @endif
        </div>
    </div>
    @endif

    <div class="section-title">Tenant Information</div>
    <div class="row">
        <div class="col-label">Tenant Name:</div>
        <div class="col-value">{{ $rental->tenant_name }}</div>
    </div>
    @if($rental->tenant_phone)
    <div class="row">
        <div class="col-label">Phone:</div>
        <div class="col-value">{{ $rental->tenant_phone }}</div>
    </div>
    @endif
    @if($rental->tenant_email)
    <div class="row">
        <div class="col-label">Email:</div>
        <div class="col-value">{{ $rental->tenant_email }}</div>
    </div>
    @endif

    <div class="section-title">Financial Terms</div>
    <div class="highlight-box">
        <div class="hl-row">
            <div class="hl-label">Monthly Rent:</div>
            <div class="hl-value">${{ number_format($rental->monthly_rent, 2) }}</div>
        </div>
        <div class="hl-row">
            <div class="hl-label">Security Deposit:</div>
            <div class="hl-value">${{ number_format($rental->deposit_amount, 2) }}</div>
        </div>
        <div class="hl-row">
            <div class="hl-label">Payment Due:</div>
            <div class="hl-value">1st of each month</div>
        </div>
    </div>

    <div class="section-title">Payment Schedule (first 12 months)</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th class="num">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rental->payments->sortBy('due_date')->take(12) as $i => $pmt)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pmt->due_date->format('d M Y') }}</td>
                <td class="num">${{ number_format($pmt->amount_due, 2) }}</td>
                <td>{{ ucfirst($pmt->status) }}</td>
            </tr>
            @endforeach
            @if($rental->payments->count() > 12)
            <tr>
                <td colspan="4" style="text-align:center;color:#6b7280;font-style:italic;">
                    ... {{ $rental->payments->count() - 12 }} more payments (full schedule on file)
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="info-box">
        <strong>Terms & Conditions:</strong><br>
        1. Rent is due on the 1st of each month. Late payments may incur additional fees.<br>
        2. The security deposit will be returned at lease end, subject to property condition inspection.<br>
        3. The tenant is responsible for minor maintenance and keeping the property in good condition.<br>
        4. Subletting is not permitted without written consent from Abou Saleh General Trading.<br>
        5. Either party must give 30 days written notice to terminate this agreement.
    </div>

    @if($rental->notes)
    <div class="section-title">Additional Notes</div>
    <div style="font-size:11px; line-height:1.6;">{{ $rental->notes }}</div>
    @endif

    <div class="section-title">Signatures</div>
    <div class="signature-section">
        <div class="sig-col">
            <p class="sig-label">Tenant: {{ $rental->tenant_name }}</p>
            <div class="sig-line"></div>
            <p style="font-size:10px;color:#6b7280;margin-top:4px;">Signature & Date</p>
        </div>
        <div class="sig-col">
            <p class="sig-label">Abou Saleh General Trading</p>
            @if($signatureB64)
            <img class="signature-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Signature">
            @else
            <div class="sig-line"></div>
            @endif
            <br>
            <div class="stamp-box"></div>
        </div>
    </div>

    <div class="info-note">
        Contract Reference: RC-{{ str_pad($rental->id, 5, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp;
        Property: MP-{{ str_pad($property->id, 5, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp;
        Generated: {{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}
    </div>
    <div class="footer-line"></div>
</body>
</html>
