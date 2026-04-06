{{--
SAVE AS: resources/views/emails/managed-sale.blade.php
--}}
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
            background: #059669;
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

        .big-num {
            font-size: 32px;
            font-weight: 900;
            color: #059669;
            text-align: center;
            padding: 16px 0;
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
            <h1>🎉 Sale Confirmed</h1>
            <p>Property purchase confirmation</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $sale->buyer_name }}</strong>,</p>
            <p>Congratulations! Your purchase of the following property has been confirmed by <strong>Abou Saleh General
                    Trading</strong>.</p>

            <div class="row"><span>Property</span><span>{{ $property->address }}</span></div>
            @if($property->city)<div class="row"><span>City / Area</span><span>{{ $property->city }}{{ $property->area ?
                    ' — '.$property->area : '' }}</span></div>@endif
            <div class="row"><span>Sale Date</span><span>{{ $sale->sale_date->format('d M Y') }}</span></div>
            <div class="row"><span>Your Name</span><span>{{ $sale->buyer_name }}</span></div>

            <div class="big-num">${{ number_format($sale->sale_price, 2) }}</div>
            <p style="text-align:center;color:#6b7280;font-size:12px;margin-top:0;">Total Purchase Price</p>

            @if($property->notes)
            <p style="font-size:13px;color:#6b7280;">{{ $property->notes }}</p>
            @endif

            <p>Our team will be in touch to finalize all legal paperwork. For any questions please contact us at <a
                    href="mailto:info@abousaleh.me">info@abousaleh.me</a>.</p>
        </div>
        <div style="padding:20px 32px; direction:rtl; text-align:right; border-top:2px solid #e5e7eb;">
            <p>عزيزي/عزيزتي <strong>{{ $sale->buyer_name }}</strong>،</p>
            <p>تهانينا! تم تأكيد شراءكم للعقار أدناه من شركة أبو صالح للتجارة العامة.</p>
            <p><strong>العقار:</strong> {{ $property->address }}</p>
            <p><strong>تاريخ البيع:</strong> {{ $sale->sale_date->format('d M Y') }}</p>
            <p><strong>سعر الشراء:</strong> ${{ number_format($sale->sale_price, 2) }}</p>
            <p>سيتواصل معكم فريقنا لإتمام الإجراءات القانونية. لأي استفسار: <a
                    href="mailto:info@abousaleh.me">info@abousaleh.me</a></p>
            <p>مع تحياتنا — <strong>أبو صالح للتجارة العامة</strong></p>
        </div>
        <div class="footer">Abou Saleh General Trading &nbsp;·&nbsp; +961 71 999 219 &nbsp;·&nbsp; info@abousaleh.me
        </div>
    </div>
</body>

</html>