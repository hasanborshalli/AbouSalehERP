<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #1f2937;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }

        .wrap {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .top {
            background: #7c3aed;
            padding: 28px 32px;
            text-align: center;
        }

        .top h1 {
            color: #fff;
            font-size: 20px;
            margin: 0 0 4px;
        }

        .top p {
            color: rgba(255, 255, 255, .8);
            font-size: 13px;
            margin: 0;
        }

        .body {
            padding: 28px 32px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .row span:first-child {
            color: #6b7280;
            font-size: 13px;
        }

        .row span:last-child {
            font-weight: 700;
            font-size: 13px;
        }

        .highlight {
            background: #f5f3ff;
            border: 1px solid #c4b5fd;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }

        .highlight .amount {
            font-size: 28px;
            font-weight: 900;
            color: #7c3aed;
        }

        .footer {
            background: #f9fafb;
            padding: 20px 32px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="top">
            <h1>🔑 Rental Contract</h1>
            <p>Your tenancy agreement — {{ $property->address }}</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $rental->tenant_name }}</strong>,</p>
            <p>Your rental contract has been prepared by <strong>Abou Saleh General Trading</strong>. Please find the
                signed contract attached as a PDF.</p>

            <div class="row"><span>Contract No.</span><span>RC-{{ str_pad($rental->id,5,'0',STR_PAD_LEFT) }}</span>
            </div>
            <div class="row"><span>Property</span><span>{{ $property->address }}</span></div>
            @if($property->city)<div class="row"><span>City / Area</span><span>{{ $property->city }}{{ $property->area ?
                    ' — '.$property->area : '' }}</span></div>@endif
            <div class="row"><span>Rental Period</span><span>{{ $rental->start_date->format('d M Y') }} → {{
                    $rental->end_date->format('d M Y') }}</span></div>
            <div class="row"><span>Security Deposit</span><span>${{ number_format($rental->deposit_amount, 2) }}</span>
            </div>

            <div class="highlight">
                <p style="margin:0 0 6px;font-size:12px;color:#6b7280;">Monthly Rent</p>
                <div class="amount">${{ number_format($rental->monthly_rent, 2) }}</div>
                <p style="margin:6px 0 0;font-size:12px;color:#6b7280;">Due on the 1st of each month</p>
            </div>

            <p style="font-size:13px;"><strong>Key terms:</strong> Rent is due on the 1st of each month. The security
                deposit will be returned at lease end subject to property inspection. Please retain this email and the
                attached contract for your records.</p>

            <p>For any questions contact us at <a href="mailto:info@abousaleh.me">info@abousaleh.me</a> or call +961 71
                999 219.</p>
        </div>
        <div class="footer">Abou Saleh General Trading &nbsp;·&nbsp; +961 71 999 219 &nbsp;·&nbsp; info@abousaleh.me
        </div>
    </div>
</body>

</html>