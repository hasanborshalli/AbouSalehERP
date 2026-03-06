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
            background: #0b2545;
            padding: 28px 32px;
            text-align: center;
        }

        .top img {
            height: 48px;
        }

        .top h1 {
            color: #fff;
            font-size: 20px;
            margin: 12px 0 4px;
        }

        .top p {
            color: rgba(255, 255, 255, .7);
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
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }

        .highlight p {
            margin: 6px 0;
            font-size: 13px;
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
            <h1>Property Management Agreement</h1>
            <p>{{ $property->isFlip() ? 'Flip / Renovation & Sale' : 'Rental Management' }}</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $property->owner_name }}</strong>,</p>
            <p>Thank you for trusting Abou Saleh General Trading to manage your property. Please find below a summary of
                your agreement, with the full signed agreement attached as a PDF.</p>

            <div class="row"><span>Agreement No.</span><span>MP-{{ str_pad($property->id,5,'0',STR_PAD_LEFT) }}</span>
            </div>
            <div class="row"><span>Property</span><span>{{ $property->address }}</span></div>
            <div class="row"><span>Service Type</span><span>{{ $property->isFlip() ? 'Flip — Renovation & Sale' :
                    'Rental Management' }}</span></div>
            <div class="row"><span>Agreement Date</span><span>{{ $property->agreement_date->format('d M Y') }}</span>
            </div>

            @if($property->isFlip())
            <div class="highlight">
                <p><strong>Your guaranteed payout on sale:</strong> ${{ number_format($property->owner_asking_price, 2)
                    }}</p>
                @if($property->agreed_listing_price)
                <p><strong>Agreed listing price:</strong> ${{ number_format($property->agreed_listing_price, 2) }}</p>
                @endif
                <p style="color:#6b7280;font-size:12px;">We will cover all renovation costs and pay you your full amount
                    upon successful sale.</p>
            </div>
            @endif

            @if($property->isRental())
            <div class="highlight">
                <p><strong>Expected monthly rent:</strong> ${{ number_format($property->agreed_rent_price ?? 0, 2) }}
                </p>
                @if($property->company_commission_pct)
                @php $comm = ($property->agreed_rent_price ?? 0) * $property->company_commission_pct / 100; @endphp
                <p><strong>Your monthly share:</strong> ${{ number_format(($property->agreed_rent_price ?? 0) - $comm,
                    2) }} ({{ 100 - $property->company_commission_pct }}%)</p>
                <p><strong>Management fee:</strong> ${{ number_format($comm, 2) }}/month ({{
                    $property->company_commission_pct }}%)</p>
                @endif
            </div>
            @endif

            <p>Please review the attached PDF and keep it for your records. If you have any questions, contact us at <a
                    href="mailto:info@abousaleh.me">info@abousaleh.me</a> or call +961 71 999 219.</p>
        </div>
        <div class="footer">Abou Saleh General Trading &nbsp;·&nbsp; +961 71 999 219 &nbsp;·&nbsp; info@abousaleh.me
        </div>
    </div>
</body>

</html>